<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MessageActions extends Component
{
    public $message;
    public $isSender;

    public $showForwardModal;
    public $users;
     public function __construct($message, $isSender, $showForwardModal,$users)
    {
        $this->message = $message;
        $this->isSender = $isSender;
        $this->showForwardModal = $showForwardModal;
        $this->users = $users;
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.message-actions');
    }
}
