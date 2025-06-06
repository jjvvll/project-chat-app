<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MessageReplyBubble extends Component
{
    /**
     * Create a new component instance.
     */
    public $message;
    public $isSender;
    public $editingMessageId = null;
    public $index;

    public function __construct($message, $isSender, $editingMessageId,$index)
    {
        $this->message = $message;
        $this->isSender = $isSender;
        $this->editingMessageId = $editingMessageId;
        $this->index = $index;
    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.message-reply-bubble');
    }
}
