<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('users.index');
});

include __DIR__.'/auth.php';
