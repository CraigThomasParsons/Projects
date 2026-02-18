<?php

namespace App\Livewire;

use Livewire\Component;

class Preferences extends Component
{
    public function render()
    {
        return view('livewire.preferences')->layout('layouts.app');
    }
}
