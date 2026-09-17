<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Export
Route::middleware(['auth', 'permission:users.export'])
    ->get('/users/export', [UserController::class, 'export'])
    ->name('users.export');
