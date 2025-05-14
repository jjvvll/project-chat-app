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
                    <h1 class="text-2xl font-bold">
                        List of users
                    </h1>

                    <div class ="overflow-x">
                        <table class="min-w-full border border-gray-200">
                            <thead class="bg-gray-100">
                              <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">#</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Email</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
                              </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">

                                @foreach ($users as $user)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-800">{{$user->id}}</td>
                                    <td class="px-4 py-2 text-sm text-gray-800">{{$user->name}}</td>
                                    <td class="px-4 py-2 text-sm text-gray-800">{{$user->email}}</td>
                                    <td class="px-4 py-2 text-sm text-gray-800">
                                       <a navigate href="{{route('chat', $user->id)}}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            class="w-4 h-4 inline-block text-blue-500">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707
                                                3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12
                                                21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172
                                                0 0 0 3.423-.379c1.584-.233 2.707-1.626
                                                2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394
                                                48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373
                                                3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                        </svg>
                                        <span id="unread-count-{{$user->id}}"
                                            class="{{$user->unread_messages_count ?'bg-red-600 text-white px-2 py-1 rounded-full text-xs' : ''}}">
                                            {{$user->unread_messages_count ? $user->unread_messages_count : ''}}
                                        </span>
                                       </a>
                                    </td>
                                </tr>
                                @endforeach

                              <!-- Add more rows as needed -->
                            </tbody>
                          </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script type="module">
    window.Echo.private('unread-channel.{{Auth::user()->id}}')
    .listen('UnreadMessage', (event) => {
        console.log(event);

    const unreadElementCount = document.getElementById(`unread-count-${event.senderId}`);

        if(unreadElementCount) {
            unreadElementCount.textContent = event.unreadMessageCount > 0 ? event.unreadMessageCount : '';

            // Toggle classes conditionally
            if (event.unreadMessageCount > 0) {

                unreadElementCount.classList.add('bg-red-600', 'text-white', 'px-2', 'py-1', 'rounded-full', 'text-xs');
            } else {
                unreadElementCount.classList.remove('bg-red-600', 'text-white', 'px-2', 'py-1', 'rounded-full', 'text-xs');
            }
        }
});
</script>
