<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Http\Requests\ImportUserRequest;
use App\Http\Requests\IndexUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Imports\UsersImport;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private UserService $users) {}

    // Các tham số tra cứu dùng chung cho danh sách và Export
    private const FILTERS = ['keyword', 'role', 'status', 'sort'];

    public function index(IndexUserRequest $request)
    {
        $this->authorize('viewAny', User::class);

        $users = $this->users->paginate($request->validated());

        $roles = $this->users->roleNames();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::pluck('name');

        return view('users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $this->users->create($request->validated());

        return redirect()->route('users.index')->with('success', 'Đã tạo người dùng.');
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::pluck('name');

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        // Đang đăng nhập không được tự chuyển tài khoản sang Không hoạt động
        if ($user->id === Auth::id() && ! $request->boolean('status')) {
            return back()->withInput()->with('error', 'Bạn không được tự vô hiệu hóa tài khoản đang đăng nhập.');
        }

        $this->users->update($user, $request->validated());

        return redirect()->route('users.index')->with('success', 'Đã cập nhật người dùng.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Không được tự xóa tài khoản của mình
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('error', 'Bạn không thể tự xóa tài khoản đang đăng nhập.');
        }

        if (! $this->users->delete($user)) {
            return redirect()->route('users.index')->with('error', 'Người dùng này không tồn tại hoặc đã bị xóa trước đó.');
        }

        return redirect()->route('users.index')->with('success', 'Đã xóa người dùng.');
    }

    public function export(Request $request)
    {
        $this->authorize('export', User::class);

        return Excel::download(
            new UsersExport($request->only(self::FILTERS)),
            'users.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function template()
    {
        $this->authorize('import', User::class);

        $headings = [
            'name',
            'email',
            'password',
            'role',
            'status',
        ];
        $sampleData = [
            [
                'name' => 'Nguyen Van A',
                'email' => 'nguyenvana@example.com',
                'password' => 'Password123!',
                'role' => 'staff',
                'status' => 'active',
            ],
        ];

        return Excel::download(new class($headings, $sampleData) implements FromCollection, WithHeadings
        {
            protected array $headings;

            protected array $data;

            public function __construct(array $headings, array $data)
            {
                $this->headings = $headings;
                $this->data = $data;
            }

            public function collection(): Collection
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                return $this->headings;
            }
        }, 'users_import_template.xlsx');
    }

    public function import(ImportUserRequest $request)
    {
        $this->authorize('import', User::class);

        $file = $request->file('file');
        $readerType = UsersImport::readerType($file);

        try {
            $missing = UsersImport::missingColumns($file, $readerType);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('users.index')
                ->with('error', 'Không đọc được tệp. Hãy dùng tệp mẫu để nhập dữ liệu.');
        }

        if (! empty($missing)) {
            return redirect()->route('users.index')
                ->with('error', 'Tệp thiếu cột: '.implode(', ', $missing).'.');
        }

        $import = new UsersImport;

        try {
            Excel::import($import, $file, null, $readerType);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('users.index')
                ->with('error', 'Import thất bại do lỗi hệ thống, không có dữ liệu nào được ghi.');
        }

        if ($import->tooManyRows) {
            return redirect()->route('users.index')
                ->with('error', 'Tệp vượt quá '.UsersImport::MAX_ROWS.' dòng dữ liệu. Hãy chia nhỏ tệp.');
        }

        return redirect()->route('users.index')->with('import_result', $import->result());
    }
}
