<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class messageForwardLabel extends Component
{

    public $message;
    public $folderPaths = [];
     public $isSender;
     public function __construct($message,$folderPaths,$isSender)
    {
        $this->message = $message;
        $this->folderPaths = $folderPaths;
        $this->isSender =  $isSender;
    }

    public function render(): View|Closure|string
    {
        return view('components.message-forward-label');
    }
}
