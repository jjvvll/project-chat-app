                            @if (!$message->deleted_at)
                                        @if (($message->message || $message->file_name) && $message->is_forwarded)
                                            <div class="text-xs italic text-black-500 mb-1">
                                                @if ($isSender)
                                                    <div class="text-right text-xs text-gray-500 mb-1">You forwarded a message</div>
                                                @else
                                                        <div class="text-left text-xs text-gray-500 mb-1">Forwarded</div>
                                                @endif
                                            </div>
                                        @endif

                                @if ($message->message && !$message->is_forwarded)
                                        {{-- <p>{{ var_dump($editingMessageId) }} | {{ var_dump($message->id) }}</p> --}}
                                            @if ((int)$editingMessageId === $message->id)
                                                <p class="text-xs text-red-500"> Editing Message ID: {{ $editingMessageId }}</p>


                                                  <textarea
                                                        x-data
                                                        x-ref="ta"
                                                        x-init="
                                                            $nextTick(() => {
                                                                $refs.ta.style.height = 'auto';
                                                                $refs.ta.style.height = $refs.ta.scrollHeight + 'px';
                                                            });
                                                            $watch('editedContent', () => {
                                                                $refs.ta.style.height = 'auto';
                                                                $refs.ta.style.height = $refs.ta.scrollHeight + 'px';
                                                            });
                                                        "
                                                        x-on:input="
                                                            $refs.ta.style.height = 'auto';
                                                            $refs.ta.style.height = $refs.ta.scrollHeight + 'px';
                                                        "
                                                        x-on:keydown="
                                                            // List of keys that *do not* change content and we want to ignore
                                                            const ignoredKeys = [
                                                                'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                                                                'Control', 'Shift', 'Alt', 'Meta', 'CapsLock',
                                                                'Tab', 'Escape', 'Enter' // Enter handled separately below
                                                            ];
                                                            if (!ignoredKeys.includes($event.key)) {
                                                                window.savedSelectionStart = $refs.ta.selectionStart;
                                                                window.savedSelectionEnd = $refs.ta.selectionEnd;
                                                                window.savedScrollTop = $refs.ta.scrollTop;
                                                            }
                                                        "
                                                        x-on:keydown.enter.prevent="
                                                            if ($event.shiftKey) {
                                                                const ta = $refs.ta;
                                                                const start = ta.selectionStart;
                                                                const end = ta.selectionEnd;

                                                                ta.value = ta.value.substring(0, start) + '\n' + ta.value.substring(end);
                                                                ta.selectionStart = ta.selectionEnd = start + 1;

                                                                editedContent = ta.value;

                                                                  // Resize textarea immediately after inserting newline
                                                                ta.style.height = 'auto';
                                                                ta.style.height = ta.scrollHeight + 'px';

                                                                const scrollPos = ta.scrollTop;
                                                                $nextTick(() => {
                                                                    ta.scrollTop = scrollPos;
                                                                });
                                                            } else {
                                                                $wire.saveEditedMessage();
                                                            }
                                                        "

                                                        x-on:input.debounce.500ms="
                                                            $nextTick(() => {
                                                                if(window.savedSelectionStart !== undefined){
                                                                    $refs.ta.selectionStart = window.savedSelectionStart;
                                                                    $refs.ta.selectionEnd = window.savedSelectionEnd;
                                                                    $refs.ta.scrollTop = window.savedScrollTop;
                                                                    window.savedSelectionStart = undefined;
                                                                    window.savedSelectionEnd = undefined;
                                                                    window.savedScrollTop = undefined;
                                                                }
                                                            });
                                                        "
                                                        wire:model="editedContent"
                                                        x-on:input="$refs.ta.style.height = 'auto'; $refs.ta.style.height = $refs.ta.scrollHeight + 'px'"
                                                        class="bg-indigo-600 text-white w-full p-2 border rounded-3xl resize-none overflow-hidden px-4 py-2 "
                                                        rows="1"
                                                    ></textarea>

                                            @else
                                                <div class="{{$isSender ? 'bg-indigo-600 text-white rounded-3xl rounded-tr-none' :
                                                                    'bg-gray-200  text-gray-900 rounded-3xl rounded-tl-none'}}
                                                                  w-full px-4 py-2  [overflow-wrap:anywhere] text-ellipsis">
                                                {!! $search ? $this->highlightText($message->message, $search) : nl2br(e($message->message)) !!}

                                            </div>
                                            @endif
                                @elseif ($message->message)


                                            <!-- Left indicator line -->
                                            {{-- <div class="{{$isSender ? 'right-0': 'left-0'}} absolute top-0 bottom-0 w-1 bg-blue-500 rounded-full"></div> --}}

                                            <!-- Message bubble (keep your existing rounded styles) -->
                                            <div class="{{$isSender ? 'bg-indigo-600 rounded-tr-none text-white ' : 'bg-gray-200  rounded-tl-none' }} rounded-lg  p-3 max-w-md ">
                                                <div class="border-l-2 {{$isSender ? 'border-white ml-1 mr-1 pl-1 pr-1' : 'border-gray-500 ml-1 mr-1 pl-1 pr-1' }} ">
                                                        <div class="[overflow-wrap:anywhere] ">
                                                            {!! $search ? $this->highlightText($message->message, $search) : e($message->message) !!}
                                                        </div>
                                                </div>

                                            </div>


                                @endif


                                    @php
                                        $folderPaths = json_decode($message->folder_path, true);
                                        $originalNames = json_decode($message->file_original_name, true);
                                        $thumbnails = json_decode($message->thumbnail_path, true);
                                    @endphp

                                <div class="{{ ( is_array($folderPaths) && count($folderPaths) > 1 )? 'flex flex-wrap gap-1 mt-2 p-4 rounded ' .
                                            ($isSender ? 'bg-indigo-600 text-white rounded-3xl rounded-tr-none' :
                                                                    'bg-gray-200  text-gray-900 rounded-3xl rounded-tl-none') : '' }}"
>
                                        @if (is_array($folderPaths) && is_array($originalNames))

                                            @foreach( $folderPaths as $index => $folderPath)

                                            @php
                                                strpos(mime_content_type('storage/'.$folderPath), 'image/') === 0 ? $isImage = true : $isImage = false
                                            @endphp

                                                @php
                                                    $originalName = $originalNames[$index] ?? 'Unknown';
                                                     $thumbnail = $thumbnails[$index] ?? 'Unknown';
                                                @endphp

                                                @if ((int)$editingMessageId === $message->id)
                                                   <div class="relative">
                                                        @if (in_array($index, $selectedIndices))
                                                                     <div
                                                                    class="w-24 h-24 object-cover rounded-lg border cursor-pointer hover:opacity-90 mt-2" ></div>
                                                        @else
                                                                @if ($isImage)
                                                                    <img src="{{ asset($thumbnail) }}"
                                                                    alt="file"
                                                                    class="w-24 h-24 object-cover rounded-lg border cursor-pointer hover:opacity-90 mt-2" />
                                                                @else
                                                                        @php
                                                                            $fullPath = storage_path('app/public/' . $folderPath);
                                                                            $mimeType = file_exists($fullPath) ? mime_content_type($fullPath) : '';
                                                                        @endphp

                                                                        <div class="w-24 h-24 object-cover border cursor-pointer hover:opacity-90 mt-2 bg-gray-300 {{ $isSender ? 'text-white' : 'text-black' }}">
                                                                                @if($mimeType === 'application/pdf')
                                                                                    <img src="{{ asset($thumbnail) }}"
                                                                                        alt="File preview" class="w-full h-full object-cover rounded" />
                                                                                @elseif(in_array($mimeType, ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword']))
                                                                                    <div class="w-full h-full flex items-center justify-center bg-white text-black rounded text-xl font-bold">
                                                                                        📄 DOC File
                                                                                    </div>
                                                                                @else
                                                                                    <div class="w-full h-full flex items-center justify-center bg-white text-black rounded text-xl font-bold">
                                                                                        📎 Unknown File
                                                                                    </div>
                                                                                @endif

                                                                                <span class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs text-center p-1 truncate"
                                                                                    title="{{ $originalName }}">
                                                                                    {{ $originalName }}
                                                                                </span>
                                                                    </div>
                                                                @endif
                                                        @endif
                                                        <button type="button"
                                                                        wire:click="toggleSelectedIndex({{$index}})"
                                                                        class="absolute top-0 right-0 text-red-500 bg-white rounded-full w-5 h-5 flex items-center justify-center shadow -translate-y-1/2 translate-x-1/2">
                                                                    <span class="text-xs font-bold">x</span>
                                                                </button>
                                                   </div>
                                                @else
                                                    @if ($isImage)
                                                    <div x-data="{ open: false }">
                                                        <img
                                                            @click="open = true"
                                                            src="{{ asset($thumbnail) }}"
                                                            alt="Image"
                                                            class="{{( is_array($folderPaths) && count($folderPaths) > 1 ) ? 'w-24 h-24' : 'w-30 h-30'}} object-cover rounded-lg border cursor-pointer hover:opacity-90 mt-2 {{$isSender ?  'ml-auto' : 'mr-auto' }}"
                                                        >
                                                        <div
                                                            x-show="open"
                                                            @click.away="open = false"
                                                            x-cloak
                                                            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90"
                                                        >
                                                            <div class="relative max-w-4xl max-h-[90vh]">
                                                                <img
                                                                    src="{{ asset('storage/'.$folderPath) }}"
                                                                    alt="Full view"
                                                                    class="max-w-full max-h-[80vh] object-contain"
                                                                >
                                                                <button
                                                                    @click="open = false"
                                                                    class="absolute top-4 right-4 text-black text-2xl hover:text-gray-300"
                                                                >
                                                                    ✕
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @else
                                                        @php
                                                            $fullPath = storage_path('app/public/' . $folderPath);
                                                            $mimeType = file_exists($fullPath) ? mime_content_type($fullPath) : '';
                                                        @endphp

                                                        <div class="mt-2">
                                                            <a href="{{ asset('storage/' . $folderPath) }}"
                                                            download
                                                            class="flex flex-col items-start gap-2 px-3 py-2 rounded text-sm bg-gray-300 relative w-24 h-24 overflow-hidden border {{ $isSender ? 'text-white' : 'text-black' }}">



                                                                    @if($mimeType === 'application/pdf')
                                                                        <img src="{{ asset($thumbnail) }}"
                                                                            alt="File preview" class="w-full h-full object-cover rounded" />
                                                                    @elseif(in_array($mimeType, ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword']))
                                                                        <div class="w-full h-full flex items-center justify-center bg-white text-black rounded text-xl font-bold">
                                                                            📄 DOC File
                                                                        </div>
                                                                    @else
                                                                        <div class="w-full h-full flex items-center justify-center bg-white text-black rounded text-xl font-bold">
                                                                            📎 Unknown File
                                                                        </div>
                                                                    @endif

                                                                    <span class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs text-center p-1 truncate"
                                                                        title="{{ $originalName }}">
                                                                        {{ $originalName }}
                                                                    </span>


                                                            </a>
                                                        </div>


                                                    @endif
                                                @endif

                                            @endforeach
                                        @endif

                                </div>
                                @if ((int)$editingMessageId === $message->id)
                                             <div class="flex gap-2 mt-2">
                                                        <button wire:click="saveEditedMessage" >
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                            </svg>
                                                        </button>
                                                        <button wire:click="cancelEdit" >
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                        @endif

                            @else
                                            <div
                                                    class="bg-transparent border border-gray-300 text-gray-600 rounded-3xl px-4 py-2 break-words">
                                                    <em class="text-sm text-gray-400">This message has been deleted</em>
                                                </div>
                            @endif
