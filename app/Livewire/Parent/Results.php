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

        $isRemarkOnly = false;

        if ($parentProfile && ($this->filterChild || $children->isNotEmpty())) {
            // Default to first child if none selected — avoids showing a blank
            // "please select a child" screen when the parent has multiple children.
            $studentId = $this->filterChild ?: $children->first()?->id;

            if ($studentId) {
                $results = Result::with('subject', 'term.session')
                    ->where('student_id', $studentId)
                    ->where('is_published', true)
                    ->when($this->filterTerm, fn($q) => $q->where('term_id', $this->filterTerm))
                    ->orderBy('term_id', 'desc')
                    ->get()
                    ->groupBy('term_id');

                // Detect remark-only class (nursery/preschool) so blade renders
                // the correct layout — scored table vs remark-only table.
                $currentTerm = Term::current();
                $enrolment = \App\Models\Enrolment::with('schoolClass')
                    ->where('student_id', $studentId)
                    ->where('academic_session_id', $currentTerm?->academic_session_id)
                    ->where('status', 'active')
                    ->first();
                $isRemarkOnly = $enrolment?->schoolClass?->isRemarkOnly() ?? false;
            }
        }

        return view('livewire.parent.results', compact('children', 'terms', 'results', 'isRemarkOnly'))
            ->layout('layouts.parent', ['title' => 'Results']);
    }
}
