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

    public function mount($userId){
        // dd($userId->name);

        // $this->user = $this->getUser($userId); no need we used route model binding
        $this->senderId = Auth::user()->id;
        $this->receiverId = $userId->id;
    }

    public function render()
    {
        return view('livewire.chat');
    }

    public function sendMessage(){
        $this->saveMessage();

        $this->message =null;
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
