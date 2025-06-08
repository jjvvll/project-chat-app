
<div class="mb-2">
    {{-- Reply Preview (if replying to another message) --}}
            @if ($message->parent && ($message->parent->message || $message->parent->file_name) && !$message->parent->trashed())
                <div class="mr-auto  bg-gray-100 px-3 py-2 rounded-md text-sm text-gray-700 mb-1 max-w-xs curso-pointer"  wire:click="goToParentMessage({{$message ->id}})">
                    <div class="border-l-2 border-gray-500 ml-1 mr-1 pl-1 pr-1 ">
                         <div class="font-semibold text-gray-600">
                            {{ $message->parent->sender->id === auth()->id() ? 'You' : $message->parent->sender->name ?? 'Unknown' }}
                        </div>

                                        @if ($message->parent->message )
                                             <div class="truncate ">
                                                {{ $message->parent->message }}
                                            </div>
                                        @endif

                                        @php
                                            $folderPaths = json_decode($message->parent->folder_path, true);
                                            $originalNames = json_decode($message->parent->file_original_name, true);
                                            $thumbnails = json_decode($message->parent->thumbnail_path, true);
                                        @endphp

                                        <div class="flex flex-wrap gap-1 rounded">
                                             @if (is_array($folderPaths) && is_array($originalNames))
                                            @foreach ($folderPaths as $index => $folderPath)

                                                @php
                                                    strpos(mime_content_type('storage/'.$folderPath), 'image/') === 0 ? $isImage = true : $isImage = false
                                                @endphp

                                                    @if ($index < 3)
                                                        @if ($isImage)
                                                            <img
                                                                        src="{{ asset($thumbnails[$index]) }}"
                                                                        alt="Image"
                                                                        class="w-24 h-24 rounded-lg object-cover border mt-2"
                                                                    >
                                                        @else
                                                                @php
                                                                    $fullPath = storage_path('app/public/' . $folderPath);
                                                                    $mimeType = file_exists($fullPath) ? mime_content_type($fullPath) : '';
                                                                @endphp

                                                                <div class="mt-2">
                                                                    <div
                                                                    class="flex flex-col items-start gap-2 px-3 py-2 rounded text-sm bg-gray-300 relative w-24 h-24 overflow-hidden border {{ $isSender ? 'text-white' : 'text-black' }}">
                                                                            @if($mimeType === 'application/pdf')
                                                                                <img src="{{ asset($thumbnails[$index]) }}"
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
                                                                                title="{{ $originalNames[$index] }}">
                                                                                {{ $originalNames[$index] }}
                                                                            </span>


                                                                    </div>
                                                                </div>
                                                        @endif
                                                    @else
                                                       <div class="w-24 h-24 rounded-lg object-cover border mt-2 text-lg relative flex items-center justify-center">
                                                            <div>
                                                                +{{ count($folderPaths) - 3 }}
                                                            </div>
                                                        </div>
                                                        @break;
                                                    @endif

                                            @endforeach
                                        @endif
                                        </div>

                    </div>

                </div>

            @elseif( $message->parent && $message->parent->trashed())
                <div class="border-l-4 border-blue-500 bg-gray-100 px-3 py-2 rounded-md text-sm text-gray-700 mb-1 max-w-xs">
                    <div class="truncate">
                    Message has been removed
                    </div>
                </div>

            @endif

 {{ $slot }}

</div>

