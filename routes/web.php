<?php

use App\Http\Controllers\userController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::controller(userController::class)->group(function() {
    Route::get('dashboard', 'index')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

    Route::get('chat/{id}', 'userChat')
    ->middleware(['auth', 'verified'])
    ->name('chat');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
