<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // chỉ tài khoản đang hoạt động mới đăng nhập được.
        // Tài khoản đã xóa mềm tự bị loại vì User dùng SoftDeletes.
        $credentials['status'] = true;

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            // thông báo chung, không tiết lộ trường nào sai
            return back()
                ->withErrors(['email' => 'Thông tin đăng nhập không hợp lệ.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('users.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function heartbeat(Request $request)
    {

        $guard = Auth::guard('web');
        $sessionKey = Auth::getName(); // hoặc dùng 'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'
        $id = $request->session()->get($sessionKey);

        if (! $id) {
            return response()->json(['state' => 'guest']);
        }

        $user = User::withTrashed()->find($id);

        return response()->json([
            'state' => match (true) {
                $user === null, $user->trashed() => 'deleted',
                ! $user->status => 'locked',
                default => 'active',
            },
        ]);
    }
}
