<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicSession;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Term;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user          = auth()->user();
        $activeTerm    = Term::current();
        $activeSession = AcademicSession::current();

        // Classes where this teacher is form teacher
        $myClasses = SchoolClass::with([
            'enrolments' => fn($q) => $q->where('status', 'active')
                ->when($activeSession, fn($q) => $q->where('academic_session_id', $activeSession->id)),
            'subjects' => fn($q) => $q->when($activeSession,
                fn($q) => $q->wherePivot('academic_session_id', $activeSession->id)
            ),
        ])
        ->where('form_teacher_id', $user->id)
        ->ordered()
        ->get();

        // Count pending submissions (results entered but not yet published) per class
        $submittedCounts = [];
        $draftCounts     = [];
        if ($activeTerm) {
            foreach ($myClasses as $class) {
                $studentIds = $class->enrolments->pluck('student_id');
                $submittedCounts[$class->id] = Result::where('term_id', $activeTerm->id)
                    ->whereIn('student_id', $studentIds)
                    ->whereNotNull('submitted_at')
                    ->where('is_published', false)
                    ->count();
                $draftCounts[$class->id] = Result::where('term_id', $activeTerm->id)
                    ->whereIn('student_id', $studentIds)
                    ->whereNull('submitted_at')
                    ->count();
            }
        }

        return view('livewire.teacher.dashboard',
            compact('myClasses', 'activeTerm', 'activeSession', 'submittedCounts', 'draftCounts'))
            ->layout('layouts.teacher', ['title' => 'Dashboard']);
    }
}
