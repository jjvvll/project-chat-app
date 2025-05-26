                                    @if (!$message->deleted_at)
                                        @if ($message->message)
                                            <div
                                                class="{{ $isSender ? 'bg-indigo-600 text-white rounded-3xl rounded-tr-none' : 'bg-gray-100 text-gray-900 rounded-3xl rounded-tl-none' }} px-4 py-2 w-fit break-words">
                                                {!! $search ? $this->highlightText($message->message, $search) : e($message->message) !!}
                                            </div>

                                        @endif

                                        @if ($message->file_name)
                                            @if ($isImage)
                                                <div x-data="{ open: false }">
                                                    <img
                                                        @click="open = true"
                                                        src="{{ asset('storage/'.$message->folder_path) }}"
                                                        alt="Image"
                                                        class="w-30 h-20 rounded-lg object-cover border cursor-pointer hover:opacity-90 mt-2"
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
