<?php

namespace App\Livewire\Admin;

use App\Models\AcademicSession;
use App\Models\Enrolment;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\Term;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $activeSession = AcademicSession::current();
        $activeTerm    = Term::current();

        // Only count students that are actually in the school
        // Exclude 'withdrawn' (rejected enrolments)
        $totalStudents = Student::whereIn('status', ['active', 'pending'])->count();

        $activeStudents = $activeSession
            ? Enrolment::where('academic_session_id', $activeSession->id)
                       ->where('status', 'active')->count()
            : 0;

        // Only count parents who have a real user account (approved enrolments)
        $totalParents = ParentGuardian::whereNotNull('user_id')->count();

        $feesCollected   = 0;
        $feesOutstanding = 0;

        if ($activeTerm) {
            $feesCollected = FeePayment::whereHas('invoice', fn($q) =>
                $q->where('term_id', $activeTerm->id)
            )->sum('amount');

            $feesOutstanding = FeeInvoice::where('term_id', $activeTerm->id)
                ->where('status', '!=', 'paid')
                ->sum('balance');
        }

        return view('livewire.admin.dashboard', compact(
            'activeSession',
            'activeTerm',
            'totalStudents',
            'activeStudents',
            'totalParents',
            'feesCollected',
            'feesOutstanding',
        ))->layout('layouts.admin', ['title' => 'Dashboard']);
    }
}
