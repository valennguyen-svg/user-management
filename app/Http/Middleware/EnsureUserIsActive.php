<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('web');
        $id = $request->session()->get($guard->getName());

        // Phiên có ID nhưng không lấy được người dùng => tài khoản đã bị xóa mềm.
        // Trường hợp còn lại: lấy được người dùng nhưng đang bị khóa.
        $daXoa = $id !== null && ! $guard->check();
        $biKhoa = $guard->check() && $guard->user()->status === false;

        if ($daXoa || $biKhoa) {
            $guard->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // để trang đăng nhập hiển thị thông báo
            return redirect()->route('login', ['ly_do' => 1]);
        }

        return $next($request);
    }
}
