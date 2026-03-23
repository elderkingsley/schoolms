<?php

namespace App\Livewire\Parent;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.parent.dashboard')
            ->layout('layouts.parent');
    }
}
