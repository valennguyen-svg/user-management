<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    // Thêm ->name('login.attempt') vào dòng bên dưới:
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Đăng ký tài khoản  tài khoản mới ở trạng thái chờ kích hoạt
Route::middleware('guest')
    ->get('/register', [RegisterController::class, 'show'])
    ->name('register');

//  tham số thứ 3 tách bộ đếm riêng, không dùng chung với đăng nhập
Route::middleware(['guest', 'throttle:5,1,register'])
    ->post('/register', [RegisterController::class, 'store'])
    ->name('register.store');

// Kiểm tra tài khoản còn hoạt động
Route::withoutMiddleware(EnsureUserIsActive::class)
    ->get('/heartbeat', [AuthController::class, 'heartbeat'])
    ->name('heartbeat');
