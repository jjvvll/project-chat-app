<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Events\MessageSentEvent;
use App\Events\UserTyping;
use Livewire\Attributes\On;



class Chat extends Component
{

    public $userId;
    public $message; //whatever we type in that input field. it will be capture by this variable
    public $senderId;
    public $receiverId;

    public $messages;
    public $search;
    public $file;

    public function mount($userId){
        // dd($userId->name);

        // $this->user = $this->getUser($userId); no need we used route model binding
        $this->senderId = Auth::user()->id;
        $this->receiverId = $userId->id;

        $this->messages =  $this->getMessages();
        $this->dispatch('messages-updated');
        // dd(vars: $messages);

    }

    public function render()
    {
        return view('livewire.chat');
    }

    public function sendMessage(){
        $sentMessage = $this->saveMessage();

        #assuigning latest message
        $this->messages[] = $sentMessage;

        #broascast the message
        broadcast(new MessageSentEvent($sentMessage));

        $this->message =null;

        #dispatching event to scroll to the bottom
        $this->dispatch('messages-updated');
    }

    #[On('echo-private:chat-channel.{senderId},MessageSentEvent')]
    public function listenMessage($event){
        $newMessage =  Message::find($event['message']['id'])->load('sender:id,name', 'receiver:id,name');
        $this->messages[] = $newMessage;

         #dispatching event to scroll to the bottom
         $this->dispatch('messages-updated');
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

    public function userTyping(){

        broadcast(new UserTyping($this->senderId, $this->receiverId))->toOthers();
        //toothers make sure that the event is broadcasted to everybody else except the source
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
