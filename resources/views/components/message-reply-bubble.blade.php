
<div class="mb-2">
    {{-- Reply Preview (if replying to another message) --}}
            @if ($message->parent && ($message->parent->message || $message->parent->file_name) && !$message->parent->trashed())
                <div class="mr-auto  bg-gray-100 px-3 py-2 rounded-md text-sm text-gray-700 mb-1 max-w-xs curso-pointer"  wire:click="goToParentMessage({{$message ->id}})">
                    <div class="border-l-2 border-gray-500 ml-1 mr-1 pl-1 pr-1">
                         <div class="font-semibold text-gray-600">
                            {{ $message->parent->sender->id === auth()->id() ? 'You' : $message->parent->sender->name ?? 'Unknown' }}
                        </div>



                    @php
                        $isImage = str_starts_with($message->parent->file_type, 'image/');
                    @endphp
                                        @if ($message->parent->message )
                                             <div class="truncate">
                                                {{ $message->parent->message }}
                                            </div>
                                        @endif

                                        @if ($message->parent->file_name)
                                            @if ($isImage)
                                                   <img
                                                            src="{{ asset('storage/' . $message->parent->folder_path) }}"
                                                            alt="Image"
                                                            class="w-30 h-20 rounded-lg object-cover border mt-2"
                                                        >
                                            @else
                                                📎 {{ $message->parent->file_original_name }}
                                            @endif
                                        @endif

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

