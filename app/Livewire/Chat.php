<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSentEvent;
use App\Events\UnreadMessage;
use App\Events\MessageReaction;
use App\Events\MessageDeleted;
use App\Events\UserTyping;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;




class Chat extends Component
{
    use WithFileUploads;

    public $userId;
    public $message; //whatever we type in that input field. it will be captured by this variable
    public $senderId;
    public $receiverId;

    public $messages;

    public $file;
    public $lastMessageTimestamp;
    public $noMoreMessages;

     public $search         = '';
    public $matchedIndexes = [];
    public $currentMatch   = 0;
    public $replyingTo = null;


    public $searhCount ;
    public $users;
    public $showForwardModal;
    public $forwardMessageId;
    public $editingMessageId = null;
    public $editedContent = '';
    public function mount($userId){
        $this->dispatch('messages-updated');
        // dd($userId->name);

        // $this->user = $this->getUser($userId); no need we used route model binding
        $this->senderId = Auth::user()->id;
        $this->receiverId = $userId->id;

        $this->messages =  $this->getMessages();

        $this->markMessagesAsRead();

        // Exclude sender and receiver
        $excludeIds = [$this->senderId, $this->receiverId];
        $this->users = User::whereNotIn('id', $excludeIds)->get();


        // dd(vars: $messages);

    }


    public function render()
    {
        $this->messages->loadMissing('sender', 'receiver', 'parent.sender');

        $this->markMessagesAsRead();

        return view('livewire.chat');
    }

    public function sendMessage(){

        if (empty($this->message) && empty($this->file)) {
            return; // Do nothing if the message is empty
        }

        $sentMessage = $this->saveMessage();

        // 3. Add the new message (relationships stay loaded)
        $this->messages = $this->messages->push($sentMessage);

         /// Load relationships before pushing (to prevent lazy-loading)
        // $this->messages->loadMissing('sender', 'receiver', 'parent.sender');

        //    $this->messages->load([
        //     'sender',  // Direct sender
        //     'receiver',
        //     'parent' => fn($q) => $q->with('sender')  // Nested sender
        // ]);


        #broascast the message
        broadcast(event: new MessageSentEvent($sentMessage));

        $unreadMessageCount = $this->getUnreadMessagesCount();

        broadcast(new UnreadMessage($this->senderId, $this->receiverId, $unreadMessageCount))->toOthers();

        $this->message =null;
        $this->file =null;
        $this->replyingTo = null;


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

        // 1. Eager load the new message with relationships
        $newMessage = Message::find($event['message']['id']);

        // 3. Add the new message (relationships stay loaded)
        $this->messages = $this->messages->push($newMessage);

         // Load relationships before pushing (to prevent lazy-loading)
        $this->messages->loadMissing('sender', 'receiver','parent.sender'); // Adjust to your needs

         #dispatching event to scroll to the bottom
         $this->dispatch(event: 'messages-updated');
    }

    public function getMessages(){
    //    return  Message::where('sender_id', $this->senderId)
        //     ->where('receiver_id', $this->receiverId)->get();

       $fetchedMessages = Message::withTrashed()->with('sender:id,name,profile_photo', 'receiver:id,name', 'parent.sender')
            ->where(function ($query) {
                $query->where('sender_id', $this->senderId)
                    ->where('receiver_id', $this->receiverId);
            })
            ->orWhere(function ($query) {
                $query->where('sender_id', $this->receiverId)
                    ->where('receiver_id', $this->senderId);
            })
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        // Store timestamp before reversing
        $this->lastMessageTimestamp = $fetchedMessages->last()->created_at ?? now();

        // Reverse collection for display
        $fetchedMessages = $fetchedMessages->reverse()->values();

        return $fetchedMessages;
    }

    public function loadMoreMessages()
    {


         $newMessages = Message::where(function ($query) {
            $query->where(function ($q) {
                $q->where('sender_id', $this->senderId)
                ->where('receiver_id', $this->receiverId);
            })->orWhere(function ($q) {
                $q->where('sender_id', $this->receiverId)
                ->where('receiver_id', $this->senderId);
            });
        })
        ->where('created_at', '<', $this->lastMessageTimestamp)
        ->orderBy('created_at', 'desc')
        ->limit(30)
        ->get();


        if ($newMessages->isNotEmpty()) {
            // Update the last message timestamp
            $this->lastMessageTimestamp = $newMessages->last()->created_at;

            // Merge new messages with existing ones (prepend to maintain chronological order)
            $this->messages = $newMessages->reverse()
                ->merge($this->messages)
                ->values();

             // Load relationships before pushing (to prevent lazy-loading)
        $this->messages->loadMissing('sender', 'receiver', 'parent.sender');

        }else{
            $this->noMoreMessages = true; // Set flag when no more messages
        }
    }


    // public function userTyping(){

    //     broadcast(new UserTyping($this->senderId, $this->receiverId))->toOthers();
    //     //toothers make sure that the event is broadcasted to everybody else except the source
    // }

    public function markMessagesAsRead(){
        Message::where('sender_id', $this->receiverId)
        ->where('receiver_id', $this->senderId)
        ->where('is_read', false)
        ->update(['is_read' => true]);
    }

    // function for sending message
    public function saveMessage(){


        // dd($this->replyingTo);

        if (empty($this->message) && empty($this->file)) {
            return; // Do nothing if the message is empty
        }

       if($this->file){

            $fileName = $this->file->hashName();
            $fileOriginalName = $this->file->getClientOriginalName();
            $folder_path = $this->file->store('chat_files', 'public');
            $fileType = $this->file->getMimeType();
       }

        return Message::create([
        'sender_id' =>  $this->senderId ,
        'receiver_id' =>   $this->receiverId,
        'message' =>  $this->message,
        'file_name' => $fileName ?? null,
        'file_original_name' => $fileOriginalName ?? null,
        'folder_path' => $folder_path ?? null,
        'file_type' =>  $fileType ?? null,
        'is_read' => false,
        'parent_id' => $this->replyingTo['id'] ?? null]);

    }

     public function highlightText($text, $search)
    {
        // Escape the search term to safely insert into the regex.
        $escaped = preg_quote($search, '/');
        // Use preg_replace to wrap matched terms in a <mark> tag.
        return preg_replace('/(' . $escaped . ')/i', '<mark>$1</mark>', e($text));
    }

    public function loadAllMatchingMessages()
    {
           $this->noMoreMessages = false; //the purpose of this is to reset load new messages button whenever this button is used

        if (empty($this->search)) return;

        // Step 1: Find the oldest matching message
        $oldestMatch = Message::with('sender:id,name,profile_photo', 'receiver:id,name', 'parent.sender')
        ->where(column: function ($query) {
                $query->where('sender_id', $this->senderId)
                    ->where('receiver_id', $this->receiverId)
                    ->orWhere(function ($q) {
                        $q->where('sender_id', $this->receiverId)
                            ->where('receiver_id', $this->senderId);
                    });
            })
            ->where('message', 'like', '%' . $this->search . '%')
            ->orderBy('created_at') // oldest first
            ->first();

        if ($oldestMatch) {
            // Step 2: Get messages from that point forward
            $this->messages = Message::with('sender:id,name,profile_photo', 'receiver:id,name', 'parent.sender')
        ->where(function ($query) {
                    $query->where('sender_id', $this->senderId)
                        ->where('receiver_id', $this->receiverId)
                        ->orWhere(function ($q) {
                            $q->where('sender_id', $this->receiverId)
                                ->where('receiver_id', $this->senderId);
                        });
                })
                ->where('created_at', '>=', $oldestMatch->created_at)
                ->orderBy('created_at')
                ->get();

            // Step 3: Re-run the search to highlight matches
            $this->updatedSearch();

            $this->lastMessageTimestamp = $oldestMatch->created_at;
        }
    }

    public function updatedSearch()
    {
        $this->messages->loadMissing('sender', 'receiver', 'parent.sender');

        # Reset matches on new search
        $this->matchedIndexes = [];

        if (! empty($this->search)) {
            foreach ($this->messages as $index => $message) {
                if (stripos($message->message, $this->search) !== false) {
                    $this->matchedIndexes[] = $index;
                }
            }
        }

        $this->searhCount = count($this->matchedIndexes);

        // Reset to first match
        $this->currentMatch = count($this->matchedIndexes) > 0 ? 0 : -1;

        // Scroll to the first match
        if ($this->currentMatch !== -1) {
            $this->scrollToMatch();
        }
    }

    /**
     * Function: nextMatch
     */
    public function nextMatch()
    {

        if (count($this->matchedIndexes) > 0) {
            $this->currentMatch = ($this->currentMatch + 1) % count($this->matchedIndexes);
            $this->scrollToMatch();
        }

           $this->messages->loadMissing('sender', 'receiver', 'parent.sender');
    }

    /**
     * Function: prevMatch
     */
    public function prevMatch()
    {

        if (count($this->matchedIndexes) > 0) {
            $this->currentMatch = ($this->currentMatch - 1 + count($this->matchedIndexes)) % count($this->matchedIndexes);
            $this->scrollToMatch();
        }
           $this->messages->loadMissing('sender', 'receiver', 'parent.sender');
    }

    /**
     * Function: scrollToMatch
     */
    public function scrollToMatch()
    {

        $index = $this->matchedIndexes[$this->currentMatch] ?? null;

        if (! is_null($index)) {
            $this->dispatch('scroll-to-message', index: $index);
        }
    }

    /**
     * Function: resetSearch
     */
    public function resetSearch()
    {
        $this->searhCount = '';

        $this->search         = '';
        $this->matchedIndexes = [];
        $this->currentMatch   = -1;

        # Scroll to latest message
        $this->dispatch('messages-updated');
    }

  public function addReaction($emoji, $messageId)
    {
        $message = Message::findOrFail($messageId);

        // Save the emoji to the 'reaction' column
        $message->reaction = $emoji;
        $message->save();

        $updatedMessage = Message::find($messageId);

          // Update the message in the local Livewire collection
        if ($this->messages instanceof \Illuminate\Support\Collection) {
            // If it's a Collection
            $this->messages = $this->messages->map(function ($msg) use ($updatedMessage) {
                return $msg->id === $updatedMessage->id ? $updatedMessage : $msg;
            });
        } else {
            // If it's an array
            foreach ($this->messages as $i => $msg) {
                if ($msg['id'] === $updatedMessage->id) {
                    $this->messages[$i] = $updatedMessage   ;
                    break;
                }
            }
        }



        $this->messages->loadMissing('sender', 'receiver', 'parent.sender');
        broadcast(event: new MessageReaction($message));

    }


    #[On('echo-private:reaction-channel.{receiverId}.{senderId},MessageReaction')]
    public function listenMessageReaction($event){
    ///listen for reaction update in a message
    }

    public function deleteMessage($messageId)
    {
        $message = Message::findOrFail($messageId);

        // Verify the user owns the message before deleting
        if ($this->senderId === Auth::user()->id) {
             $message->reaction = '';
                $message->save();
            // Soft delete the message
             $message->delete();

            $updatedMessage = Message::withTrashed()->find($message->id);

                // Update the message in the local Livewire collection
                if ($this->messages instanceof \Illuminate\Support\Collection) {
                    // If it's a Collection
                    $this->messages = $this->messages->map(function ($msg) use ($updatedMessage) {
                        return $msg->id === $updatedMessage->id ? $updatedMessage : $msg;
                    });
                } else {
                    // If it's an array
                    foreach ($this->messages as $i => $msg) {
                        if ($msg['id'] === $updatedMessage->id) {
                            $this->messages[$i] = $updatedMessage   ;
                            break;
                        }
                    }
                }

                $this->messages->loadMissing('sender', 'receiver', 'parent.sender');

                broadcast(event: new MessageDeleted($updatedMessage)); //same purpose, send an event to update message bubble of the receiver

        }
    }

    #[On('echo-private:deleted-channel.{senderId}.{receiverId},MessageDeleted')]
    public function listenMessageDeleted($event){
        //listen for deleted message update

    }


   public function startReply($messageId, $messageContent = null)
    {
        $this->replyingTo = [
            'id' => $messageId,
            'content' => $messageContent
        ];

        $this->dispatch('focus-message-input');
    }

    public function openForwardModal( $messageId)
    {
        $this->forwardMessageId = $messageId;
        $this->showForwardModal = true;
    }

    public function forwardMessage($recipientId)
    {

        $message = Message::findOrFail($this->forwardMessageId);

        $originalPath = $message->folder_path; // already includes filename
        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);

        // Generate a new file name (e.g., with unique ID or timestamp)
        $newFileName = 'copy_' . Str::random(8) . '.' . $extension;
        $newFilePath = 'chat_files/' . $newFileName;

        // Copy the file
        if (Storage::disk('public')->exists($originalPath)) {
           Storage::disk('public')->copy($originalPath, $newFilePath);

        }

        // Create the new message
        $sentMessage = Message::create([
            'sender_id' => $this->senderId,
            'receiver_id' => $recipientId,
            'message' => $message->message,
            'file_name' => $newFileName,
            'file_original_name' => $message->file_original_name,
            'folder_path' => $newFilePath, // this is full path: chat_files/filename
            'file_type' => $message->file_type,
            'is_read' => false,
            'is_forwarded' => true,
        ]);
        #broascast the message
        broadcast(event: new MessageSentEvent($sentMessage));


        $this->showForwardModal = false;
        session()->flash('message', 'Message forwarded!');
    }




    public function editMessage($messageId, $currentContent)
    {
        $this->editingMessageId = $messageId;
        $this->editedContent = $currentContent;

    }

    public function saveEditedMessage()
    {
        $editedMessage = Message::findOrFail((int)$this->editingMessageId);
        $editedMessage->update(['message' => $this->editedContent]);

    // Update the message in the local Livewire collection
                if ($this->messages instanceof \Illuminate\Support\Collection) {
                    // If it's a Collection
                    $this->messages = $this->messages->map(function ($msg) use ($editedMessage) {
                        return $msg->id === $editedMessage->id ? $editedMessage : $msg;
                    });
                } else {
                    // If it's an array
                    foreach ($this->messages as $i => $msg) {
                        if ($msg['id'] === $editedMessage->id) {
                            $this->messages[$i] = $editedMessage   ;
                            break;
                        }
                    }
                }

        $this->cancelEdit();
    }

    public function cancelEdit()
    {
        $this->editingMessageId = null;
        $this->editedContent = '';
    }

}
