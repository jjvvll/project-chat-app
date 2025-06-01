                                    @if (!$message->deleted_at)
                                        @if (($message->message || $message->file_name) && $message->is_forwarded)
                                            <div class="text-xs italic text-black-500 mb-1">
                                                @if ($isSender)
                                                    <div class="text-right">You forwarded a message</div>
                                                @else
                                                        <div class="text-left">Forwarded</div>
                                                @endif
                                            </div>
                                        @endif

                                        @if ($message->message && !$message->is_forwarded)
                                        <p>{{ var_dump($editingMessageId) }} | {{ var_dump($message->id) }}</p>
                                            @if ((int)$editingMessageId === $message->id)

                                                    <textarea
                                                        wire:model="editedContent"
                                                        class="w-full p-2 border rounded-lg"
                                                    >{{ $editedContent }}</textarea>
                                                    <div class="flex gap-2 mt-2">
                                                        <button wire:click="saveEditedMessage" class="bg-green-500 text-white px-3 py-1 rounded">
                                                        Save
                                                        </button>
                                                        <button wire:click="cancelEdit" class="bg-gray-500 text-white px-3 py-1 rounded">
                                                        Cancel
                                                        </button>
                                                    </div>
                                            @else
                                                <div class="{{ $isSender ? 'bg-indigo-600 text-white rounded-3xl rounded-tr-none' :
                                                                    'bg-gray-100 text-gray-900 rounded-3xl rounded-tl-none' }} w-full px-4 py-2 break-all
                                                                    whitespace-normal min-w-0 overflow-hidden text-ellipsis">
                                                {!! $search ? $this->highlightText($message->message, $search) : e($message->message) !!}
                                            </div>
                                            <button
                                                        wire:click="editMessage('{{ $message->id }}', '{{ $message->message }}')"
                                                        class="bg-gray-200 px-2 py-1 rounded text-sm mt-1"
                                                    >
                                                        Edit
                                                    </button>
                                            @endif


                                        @elseif ($message->message)


                                               <div class="{{$isSender ? 'bg-indigo-600 text-white rounded-tr-none italic':'italic bg-gray-100 text-gray-500 rounded-tl-none'}} relative rounded-3xl w-full px-4 py-2 break-all whitespace-normal min-w-0 overflow-hidden text-ellipsis">
                                                    <div>
                                                        {!! $search ? $this->highlightText($message->message, $search) : e($message->message) !!}
                                                    </div>
                                                </div>
                                        @endif

                                        @if ($message->file_name)
                                            @if ($isImage)
                                                <div x-data="{ open: false }">
                                                    <img
                                                        @click="open = true"
                                                        src="{{ asset('storage/'.$message->folder_path) }}"
                                                        alt="Image"
                                                        class="w-30 h-20 rounded-lg object-cover border cursor-pointer hover:opacity-90 mt-2 {{$isSender ?  'ml-auto' : 'mr-auto' }}"
                                                    >

                                                    <div
                                                        x-show="open"
                                                        @click.away="open = false"
                                                        x-cloak
                                                        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90"
                                                    >
                                                        <div class="relative max-w-4xl max-h-[90vh]">
                                                            <img
                                                                src="{{ asset('storage/'.$message->folder_path) }}"
                                                                alt="Full view"
                                                                class="max-w-full max-h-[80vh] object-contain"
                                                            >
                                                            <button
                                                                @click="open = false"
                                                                class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300"
                                                            >
                                                                ✕
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                            @else

                                                    <a href="{{ asset('storage/' . $message->folder_path) }}"
                                                        download
                                                        class="flex items-center gap-2 mt-2 px-3 py-2 rounded text-sm break-words
                                                        {{ $isSender ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-black' }}">
                                                        📎 {{ $message->file_original_name }}
                                                    </a>

                                            @endif
                                        @endif
                                    @else
                                           <div
                                                class="bg-transparent border border-gray-300 text-gray-600 rounded-3xl px-4 py-2 break-words">
                                                <em class="text-sm text-gray-400">This message has been deleted</em>
                                            </div>
                                    @endif
