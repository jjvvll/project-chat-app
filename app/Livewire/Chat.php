<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Events\MessageSentEvent;
use App\Events\UnreadMessage;
use App\Events\UserTyping;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;



class Chat extends Component
{
    use WithFileUploads;

    public $userId;
    public $message; //whatever we type in that input field. it will be capture by this variable
    public $senderId;
    public $receiverId;

    public $messages;
    public $search;
    public $file;

    public function mount($userId){
        $this->dispatch('messages-updated');
        // dd($userId->name);

        // $this->user = $this->getUser($userId); no need we used route model binding
        $this->senderId = Auth::user()->id;
        $this->receiverId = $userId->id;

        $this->messages =  $this->getMessages();

        $this->markMessagesAsRead();

        // dd(vars: $messages);

    }

    public function render()
    {
        $this->markMessagesAsRead();
        return view('livewire.chat');
    }

    public function sendMessage(){

        $sentMessage = $this->saveMessage();

        #assuigning latest message
        $this->messages[] = $sentMessage;
        // dd($this->receiverId. "----".$sentMessage->sender_id );

        #broascast the message
        broadcast(new MessageSentEvent($sentMessage));

        $unreadMessageCount = $this->getUnreadMessagesCount();

        broadcast(new UnreadMessage($this->senderId, $this->receiverId, $unreadMessageCount))->toOthers();

        $this->message =null;
        $this->file =null;

        #dispatching event to scroll to the bottom
        $this->dispatch('messages-updated');
    }

    public function sendFileMessage(){

    }


    public function getUnreadMessagesCount(){
        return Message::where('receiver_id', $this->receiverId)
        ->where('sender_id', $this->senderId)
        ->where('is_read', false)->count();
    }

    #[On('echo-private:chat-channel.{senderId}.{receiverId},MessageSentEvent')]
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

    public function markMessagesAsRead(){
        Message::where('sender_id', $this->receiverId)
        ->where('receiver_id', $this->senderId)
        ->where('is_read', false)
        ->update(['is_read' => true]);
    }

    // function for sending message
    public function saveMessage(){

       if($this->file){

            $fileName = $this->file->hashName();
            $fileOriginalName = $this->file->getClientOriginalName();
            $forlderPath = $this->file->store('chat_files', 'public');
            $fileType = $this->file->getMimeType();
       }

        return Message::create([
        'sender_id' =>  $this->senderId ,
        'receiver_id' =>   $this->receiverId,
        'message' =>  $this->message,
        'file_name' => $fileName ?? null,
        'file_original_name' => $fileOriginalName ?? null,
        'folder_path' => $forlderPath ?? null,
        'file_type' =>  $fileType ?? null,
        'is_read' => false]);
    }
}
