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

     public function __construct($message, $isSender, $search, $isImage)
    {
        $this->message = $message;
        $this->isSender = $isSender;
        $this->search = $search;
        $this->isImage = $isImage;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.message-bubble');
    }
}
