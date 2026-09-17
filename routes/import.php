<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Import
Route::middleware(['auth', 'permission:users.import'])
    ->post('/users/import', [UserController::class, 'import'])
    ->name('users.import');

// Tải tệp mẫu import (FR-11)
Route::middleware(['auth', 'permission:users.import'])
    ->get('/users/import/template', [UserController::class, 'template'])
    ->name('users.template');
