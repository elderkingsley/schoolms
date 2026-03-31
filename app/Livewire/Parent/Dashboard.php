<?php

namespace App\Livewire\Parent;

use App\Models\MessageRecipient;
use App\Models\Term;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user       = auth()->user();
        $activeTerm = Term::current();

        // A parent may have enrolled more than one child. Each child's enrolment
        // creates a separate row in the `parents` table linked to the same user_id.
        // User->parentProfile() is a hasOne — it only returns the FIRST parent record.
        // We must load ALL parent records for this user to get all children.
        $parentProfiles = \App\Models\ParentGuardian::where('user_id', $user->id)->get();

        $children = collect();
        $unread   = 0;

        if ($parentProfiles->isNotEmpty()) {
            // Collect all student IDs across every parent record for this user
            $studentIds = $parentProfiles->flatMap(fn($p) =>
                $p->students()->pluck('students.id')
            )->unique();

            // Load students with their enrolments and invoices in one query
            $children = \App\Models\Student::whereIn('id', $studentIds)
                ->where('status', 'active')
                ->with([
                    'enrolments' => fn($q) => $q->where('status', 'active')
                        ->with('schoolClass')
                        ->when($activeTerm, fn($q) =>
                            $q->where('academic_session_id', $activeTerm->academic_session_id)
                        ),
                    'feeInvoices' => fn($q) => $q->when($activeTerm,
                        fn($q) => $q->where('term_id', $activeTerm->id)
                    ),
                ])
                ->orderBy('first_name')
                ->get();

            // Unread messages across all parent profile records for this user
            $unread = MessageRecipient::whereIn('parent_id', $parentProfiles->pluck('id'))
                ->whereNull('read_at')
                ->count();
        }

        return view('livewire.parent.dashboard', compact('children', 'activeTerm', 'unread'))
            ->layout('layouts.parent', ['title' => 'Home']);
    }
}
