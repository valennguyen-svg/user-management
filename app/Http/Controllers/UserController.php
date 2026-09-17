<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Exports\UsersTemplateExport;
use App\Http\Requests\ImportUserRequest;
use App\Http\Requests\IndexUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Imports\UsersImport;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private UserService $users) {}

    // Các tham số tra cứu dùng chung cho danh sách và Export (BR-09)
    private const FILTERS = ['keyword', 'role', 'status', 'sort'];

    public function index(IndexUserRequest $request)
    {
        $validated = $request->validated();

        $users = User::with('roles');

        if ($request->filled('role')) {
            $users = $users->role($request->role);
        }

        // Tìm theo tên hoặc email
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $user = $users->whereAny(['name', 'email'], 'ilike', "%{$keyword}%");
        }

        // Lọc trạng thái : chỉ nhận '1' hoặc '0'
        if (in_array($request->status, ['0', '1'], true)) {
            $users = $users->where('status', $request->status === '1');
        }

        // Sắp xếp theo ngày tạo
        $direction = $request->sort === 'desc' ? 'desc' : 'asc';
        $users = $users->orderBy('created_at', $direction)->orderBy('id', $direction);

        // Docs dùng ->get(), đề yêu cầu phân trang (FR-03) nên đổi thành ->paginate()
        $users = $users->paginate(10)->withQueryString();

        $roles = Role::all()->pluck('name');

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all()->pluck('name');

        return view('users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        // : tạo user và gán role trong 1 transaction
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => $request->boolean('status'),
            ]);

            $user->assignRole($request->role);
        });

        return redirect()->route('users.index')->with('success', 'Đã tạo người dùng.');
    }

    public function edit(User $user)
    {
        $roles = Role::all()->pluck('name');

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        DB::transaction(function () use ($request, $user) {
            $user->name = $request->name;
            $user->email = $request->email;
            $user->status = $request->boolean('status');

            // Để trống mật khẩu mới thì giữ nguyên mật khẩu cũ (AC-07)
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            $user->syncRoles([$request->role]);
        });

        return redirect()->route('users.index')->with('success', 'Đã cập nhật người dùng.');
    }

    public function destroy(User $user)
    {
        $response = Gate::inspect('delete', $user);

        if ($response->denied()) {
            // Policy không ghi lý do (hoặc không tìm thấy Policy) thì vẫn phải có thông báo
            return redirect()->route('users.index')
                ->with('error', $response->message() ?? 'Bạn không có quyền xóa người dùng này.');
        }

        if (! $this->users->delete($user)) {
            return redirect()->route('users.index')->with('error', 'Người dùng này đã bị xóa trước đó.');
        }

        return redirect()->route('users.index')->with('success', 'Đã xóa người dùng.');
    }

    public function export(Request $request)
    {

        // return Excel::download(new UsersExport, 'users.xlsx');
        return Excel::download(
            new UsersExport($request->only(self::FILTERS)),
            'users.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function template()
    {
        return Excel::download(
            new UsersTemplateExport,
            'users_import_template.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }

    public function import(ImportUserRequest $request)
    {
        $file = $request->file('file');
        $readerType = UsersImport::readerType($file);

        // sai định dạng hoặc thiếu cột thì dừng xử lý
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
