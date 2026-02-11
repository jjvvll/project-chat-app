<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="container mx-auto p-8">
                    <h1 class="text-2xl font-bold mb-6 dark:text-white">
                        Messages
                    </h1>

                    <div class="space-y-2">
                        @foreach ($users as $user)
                            <a href="{{ route('chat', $user->id) }}"
                                class="flex items-center gap-3 p-4 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors border-b border-gray-100 dark:border-gray-700 last:border-b-0">

                                <!-- Profile Picture -->
                                <div class="relative flex-shrink-0">
                                    @if ($user->profile_photo)
                                        <img src="{{ asset('storage/' . $user->profile_photo) }}"
                                            alt="{{ $user->name }}"
                                            class="w-12 h-12 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600">
                                    @else
                                        <div
                                            class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-lg">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <!-- Unread Badge (on avatar) -->

                                    <span id="unread-count-{{ $user->id }}"
                                        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center border-2 border-white dark:border-gray-800">
                                        {{ $user->unread_messages_count > 9 ? '9+' : $user->unread_messages_count }}
                                    </span>


                                </div>

                                <!-- User Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $user->name }}
                                        </h3>
                                        <!-- You can add timestamp here if available -->
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                        {{ $user->email }}
                                    </p>
                                </div>

                                <!-- Message Icon -->
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                    </svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script type="module">
    window.Echo.private('unread-channel.{{ Auth::user()->id }}')
        .listen('UnreadMessage', (event) => {
            console.log(event);

            const unreadElementCount = document.getElementById(`unread-count-${event.senderId}`);

            if (unreadElementCount) {
                unreadElementCount.textContent = event.unreadMessageCount > 0 ? event.unreadMessageCount : '';

                // Toggle classes conditionally
                if (event.unreadMessageCount > 0) {

                    unreadElementCount.classList.add('bg-red-600', 'text-white', 'px-2', 'py-1', 'rounded-full',
                        'text-xs');
                } else {
                    unreadElementCount.classList.remove('bg-red-600', 'text-white', 'px-2', 'py-1', 'rounded-full',
                        'text-xs');
                }

                //play notificaiton audio
                if (event.unreadMessageCount > 0) {
                    const audio = new Audio('{{ asset('storage/sounds/notification-sound.mp3') }}');
                    audio.play();
                }
            }
        });
</script>
