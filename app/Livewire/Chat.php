<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;


class Chat extends Component
{

    public $userId;
    public $message; //whatever we type in that input field. it will be capture by this variable
    public $senderId;
    public $receiverId;

    public $messages;
    public $search;
    public $file;
    public $userTyping;

    public function mount($userId){
        // dd($userId->name);

        // $this->user = $this->getUser($userId); no need we used route model binding
        $this->senderId = Auth::user()->id;
        $this->receiverId = $userId->id;
        $this->messages =  $this->getMessages();
        // dd(vars: $messages);

    }

    public function render()
    {
        return view('livewire.chat');
    }

    public function sendMessage(){
        $this->saveMessage();

        $this->message =null;
    }

    public function getMessages(){
    //    return  Message::where('sender_id', $this->senderId)
    //     ->where('receiver_id', $this->receiverId)->get();

         return Message::with('sender:id,name', 'receiver:id,name')
        ->where(function($query){
            $query->where('sender_id', $this->senderId)
            ->where('receiver_id', $this->receiverId);
        })  ->orWhere(function($query){
            $query->where('sender_id', $this->receiverId)
            ->where('receiver_id', $this->senderId);
        })->get();
    }

    // function for sending message
    public function saveMessage(){
        return Message::create([
        'sender_id' =>  $this->senderId ,
        'receiver_id' =>   $this->receiverId,
        'message' =>  $this->message,
        // 'file_name',
        // 'file_original_name',
        // 'folder_path',
        'is_read' => false]);
    }
}
