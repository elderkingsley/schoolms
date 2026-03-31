<?php

namespace App\Livewire\Parent;

use App\Models\ParentGuardian;
use App\Models\Student;
use Livewire\Component;

class ChildrenList extends Component
{
    public function render()
    {
        $user = auth()->user();

        // Load ALL parent records for this user — a parent who enrolled
        // more than one child has one row per child in the parents table.
        $parentProfiles = ParentGuardian::where('user_id', $user->id)->get();

        $children = collect();

        if ($parentProfiles->isNotEmpty()) {
            $studentIds = $parentProfiles
                ->flatMap(fn($p) => $p->students()->pluck('students.id'))
                ->unique();

            $children = Student::whereIn('id', $studentIds)
                ->with([
                    'enrolments.schoolClass',
                    'enrolments.session',
                ])
                ->orderBy('first_name')
                ->get();
        }

        return view('livewire.parent.children-list', compact('children'))
            ->layout('layouts.parent', ['title' => 'My Children']);
    }
}
