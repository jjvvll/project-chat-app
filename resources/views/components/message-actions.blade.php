<div
    x-data="{
        id: {{ $message->id }},
        x: 0,
        y: 0,
        get showActions() {
            return $store.dropdown.openId === this.id
        },
        toggleActions(event) {
            $store.dropdown.openId = this.id;

            if (event) {
                this.x = event.clientX;
                this.y = event.clientY;
            }
        },
        closeMenu() {
            if (this.showActions) {
                $store.dropdown.openId = null;
            }
        }
    }"
    @contextmenu.prevent="toggleActions($event)"
    @click.away="closeMenu()"
@wheel.window="closeMenu()"
    @click.window="closeMenu()"
    class="grid gap-1 relative group items-center"
    id="message-actions-menu"
>
    <!-- Trigger -->
    <div class="cursor-pointer" >
        {{ $slot }} <!-- This will contain your message content -->
    </div>

    <!-- Actions Menu -->
    @if (is_null($message->deleted_at))
       <div
        x-show="showActions"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-data="{ isSender: {{ json_encode($isSender) }} }"
        class="fixed z-50 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1"
        :style="`top: ${y}px; left: ${x}px;`"
    >
        <button
            wire:click="startReply({{ $message->id }},  @js($message->message))"
            @click="showActions = false"
            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
        >
            Reply
        </button>
        <button
            @click="showActions = false"
            wire:click="openForwardModal({{ $message->id }})"
            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
        >
            Forward
        </button>

        @if ($isSender && (bool)$message->is_forwarded === false)
            <button
                @click="showActions = false"
                wire:click="editMessage({{ $message->id }}, @js($message->message))"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
            >
                Edit
            </button>
        @endif

        @if($isSender)
            <button
                @click="showActions = false"
                wire:click="deleteMessage({{ $message->id }})"
                wire:confirm="Are you sure you want to delete this message?"
                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
            >
                Delete
            </button>
        @endif
    </div>
    @endif
</div>

