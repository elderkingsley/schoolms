<?php

namespace App\Livewire\Parent;

use Livewire\Component;

class ChildrenList extends Component
{
    public function render()
    {
        $parentProfile = auth()->user()->parentProfile;

        $children = $parentProfile
            ? $parentProfile->students()
                ->with([
                    'enrolments.schoolClass',
                    'enrolments.session',
                ])
                ->orderBy('first_name')
                ->get()
            : collect();

        return view('livewire.parent.children-list', compact('children'))
            ->layout('layouts.parent', ['title' => 'My Children']);
    }
}
