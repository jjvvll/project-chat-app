<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class messageForward extends Component
{

    public $showForwardModal;
    public $users;
     public function __construct($showForwardModal,$users)
    {
        $this->showForwardModal = $showForwardModal;
        $this->users = $users;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.message-forward');
    }
}
