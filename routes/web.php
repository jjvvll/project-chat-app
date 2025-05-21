<?php

use App\Http\Controllers\userController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::controller(userController::class)->group(function() {
    Route::get('dashboard', 'index')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

    Route::get('chat/{userId}', 'userChat')
    ->middleware(['auth', 'verified'])
    ->name('chat');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';


Route::post('/users/{userId}/add-profile-picture', [UserController::class, 'AddProfilePicture'])
    ->name('users.addProfilePicture');


use App\Events\UserTyping;
use Illuminate\Http\Request;

Route::post('/broadcast-typing', function (Request $request) {
    broadcast(new UserTyping($request->sender_id, $request->receiver_id))->toOthers();
    return response()->noContent();
});
