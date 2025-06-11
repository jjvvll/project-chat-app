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
use App\Events\listenEditedMessage;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Imagick;
 use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;


class Chat extends Component
{
    use WithFileUploads;

    public $userId;
    public $message; //whatever we type in that input field. it will be captured by this variable
    public $senderId;
    public $receiverId;

    public $messages;

    public $file;
    public $files  = [];
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
    public array $selectedIndices = [];
    public $isTempUpload = false;
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

        if (empty($this->message) && empty($this->files)) {
            return; // Do nothing if the message is empty
        }

        $sentMessage = $this->saveMessage();
        $this->files = []; //empty the files
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

        if (empty($this->message) && empty($this->files)) {
            return; // Do nothing if message and files are empty
        }

        if ($this->files && is_array($this->files)) {
            $fileNames = [];
            $fileOriginalNames = [];
            $folderPaths = [];
            $fileTypes = [];
            $thumbnailPath = [];

            foreach ($this->files as $index => $file) {
                $fileNames[] = $file->hashName();
                $fileOriginalNames[] = $file->getClientOriginalName();
                $folderPaths[] = $file->store('chat_files', 'public');
                $fileTypes[] = $file->getMimeType();

                //convert that to a full path for Imagick:
                $fullPath = storage_path('app/public/' . $folderPaths[$index]);

                // Then pass $fullPath to your generateThumbnail function:
                $thumbnailPath[] = $this->generateThumbnail($fullPath);

            }
        }

        return Message::create([
        'sender_id' =>  $this->senderId ,
        'receiver_id' =>   $this->receiverId,
        'message' =>  $this->message,
        'file_name' => isset($fileNames) ? json_encode($fileNames) : null,
        'file_original_name' => isset($fileOriginalNames) ? json_encode($fileOriginalNames) : null,
        'folder_path' => isset($folderPaths) ? json_encode($folderPaths) : null,
        'file_type' => isset($fileTypes) ? json_encode($fileTypes) : null,
        'thumbnail_path' => isset($thumbnailPath) ? json_encode($thumbnailPath) : null,
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
            foreach ($this->messages as $message) {
                if (stripos($message->message, $this->search) !== false) {
                    $this->matchedIndexes[] = $message->id;
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

        #reset the messages to pevent lag
        $this->messages =  $this->getMessages();
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

        // Decode JSON arrays from DB
        // $fileNames = json_decode($message->file_name, true) ?: [];
        $fileOriginalNames = json_decode($message->file_original_name, true) ?: [];
        $folderPaths = json_decode($message->folder_path, true) ?: [];
        // $fileTypes = json_decode($message->file_type, true) ?: [];
        $thumbnailPaths = json_decode($message->thumbnail_path, true) ?: [];

        $newFileNames = [];
        $newFilePaths = [];
        $newThumbnailFilePath  = [];

       if($folderPaths){
            foreach($folderPaths as $index => $folderpath){


                $extension = pathinfo($folderpath, PATHINFO_EXTENSION);

                // Generate a new file name (e.g., with unique ID or timestamp)
                $newFileNames[$index] = 'copy_' . Str::random(8) . '_' . pathinfo($fileOriginalNames[$index], PATHINFO_FILENAME) . '.' . $extension;
                $newFilePaths[$index] = 'chat_files/' . $newFileNames[$index];

                $thumbnailExtension = pathinfo($thumbnailPaths[$index], PATHINFO_EXTENSION);

                $newThumbnailName = pathinfo($newFileNames[$index], PATHINFO_FILENAME) . '_thumb.' . $thumbnailExtension;
                $newThumbnailFilePath[$index] = 'chat_files/thumbnails/' . $newThumbnailName;

                $relativePath = str_replace(storage_path('app/public/storage/') . DIRECTORY_SEPARATOR, '', $thumbnailPaths[$index]);

                // Remove the 'storage/' prefix if it exists
                if (str_starts_with($relativePath, 'storage/')) {
                    $relativePath = substr($relativePath, strlen('storage/'));
                }

                // Copy the file
                if (Storage::disk('public')->exists($folderpath) && Storage::disk('public')->exists($relativePath) ) {
                    Storage::disk('public')->copy($folderpath, $newFilePaths[$index]);
                    Storage::disk('public')->copy($relativePath,  $newThumbnailFilePath[$index]);
                }
            }
        }

        // Create the new message
        $sentMessage = Message::create([
            'sender_id' => $this->senderId,
            'receiver_id' => $recipientId,
            'message' => $message->message,
            'file_name' => !empty($newFileNames) ? json_encode($newFileNames) : null,
            'file_original_name' => $message->file_original_name,
            'folder_path' => !empty($newFilePaths) ? json_encode($newFilePaths) : null,
            'thumbnail_path' => !empty($newThumbnailFilePath) ? json_encode($newThumbnailFilePath) : null,
            'file_type' => $message->file_type,
            'is_read' => false,
            'is_forwarded' => true,
        ]);

        #broascast the message
        broadcast(event: new MessageSentEvent($sentMessage));


        $this->showForwardModal = false;
        session()->flash('message', 'Message forwarded!');
    }





    public function goToParentMessage( $messageId){

        $message = Message::findOrFail($messageId);

        $exists = $this->messages->contains('id', $message->parent->id);


        if (!$exists) {
            // Not in the local collection, run the query

            $message = Message::findOrFail($messageId);

            $timestamp = $message->parent ? $message->parent->created_at : $message->created_at;

            $this->messages = Message::where(function ($query) use ($message) {
                $query->where(function ($q) use ($message) {
                    $q->where('sender_id', $message->sender_id)
                    ->where('receiver_id', $message->receiver_id);
                })->orWhere(function ($q) use ($message) {
                    $q->where('sender_id', $message->receiver_id)
                    ->where('receiver_id', $message->sender_id);
                });
            })
            ->where('created_at', '>=', $timestamp)
            ->orderBy('created_at', 'asc')
            ->get();
        }



        $this->dispatch('scroll-to-message', index: $message->parent->id);

    }

public function removeFile($index = null)
{
    if ($index === 'all') {
        $this->files = [];
    } elseif (is_numeric($index) && isset($this->files[$index])) {
        unset($this->files[$index]);
        $this->files = array_values($this->files); // reindex array
    }
}




      public function editMessage($messageId, $currentContent)
    {
        $this->editingMessageId = $messageId;
        $this->editedContent = $currentContent;

        // $working = Message::find(707); // A message where edit works
        // $broken = Message::find(708); // Your problematic message

        // // Output all differences
        // dd([
        //     'diff' => array_diff_assoc($broken->toArray(), $working->toArray()),
        //     'is_forwarded_type' => [
        //         'working' => gettype($working->is_forwarded),
        //         'broken' => gettype($broken->is_forwarded)
        //     ]
        // ]);

    }

    public function saveEditedMessage()
    {
        $editedMessage = Message::findOrFail((int)$this->editingMessageId);

        $this->removeImage($editedMessage);

        $this->cancelEdit();
    }


    #[On('echo-private:EditedMessage-channel.{senderId}.{receiverId},listenEditedMessage')]
    public function listenEditedMessage($event){
            ///listen for reaction update in a message
        }
    public function cancelEdit()
    {
        $this->editingMessageId = null;
        $this->editedContent = '';
        $this->selectedIndices = [];
    }

    public function toggleSelectedIndex($index)
    {
        if (($key = array_search($index, $this->selectedIndices)) !== false) {
            unset($this->selectedIndices[$key]);
        } else {
            $this->selectedIndices[] = $index;
        }

        // Optional: reindex the array to keep it clean
        $this->selectedIndices = array_values($this->selectedIndices);
    }
   public function removeImage( Message $message)
    {
        // $message = Message::findOrFail($messageId);

        // Decode JSON arrays from DB
        $fileNames = json_decode($message->file_name, true) ?: [];
        $fileOriginalNames = json_decode($message->file_original_name, true) ?: [];
        $folderPaths = json_decode($message->folder_path, true) ?: [];
        $fileTypes = json_decode($message->file_type, true) ?: [];
        $thumbnailPaths = json_decode($message->thumbnail_path, true) ?: [];
            // Sort indices descending to safely remove by index without messing up offsets
        rsort($this->selectedIndices);

        foreach ($this->selectedIndices as $index) {
            if (!isset($fileNames[$index])) {
                continue; // skip invalid index
            }

            // Get filenames and paths
            // $fileName = $fileNames[$index];
            $folderPath = $folderPaths[$index] ?? null;
            $thumbnailPath = $thumbnailPaths[$index] ?? null;

            // Construct destination folder for deleted files
            $toBeDeletedFolder = storage_path('app/public/toBeDeleted');

            if (!file_exists($toBeDeletedFolder)) {
                mkdir($toBeDeletedFolder, 0755, true);
            }

            // Build new filename: owner_createdAt_date_originalName.ext
            // Assuming $message has owner info and created_at property
            $ownerName = $message->sender->name ?? 'owner'; // Adjust as needed
            $dateStr = $message->created_at->format('Ymd_His');

            // Rename and move original file if it exists
            if ($folderPath && file_exists(storage_path("app/public/{$folderPath}"))) {
                $newFileName = "{$ownerName}_{$dateStr}_" . basename($folderPath);
                rename(storage_path("app/public/{$folderPath}"), "{$toBeDeletedFolder}/{$newFileName}");
            }

            $relativePath = str_replace('storage/', '', $thumbnailPath);

            // Rename and move thumbnail file if it exists
            if ($thumbnailPath && file_exists(storage_path('app/public/' . $relativePath))) {
                $newThumbName = "{$ownerName}_{$dateStr}_" . basename($thumbnailPath);
                rename(storage_path('app/public/' . $relativePath), "{$toBeDeletedFolder}/{$newThumbName}");
            }

            // Remove the entries from arrays
            unset($fileNames[$index]);
            unset($fileOriginalNames[$index]);
            unset($folderPaths[$index]);
            unset($fileTypes[$index]);
            unset($thumbnailPaths[$index]);
            }

            // Reindex arrays so json_encode stores them correctly
            $fileNames = array_values($fileNames);
            $fileOriginalNames = array_values($fileOriginalNames);
            $folderPaths = array_values($folderPaths);
            $fileTypes = array_values($fileTypes);
            $thumbnailPaths = array_values($thumbnailPaths);

            // Update the message record with the new JSON arrays
            $message->update([
                'message' => $this->editedContent ?? null,
                'file_name' => !empty($fileNames) ? json_encode($fileNames) : null,
                'file_original_name' => !empty($fileOriginalNames) ? json_encode($fileOriginalNames) : null,
                'folder_path' => !empty($folderPaths) ? json_encode($folderPaths) : null,
                'file_type' => !empty($fileTypes) ? json_encode($fileTypes) : null,
                'thumbnail_path' => !empty($thumbnailPaths) ? json_encode($thumbnailPaths) : null,
            ]);

            if(empty($this->editedContent) && empty($fileNames)){
                $message->delete();
            }

            $this->addImageToAMessage($message);


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


                broadcast(event: new MessageDeleted($updatedMessage)); //same purpose, send an event to update message bubble of the receiver
    }

    public function addImageToAMessage($message){

         if (empty($this->files)) {
            return; // Do nothing if files is empty
        }

        if ($this->files && is_array($this->files)) {
            $fileNames = [];
            $fileOriginalNames = [];
            $folderPaths = [];
            $fileTypes = [];
            $thumbnailPaths = [];

            foreach ($this->files as $index => $file) {
                $fileNames[] = $file->hashName();
                $fileOriginalNames[] = $file->getClientOriginalName();
                $folderPaths[] = $file->store('chat_files', 'public');
                $fileTypes[] = $file->getMimeType();

                //convert that to a full path for Imagick:
                $fullPath = storage_path('app/public/' . $folderPaths[$index]);

                // Then pass $fullPath to your generateThumbnail function:
                $thumbnailPaths[] = $this->generateThumbnail($fullPath);

            }
        }

         $message->update([
                // 'message' => $this->editedContent ?? $message->message, // Keep existing if no edit

                // Merge and encode file arrays
                'file_name' => !empty($fileNames)
                    ? json_encode(array_merge(
                        json_decode($message->file_name ?? '[]', true),
                        $fileNames
                    ))
                    : $message->file_name,

                'file_original_name' => !empty($fileOriginalNames)
                    ? json_encode(array_merge(
                        json_decode($message->file_original_name ?? '[]', true),
                        $fileOriginalNames
                    ))
                    : $message->file_original_name,

                'folder_path' => !empty($folderPaths)
                    ? json_encode(array_merge(
                        json_decode($message->folder_path ?? '[]', true),
                        $folderPaths
                    ))
                    : $message->folder_path,

                'file_type' => !empty($fileTypes)
                    ? json_encode(array_merge(
                        json_decode($message->file_type ?? '[]', true),
                        $fileTypes
                    ))
                    : $message->file_type,

                'thumbnail_path' => !empty($thumbnailPaths)
                    ? json_encode(array_merge(
                        json_decode($message->thumbnail_path ?? '[]', true),
                        $thumbnailPaths
                    ))
                    : $message->thumbnail_path,
            ]);

            $this->files = [];
    }


    public function generateThumbnail($filePath)
    {
        $thumbnailDir = storage_path('app/public/chat_files/thumbnails/');

        if (!file_exists($thumbnailDir)) {
            mkdir($thumbnailDir, 0775, true);
        }

        $imagick = new \Imagick();

        try {
            $filenameWithoutExt = pathinfo($filePath, PATHINFO_FILENAME);
            $extension = strtolower($filenameWithoutExt);

            if ($extension === 'pdf') {
                $imagick->setResolution(150, 150); // For better PDF quality
                $imagick->readImage($filePath . '[0]'); // Only first page
            } else {
                $imagick->readImage($filePath);
            }

            $imagick->setImageFormat('jpeg');
            $imagick->thumbnailImage(150, 150, true);

            $thumbnailFilename = basename($filenameWithoutExt) . '.jpg';

            // Full physical path to save the image
            $thumbnailFullPath = storage_path('app/public/chat_files/thumbnails/' . $thumbnailFilename);

            $imagick->writeImage($thumbnailFullPath);



            $imagick->clear();
            $imagick->destroy();

            return 'storage/chat_files/thumbnails/' . $thumbnailFilename;

        } catch (\Exception $e) {
            logger()->error('Thumbnail generation failed: ' . $e->getMessage());
            return null;
        }
    }

    public function isTemporaryFile($file){

        return $file instanceof TemporaryUploadedFile;
    }



}
