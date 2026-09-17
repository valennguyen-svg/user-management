<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

// Xóa (withTrashed: để báo "đã bị xóa trước đó" thay vì 404)
Route::middleware(['auth', 'permission:users.delete'])
    ->delete('/users/{user}', [UserController::class, 'destroy'])
    ->withTrashed()
    ->name('users.destroy');
