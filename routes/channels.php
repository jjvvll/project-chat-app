<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat-channel.{receiverId}.{senderId}', function (User $user, $receiverId, $senderId) {
    return  (int) $user->id === (int) $senderId || (int) $user->id === (int) $receiverId;
});

Broadcast::channel('unread-channel.{reiceverId}', function(User $user, $receiverId){
     return (int) $user->id === (int) $receiverId;
});
