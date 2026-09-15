<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('users.index');
});

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

// Spatie - Middleware - Using Middleware in Routes and Controllers
// Route::middleware(['role:manager', 'permission:publish articles'])->get('/admin/publish', ...);

// Export
Route::middleware(['auth', 'permission:users.export'])
    ->get('/users/export', [UserController::class, 'export'])
    ->name('users.export');

// Import
Route::middleware(['auth', 'permission:users.import'])
    ->post('/users/import', [UserController::class, 'import'])
    ->name('users.import');

// Tải tệp mẫu import
Route::middleware(['auth', 'permission:users.import'])
    ->get('/users/import/template', [UserController::class, 'template'])
    ->name('users.template');

// Xem danh sách
Route::middleware(['auth', 'permission:users.view'])
    ->get('/users', [UserController::class, 'index'])
    ->name('users.index');

// Tạo mới
Route::middleware(['auth', 'permission:users.create'])
    ->get('/users/create', [UserController::class, 'create'])
    ->name('users.create');

Route::middleware(['auth', 'permission:users.create'])
    ->post('/users', [UserController::class, 'store'])
    ->name('users.store');

// Cập nhật
Route::middleware(['auth', 'permission:users.update'])
    ->get('/users/{user}/edit', [UserController::class, 'edit'])
    ->name('users.edit');

Route::middleware(['auth', 'permission:users.update'])
    ->put('/users/{user}', [UserController::class, 'update'])
    ->name('users.update');

// Xóa
Route::middleware(['auth', 'permission:users.delete'])
    ->delete('/users/{user}', [UserController::class, 'destroy'])
    ->withTrashed()
    ->name('users.destroy');