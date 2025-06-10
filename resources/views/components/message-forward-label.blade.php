<div>
                                                @php
                                                    $imageCount = 0;
                                                    $fileCount = 0;

                                                    foreach ( $folderPaths as $index => $folderPath){
                                                        strpos(mime_content_type('storage/'.$folderPath), 'image/') === 0 ? $isImage = true : $isImage = false;

                                                        if($isImage){
                                                            $imageCount++;
                                                        }else{
                                                            $fileCount++;
                                                        }
                                                    }
                                                @endphp

                                            @if($isSender)

                                                <p class="text-sm italic text-gray-500 text-right my-2">
                                                    @if ($imageCount && $fileCount)
                                                        You forwarded
                                                        {{ $imageCount === 1 ? 'an image' : 'images' }} and
                                                        {{ $fileCount === 1 ? 'a file' : 'files' }}.
                                                    @elseif ($imageCount)
                                                        You forwarded {{ $imageCount === 1 ? 'an image' : 'images' }}.
                                                    @elseif ($fileCount)
                                                        You forwarded {{ $fileCount === 1 ? 'a file' : 'files' }}.
                                                    @endif
                                                </p>

                                            @else
                                                 <p class="text-sm italic text-gray-500 text-left my-2">

                                                    @if ($imageCount && $fileCount)
                                                        {{ $message->sender->name }} forwarded
                                                        {{ $imageCount === 1 ? 'an image' : 'images' }} and
                                                        {{ $fileCount === 1 ? 'a file' : 'files' }}.
                                                    @elseif ($imageCount)
                                                        {{ $message->sender->name }} forwarded {{ $imageCount === 1 ? 'an image' : 'images' }}.
                                                    @elseif ($fileCount)
                                                        {{ $message->sender->name }} forwarded {{ $fileCount === 1 ? 'a file' : 'files' }}.
                                                    @endif
                                                </p>
                                            @endif
</div>
