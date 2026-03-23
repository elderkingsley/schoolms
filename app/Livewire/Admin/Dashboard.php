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

        // Student counts
        $totalStudents  = Student::count();
        $activeStudents = $activeSession
            ? Enrolment::where('academic_session_id', $activeSession->id)
                       ->where('status', 'active')->count()
            : 0;

        // Parent count
        $totalParents = ParentGuardian::count();

        // Fee figures — current term only
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
