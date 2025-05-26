<div class="relative">
    {{$slot}}
    <div class="absolute top-1/2 transform -translate-y-1/2  text-xs text-gray-500 {{ $isSender ? '-left-8' : '-right-8' }}"
                                            x-data="{ showReactions: false }">

                                            @if (!$isSender)
                                                <button
                                                @click="showReactions = true"
                                                class="flex items-center justify-center w-6 h-6"
                                                 id="button-{{ $message->id }}"
                                            >
                                                @if (!empty($message->reaction))
                                                    <span class="text-lg bg-gray-100 rounded-full px-2.5 py-1.5 inline-flex items-center justify-center min-w-[2.5rem]">
                                                        {{ $message->reaction }}
                                                    </span>
                                                @else
                                                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 opacity-0 group-hover:opacity-100 transition-opacity  hover:text-indigo-600" >
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                                    </svg>
                                                @endif

                                                </button>
                                            @else
                                                <div class="flex items-center justify-center w-6 h-6">
                                                     @if (!empty($message->reaction))
                                                    <span class="text-lg bg-gray-100 rounded-full px-2.5 py-1.5 inline-flex items-center justify-center min-w-[2.5rem]">
                                                        {{ $message->reaction }}
                                                    </span>
                                                  @endif
                                                </div>
                                            @endif


                                            <!-- Reaction Picker Popup -->
                                            <div
                                                x-show="showReactions"
                                                @click.away="showReactions = false"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="opacity-100 scale-100"
                                                x-transition:leave-end="opacity-0 scale-95"

                                                x-data="{ isSender: {{ json_encode($isSender) }} }"
                                                x-bind:class="isSender ? 'right-0 origin-bottom-right' : 'left-0 origin-bottom-left'"
                                                class="absolute bottom-full mb-2 bg-white shadow-xl rounded-full p-2.5 flex gap-2 z-50 border border-gray-200 transform scale-110">
                                                <!-- Reaction options -->
                                                <button @click="$wire.addReaction('❤️', {{ $message->id }}); showReactions = false"
                                                        class="hover:scale-150 transition-transform text-2xl w-10 h-10 flex items-center justify-center">
                                                    ❤️
                                                </button>
                                                <button @click="$wire.addReaction('😂', {{ $message->id }}); showReactions = false"
                                                        class="hover:scale-150 transition-transform text-2xl w-10 h-10 flex items-center justify-center">
                                                    😂
                                                </button>
                                                <button @click="$wire.addReaction('😮', {{ $message->id }}); showReactions = false"
                                                        class="hover:scale-150 transition-transform text-2xl w-10 h-10 flex items-center justify-center">
                                                    😮
                                                </button>
                                                <button @click="$wire.addReaction('😢', {{ $message->id }}); showReactions = false"
                                                        class="hover:scale-150 transition-transform text-2xl w-10 h-10 flex items-center justify-center">
                                                    😢
                                                </button>
                                                <button @click="$wire.addReaction('👍', {{ $message->id }}); showReactions = false"
                                                        class="hover:scale-150 transition-transform text-2xl w-10 h-10 flex items-center justify-center">
                                                    👍
                                                </button>
                                                <button @click="$wire.addReaction('👎', {{ $message->id }}); showReactions = false"
                                                        class="hover:scale-150 transition-transform text-2xl w-10 h-10 flex items-center justify-center">
                                                    👎
                                                </button>
                                            </div>
                                        </div>

</div>
