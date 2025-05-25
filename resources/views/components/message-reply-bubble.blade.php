<div class="mb-2">
    {{-- Reply Preview (if replying to another message) --}}
    @if ($message->parent && $message->parent->message)
        <div class="border-l-4 border-blue-500 bg-gray-100 px-3 py-2 rounded-md text-sm text-gray-700 mb-1 max-w-xs">
            <div class="font-semibold text-gray-600">
                {{ $message->parent->sender->id === auth()->id() ? 'You' : $message->parent->sender->name ?? 'Unknown' }}
            </div>
            <div class="truncate">
                {{ $message->parent->message }}
            </div>
        </div>
    @endif

     {{ $slot }}
</div>
