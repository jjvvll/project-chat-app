<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MessageActions extends Component
{
    public $message;
    public $isSender;

     public function __construct($message, $isSender)
    {
        $this->message = $message;
        $this->isSender = $isSender;
    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.message-actions');
    }
}
