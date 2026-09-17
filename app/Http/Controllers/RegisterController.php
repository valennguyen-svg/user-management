<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    // Vai trò cho tài khoản tự đăng ký: quyền thấp nhất trong hệ thống
    private const DEFAULT_ROLE = 'staff';

    public function show()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        // Tạo user và gán role trong 1 transaction, lỗi thì không để lại dữ liệu một phần
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                // Chờ quản trị viên kích hoạt. BR-03 chặn đăng nhập khi chưa hoạt động,
                // nên người lạ không thể tự đăng ký rồi xem ngay danh sách người dùng.
                'status' => false,
            ]);

            // Spatie - Using Permissions via Roles - Assigning Roles
            // $user->assignRole('writer');
            $user->assignRole(self::DEFAULT_ROLE);
        });

        return redirect()->route('login')
            ->with('success', 'Đăng ký thành công.');
    }
}
