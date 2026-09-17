<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

// Đăng nhập / đăng xuất (FR-01, FR-02)
Route::middleware('guest')
    ->get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::middleware(['guest', 'throttle:5,1'])
     ->post('/login', [AuthController::class, 'login'])
     ->name('login.attempt');

Route::middleware('auth')
     ->post('/logout', [AuthController::class, 'logout'])
     ->name('logout');

// Đăng ký tài khoản  tài khoản mới ở trạng thái chờ kích hoạt
Route::middleware('guest')
    ->get('/register', [RegisterController::class, 'show'])
    ->name('register');

//  tham số thứ 3 tách bộ đếm riêng, không dùng chung với đăng nhập
Route::middleware(['guest', 'throttle:5,1,register'])
    ->post('/register', [RegisterController::class, 'store'])
    ->name('register.store');
