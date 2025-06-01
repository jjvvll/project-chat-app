<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MessageBubble extends Component
{
    /**
     * Create a new component instance.
     */
     public $message;
    public $isSender;
    public $search;
    public $isImage;
    public $editingMessageId = null;    // ?int = nullable integer
    public $editedContent = null;    // ?string = nullable string

     public function __construct($message, $isSender, $search, $isImage,$editingMessageId, $editedContent)
    {
        $this->message = $message;
        $this->isSender = $isSender;
        $this->search = $search;
        $this->isImage = $isImage;
        $this->editingMessageId = $editingMessageId;
        $this->editedContent = $editedContent;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.message-bubble');
    }
}
