<?php

namespace App\Livewire\Parent;

use App\Models\Result;
use App\Models\Term;
use Livewire\Component;

class Results extends Component
{
    public string $filterChild = '';
    public string $filterTerm  = '';

    public function render()
    {
        $parentProfile = auth()->user()->parentProfile;
        $children      = $parentProfile
            ? $parentProfile->students()->where('status', 'active')->orderBy('first_name')->get()
            : collect();

        $terms   = Term::with('session')->orderByDesc('id')->get();
        $results = collect();

        if ($parentProfile && ($this->filterChild || $children->count() === 1)) {
            $studentId = $this->filterChild ?: $children->first()?->id;

            if ($studentId) {
                $results = Result::with('subject', 'term.session')
                    ->where('student_id', $studentId)
                    ->where('is_published', true)
                    ->when($this->filterTerm, fn($q) => $q->where('term_id', $this->filterTerm))
                    ->orderBy('term_id', 'desc')
                    ->get()
                    ->groupBy('term_id');
            }
        }

        return view('livewire.parent.results', compact('children', 'terms', 'results'))
            ->layout('layouts.parent', ['title' => 'Results']);
    }
}
