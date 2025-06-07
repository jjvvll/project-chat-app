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

    public $editingMessageId = null;    // ?int = nullable integer
    public $editedContent = null;    // ?string = nullable string
    public $selectedIndices;

     public function __construct($message, $isSender, $search,$editingMessageId, $editedContent, $selectedIndices)
    {
        $this->message = $message;
        $this->isSender = $isSender;
        $this->search = $search;

        $this->editingMessageId = $editingMessageId;
        $this->editedContent = $editedContent;

        $this->selectedIndices =$selectedIndices;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.message-bubble');
    }
}
