<div>
    <!-- Modal -->
    @if($showForwardModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center ">
            <div class="bg-white rounded-lg p-6 w-full max-w-md space-y-4 shadow-lg">
                <h2 class="text-lg font-semibold">Forward Message</h2>

                @if($users->isEmpty())
                    <p class="text-gray-500">No available users to forward to.</p>
                @else
                    <ul class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($users as $user)
                            <li class="flex items-center justify-between border-b pb-2">
                                <span>{{ $user->name }}</span>

                                <button wire:click="forwardMessage({{ $user->id }})"
                                        class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
                                    Send
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <button wire:click="$set('showForwardModal', false)"
                        class="mt-4 w-full py-2 text-center bg-gray-200 rounded hover:bg-gray-300 text-sm">
                    Cancel
                </button>
            </div>
        </div>
    @endif
</div>

