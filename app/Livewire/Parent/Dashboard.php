<?php

namespace App\Livewire\Parent;

use App\Models\FeeInvoice;
use App\Models\MessageRecipient;
use App\Models\Term;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user          = auth()->user();
        $parentProfile = $user->parentProfile;
        $activeTerm    = Term::current();

        $children = collect();
        $unread   = 0;

        if ($parentProfile) {
            $children = $parentProfile->students()
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
                ->get();

            $unread = MessageRecipient::where('parent_id', $parentProfile->id)
                ->whereNull('read_at')
                ->count();
        }

        return view('livewire.parent.dashboard', compact('children', 'activeTerm', 'unread'))
            ->layout('layouts.parent', ['title' => 'Home']);
    }
}
