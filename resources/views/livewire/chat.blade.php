{{-- Livewire Chat Component --}}
<div>
    <div class="bg-white py-3">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __($user->name ?? null) }}
                </h2>

                {{-- Search Input --}}
                <div class="relative w-1/3">
                    <input type="text" wire:model.live="search" placeholder="Search Messages..."
                        class="pl-10 pr-16 py-2 border rounded-md w-full focus:outline-none focus:ring-2 focus:ring-blue-400">

                    {{-- Search Icon (Left) --}}
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </span>

                    {{-- Clear Button --}}
                    @if (!empty($search))
                        <button type="button" wire:click="resetSearch"
                            class="absolute right-20 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="red" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </button>

                        <!-- Navigation Icons (Right) -->
                        <div wire:ignore.self class="absolute right-3 top-1/2 transform -translate-y-1/2 flex space-x-2">

                            {{-- Arrow up --}}
                            <button type="button" @click="$wire.prevMatch()"  class="text-gray-500 hover:text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                                </svg>
                            </button>

                            {{-- Arrow down --}}
                            <button type="button"  @click="$wire.nextMatch()" class="text-gray-500 hover:text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
                        <div>
                             <button wire:click="loadAllMatchingMessages"
                                class="mt-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                Load All Matching Messages
                            </button>
                            <p>Results: {{$searhCount}}</p>
                        </div>
            </div>
        </div>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div id="chat-container"
                class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-15 overflow-y-auto h-[calc(100vh-12rem)] scroll-smooth ">

                <div class="w-full px-5 py-8 grow" id="message-list">
                   <div class="flex justify-center">
                        @if (!$noMoreMessages)
                            <button wire:click="loadMoreMessages"
                                    class="text-sm px-3 py-1 mb-2 rounded bg-gray-200 hover:bg-gray-300">
                                Load More
                            </button>
                        @else
                            <p class="text-sm text-gray-500 mb-2">You've reached the end</p>
                        @endif
                    </div>
                    @foreach ($messages as $index => $message)

                      {{-- @if ($message->relationLoaded('sender'))
                            <p>Sender is eager loaded: {{ $message->sender->name }}</p>
                        @else
                            <p>Sender is NOT eager loaded</p>
                        @endif

                        @if ($message->relationLoaded('receiver'))
                            <p>Receiver is eager loaded: {{ $message->receiver->name }}</p>
                        @else
                            <p>Receiver is NOT eager loaded</p>
                        @endif --}}

                        {{-- Message wrapper --}}

                <div id="message-{{ $message->id }}">
                        @php
                            $isSender = $message->sender->id === auth()->user()->id;
                            $timestamp = $message->created_at->isToday()
                                ? $message->created_at->diffForHumans()
                                : ($message->created_at->isCurrentWeek()
                                    ? $message->created_at->format('l \a\t h:i A')
                                    : $message->created_at->format('M j, Y \a\t h:i A'));
                        @endphp

                        <div class=" flex {{ $isSender ? 'justify-end' : 'justify-start' }} pb-3 ">
                            <div class=" max-w-[50%] {{ (int)$editingMessageId === $message->id ? 'w-full' : 'flex gap-2.5' }}">
                                @unless($isSender)
                                    {{-- <img src="{{ asset('storage/' . $message->sender->profile_photo) }}" alt="Profile"
                                        class="w-10 h-11 rounded-full"> --}}
                                        {{-- the current user is the receiver, which is why we're displaying photos of the sender only --}}
                                        @if($message->sender->profile_photo)
                                            <img src="{{ asset('storage/' . $message->sender->profile_photo) }}"
                                                alt="{{ $message->sender->name }}"
                                                class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
                                        @else
                                            <!-- Fallback avatar with initials -->
                                            <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-sm">
                                                {{ strtoupper(substr($message->sender->name, 0, 1)) }}
                                            </div>
                                        @endif
                                @endunless

                            <x-message-actions :message="$message" :isSender="$isSender" :showForwardModal="$showForwardModal" :users="$users" >

                                <div>
                                    <h5 class="text-sm font-semibold {{ $isSender ? 'text-right' : '' }}">
                                        {{ $isSender ? 'You' : $message->sender->name }}
                                    </h5>

                                {{-- <div class="{{$message->parent_id ? ($isSender ? 'bg-indigo-600 rounded-tr-none rounded-3xl px-3 pt-4' : 'bg-gray-200 rounded-tl-none rounded-3xl px-3 pt-4') : '' }} "> --}}
                                    <x-message-reply-bubble :message="$message" :isSender="$isSender"  :editingMessageId="$editingMessageId ?? null" :index="$index ?? null">

                                        <x-message-reactions :message="$message" :isSender="$isSender" >
                                            <x-message-bubble :message="$message" :selectedIndices="$selectedIndices ?? null" :isSender="$isSender" :search="$search"   :editingMessageId="$editingMessageId ?? null" :editedContent="$editedContent ?? null"/>
                                        </x-messsage-reactions>
                                    </x-message-reply-bubble>
                                {{-- </div> --}}
                                </x-message-actions>

                                    <h6 class="text-xs text-gray-500 mt-1 {{  $isSender ? 'ml-auto' : 'mr-auto'}}">
                                        {{ $timestamp }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    @endforeach

                     <x-message-forward :showForwardModal="$showForwardModal" :users="$users"/>
                </div>

                {{-- Bottom Message Search Box --}}

                <div class="relative">
                <!-- Typing Indicator (Hidden by default) -->
                    <div id="typing-indicator" class="hidden absolute -top-7 left-8 flex items-center space-x-2">
                        <img
                        src="{{ asset('storage/images/typing-indicator.gif') }}"
                        width="60"
                        alt="Typing..."
                        class="h-7 object-contain"
                        >
                        <span class="text-sm text-gray-500 font-medium">Typing...</span>  <!-- Larger text -->
                    </div>
                </div>
                <form wire:submit="sendMessage"
                    class="w-full px-3 py-2 border-t border-gray-200 bg-white sticky bottom-0">
                    <div
                        class="w-full pl-3 pr-1 py-1 rounded-3xl border border-gray-200 items-center gap-2 flex justify-between">

                        <div class="flex w-full items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22"
                                viewBox="0 0 22 22" fill="none">
                                <g id="User Circle">
                                    <path id="icon"
                                        d="M6.05 17.6C6.05 15.3218 8.26619 13.475 11 13.475C13.7338 13.475 15.95 15.3218 15.95 17.6M13.475 8.525C13.475 9.89191 12.3669 11 11 11C9.6331 11 8.525 9.89191 8.525 8.525C8.525 7.1581 9.6331 6.05 11 6.05C12.3669 6.05 13.475 7.1581 13.475 8.525ZM19.25 11C19.25 15.5563 15.5563 19.25 11 19.25C6.44365 19.25 2.75 15.5563 2.75 11C2.75 6.44365 6.44365 2.75 11 2.75C15.5563 2.75 19.25 6.44365 19.25 11Z"
                                        stroke="#4F46E5" stroke-width="1.6" />
                                </g>
                            </svg>


                            <div class=" relative w-full" > <!-- Fixed width container -->
                                <!-- Floating reply preview -->
                                @if($replyingTo)
                                    <div class="absolute bottom-full left-0 mb-1 w-full z-10">
                                        <div class="w-fit max-w-full bg-gray-100 p-2 rounded text-sm text-gray-600 shadow-sm">
                                            <div class="flex justify-between items-start">
                                                <div class="min-w-0"> <!-- Prevent text overflow -->
                                                    <span class="font-medium">Replying to:</span>
                                                    <span class="italic truncate">"{{ Str::limit($replyingTo['content'], 50) }}"</span>
                                                </div>
                                                <button
                                                    wire:click.prevent="$set('replyingTo', null)"
                                                    class="flex-shrink-0 text-gray-400 hover:text-gray-600 text-lg ml-2"
                                                >
                                                    &times;
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Text input -->
                                <textarea
                                    autocomplete="off"
                                    x-data="{
                                                insertNewline() {
                                                    const el = this.$el;
                                                    const start = el.selectionStart;
                                                    const end = el.selectionEnd;
                                                    const value = el.value;

                                                    el.value = value.slice(0, start) + '\n' + value.slice(end);
                                                    el.selectionStart = el.selectionEnd = start + 1;

                                                    // Trigger Livewire or Alpine reactivity if needed
                                                    el.dispatchEvent(new Event('input'));
                                                }
                                            }"

                                        x-ref="textarea"
                                        x-init="$nextTick(() => {
                                            $refs.textarea.style.height = 'auto';
                                            $refs.textarea.style.height = $refs.textarea.scrollHeight + 'px';
                                        })"
                                        @input="$refs.textarea.style.height = 'auto';
                                                $refs.textarea.style.height = $refs.textarea.scrollHeight + 'px';"


                                    @keydown.enter.prevent="$event.shiftKey ? insertNewline() : $wire.sendMessage()"

                                    wire:model.defer="message"
                                    id="message-input"
                                    class="resize-none w-full text-black text-sm font-medium rounded leading-4 focus:outline-none px-3 py-2 border border-gray-300"
                                    placeholder="Type here...">
                                </textarea>
                            </div>

                        </div>

                        <div class="flex items-center gap-2">

                            {{-- Attachment --}}
                            <label title="Attachment" for="fileUpload" class="cursor-pointer flex items-center">

                                <input type="file" id="fileUpload" wire:model="files" multiple class="hidden">


                                <svg class="cursor-pointer" xmlns="http://www.w3.org/2000/svg" width="22"
                                    height="22" viewBox="0 0 22 22" fill="none">
                                    <g id="Attach 01">
                                        <g id="Vector">
                                            <path
                                                d="M14.9332 7.79175L8.77551 14.323C8.23854 14.8925 7.36794 14.8926 6.83097 14.323C6.294 13.7535 6.294 12.83 6.83097 12.2605L12.9887 5.72925M12.3423 6.41676L13.6387 5.04176C14.7126 3.90267 16.4538 3.90267 17.5277 5.04176C18.6017 6.18085 18.6017 8.02767 17.5277 9.16676L16.2314 10.5418M16.8778 9.85425L10.72 16.3855C9.10912 18.0941 6.49732 18.0941 4.88641 16.3855C3.27549 14.6769 3.27549 11.9066 4.88641 10.198L11.0441 3.66675"
                                                stroke="#9CA3AF" stroke-width="1.6" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path
                                                d="M14.9332 7.79175L8.77551 14.323C8.23854 14.8925 7.36794 14.8926 6.83097 14.323C6.294 13.7535 6.294 12.83 6.83097 12.2605L12.9887 5.72925M12.3423 6.41676L13.6387 5.04176C14.7126 3.90267 16.4538 3.90267 17.5277 5.04176C18.6017 6.18085 18.6017 8.02767 17.5277 9.16676L16.2314 10.5418M16.8778 9.85425L10.72 16.3855C9.10912 18.0941 6.49732 18.0941 4.88641 16.3855C3.27549 14.6769 3.27549 11.9066 4.88641 10.198L11.0441 3.66675"
                                                stroke="black" stroke-opacity="0.2" stroke-width="1.6"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                            <path
                                                d="M14.9332 7.79175L8.77551 14.323C8.23854 14.8925 7.36794 14.8926 6.83097 14.323C6.294 13.7535 6.294 12.83 6.83097 12.2605L12.9887 5.72925M12.3423 6.41676L13.6387 5.04176C14.7126 3.90267 16.4538 3.90267 17.5277 5.04176C18.6017 6.18085 18.6017 8.02767 17.5277 9.16676L16.2314 10.5418M16.8778 9.85425L10.72 16.3855C9.10912 18.0941 6.49732 18.0941 4.88641 16.3855C3.27549 14.6769 3.27549 11.9066 4.88641 10.198L11.0441 3.66675"
                                                stroke="black" stroke-opacity="0.2" stroke-width="1.6"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </g>
                                    </g>
                                </svg>
                            </label>

                            {{-- Submit button --}}
                            <button type="submit"
                                class="items-center flex px-3 py-2 bg-indigo-600 rounded-full shadow ">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 16 16" fill="none">
                                    <g id="Send 01">
                                        <path id="icon"
                                            d="M9.04071 6.959L6.54227 9.45744M6.89902 10.0724L7.03391 10.3054C8.31034 12.5102 8.94855 13.6125 9.80584 13.5252C10.6631 13.4379 11.0659 12.2295 11.8715 9.81261L13.0272 6.34566C13.7631 4.13794 14.1311 3.03408 13.5484 2.45139C12.9657 1.8687 11.8618 2.23666 9.65409 2.97257L6.18714 4.12822C3.77029 4.93383 2.56187 5.33664 2.47454 6.19392C2.38721 7.0512 3.48957 7.68941 5.69431 8.96584L5.92731 9.10074C6.23326 9.27786 6.38623 9.36643 6.50978 9.48998C6.63333 9.61352 6.72189 9.7665 6.89902 10.0724Z"
                                            stroke="white" stroke-width="1.6" stroke-linecap="round" />
                                    </g>
                                </svg>
                                <h3 class="text-white text-xs font-semibold leading-4 px-2">Send</h3>
                            </button>
                        </div>


                        {{-- File Preview --}}
                        @if ($files)
                            <div x-data="{ showModal: false }">
                                <!-- Thumbnail previews -->
                                <div class="flex items-center gap-2">
                                    @foreach ($files as $index => $file)
                                        @if ($index < 3)
                                             @php
                                                $imgType = str_starts_with($file->getMimeType(), 'image/') ? true : false;
                                            @endphp

                                            <span>
                                                @if ($imgType)
                                                    <img src="{{ $file->temporaryUrl() }}" alt="file"
                                                        class="w-12 h-12 rounded-lg object-cover border border-gray-300 shadow-md" />
                                                @else
                                                    <span class="w-full max-w-64">{{ $file->getClientOriginalName() }}</span>
                                                @endif
                                            </span>
                                        @elseif ($index === 3)
                                            @php $remaining = count($files) - 3; @endphp

                                            <button
                                                @click.prevent="showModal = true"
                                                class="w-16 h-16 bg-gray-200 text-gray-800 rounded flex items-center justify-center text-sm font-semibold shadow"
                                            >
                                                +{{ $remaining }}
                                            </button>
                                            @break
                                        @endif
                                    @endforeach
                                     <button type="button"  wire:click.prevent="removeFile('all')"
                                        class="text-red-500 ms-2"><span class="text-2xl">x</span></button>
                                </div>

                                <!-- Modal -->
                                <div
                                    x-show="showModal"
                                    x-transition
                                    @keydown.escape.window="showModal = false"
                                    class="fixed inset-0 z-50 bg-black bg-opacity-40 flex items-center justify-center"
                                >
                                    <div
                                        @click.prevent.outside="showModal = false"
                                        class="bg-white max-w-md w-full max-h-[80vh] overflow-y-auto p-4 rounded shadow relative"
                                    >
                                        <button
                                            @click.prevent="showModal = false"
                                            class="absolute top-2 right-2 text-gray-600 hover:text-black text-xl font-bold"
                                        >
                                            &times;
                                        </button>
                                        <h2 class="text-lg font-semibold mb-3">All Files</h2>
                                        <div class="grid grid-cols-3 gap-2">
                                            @foreach ($files as $index => $file)
                                                @php
                                                    $imgType = str_starts_with($file->getMimeType(), 'image/');
                                                @endphp
                                                <div class="relative w-20 h-20">
                                                    @if ($imgType)
                                                        <img src="{{ $file->temporaryUrl() }}" alt="file"
                                                            class="w-full h-full rounded-lg object-cover border border-gray-300 shadow-md" />
                                                    @else
                                                        <div class="text-xs text-gray-700 truncate border rounded p-2 bg-white shadow w-full h-full flex items-center justify-center">
                                                            📄 {{ $file->getClientOriginalName() }}
                                                        </div>
                                                    @endif

                                                    <!-- X button -->
                                                    <button
                                                        wire:click.prevent="removeFile({{ $index }})"
                                                        class="absolute top-0 right-0 bg-white rounded-full text-xs text-red-600 hover:text-white hover:bg-red-500 w-5 h-5 flex items-center justify-center shadow -mt-1 -mr-1"
                                                        title="Remove"
                                                    >
                                                        &times;
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>

                                    </div>
                                </div>
                            </div>

                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="module">
    let typingTimeout = null;
    let chatContainer = document.getElementById('chat-container');
    const messageInput = document.getElementById('message-input');
    const typingIndicator = document.getElementById('typing-indicator');

    // Listen to broadcasted Laravel events
    window.Echo.private(`chat-channel.{{ $senderId }}.{{ $receiverId }}`)
        .listen('MessageSentEvent', (event) => {
        console.log(event);
            const audio = new Audio('{{ asset('storage/sounds/notification-sound.mp3') }}');
            audio.play();
        });

        window.Echo.private(`reaction-channel.{{$receiverId}}.{{$senderId}}`)
        .listen('MessageReaction', (event) => {
        console.log(event);
            const audio = new Audio('{{ asset('storage/sounds/notification-sound.mp3') }}');
            audio.play();
        });

    // Listen to whispers from the other client
    window.Echo.private(`chat-channel.{{ $senderId }}.{{ $receiverId }}`)
        .listenForWhisper('typing', (event) => {
            if (typingIndicator) {
                typingIndicator.classList.remove('hidden');
                clearTimeout(typingTimeout);
                typingTimeout = setTimeout(() => {
                    typingIndicator.classList.add('hidden');
                }, 2000);
            }
        });

    // Emit whisper on input
    if (messageInput) {
        messageInput.addEventListener('input', () => {
            window.Echo.private(`chat-channel.{{ $receiverId }}.{{ $senderId }}`)
                .whisper('typing', {
                    senderId: {{ $senderId }},
                    receiverId: {{ $receiverId }}
                });
        });
    }

    // Auto-scroll logic
    Livewire.on('messages-updated', () => {
        setTimeout(() => {
            scrollToLatestMessage()
        }, 50);
    });

    window.onload = () => {
        scrollToLatestMessage();
    }

    function scrollToLatestMessage() {
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }

            // Scroll to Message
   // Wrap in DOMContentLoaded and make it more specific
document.addEventListener('DOMContentLoaded', function() {
    Livewire.on('scroll-to-message', (event) => {
        // Add delay to ensure Livewire finishes rendering
        setTimeout(() => {
            const messageElement = document.getElementById(`message-${event.index}`);
            if (messageElement) {
                console.log('Scrolling to message:', event.index);
                messageElement.scrollIntoView({
                    behavior: 'auto', // Change to 'auto' first for testing
                    block: 'center'
                });
            }
        }, 50); // 50ms delay
    });
});

         Livewire.on('focus-message-input', () => {
                document.getElementById('message-input').focus();
            });

    document.addEventListener('alpine:init', () => {
        Alpine.store('dropdown', {
            openId: null
        })
    })
</script>
