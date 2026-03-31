<?php

namespace App\Livewire\Parent;

use App\Models\FeeInvoice;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\Term;
use Livewire\Component;

class FeeInvoices extends Component
{
    public string $filterChild  = '';
    public string $filterStatus = '';
    public string $filterTerm   = '';

    public function mount(): void
    {
        $this->filterChild = request('child', '');
    }

    public function render()
    {
        $user = auth()->user();

        // All parent records for this user — covers parents with multiple children
        $parentProfiles = ParentGuardian::where('user_id', $user->id)->get();

        if ($parentProfiles->isEmpty()) {
            return view('livewire.parent.fee-invoices', [
                'invoices'  => collect(),
                'children'  => collect(),
                'terms'     => collect(),
                'totals'    => ['outstanding' => 0, 'paid' => 0, 'total' => 0],
            ])->layout('layouts.parent', ['title' => 'Fee Invoices']);
        }

        $studentIds = $parentProfiles
            ->flatMap(fn($p) => $p->students()->pluck('students.id'))
            ->unique();

        $children = Student::whereIn('id', $studentIds)
            ->orderBy('first_name')
            ->get();

        $terms = Term::with('session')->orderByDesc('id')->get();

        $invoices = FeeInvoice::with('student', 'term.session', 'items', 'payments')
            ->whereIn('student_id', $studentIds)
            ->when($this->filterChild,  fn($q) => $q->where('student_id', $this->filterChild))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterTerm,   fn($q) => $q->where('term_id', $this->filterTerm))
            ->orderByDesc('created_at')
            ->get();

        $totals = [
            'total'       => $invoices->sum('total_amount'),
            'paid'        => $invoices->sum('amount_paid'),
            'outstanding' => $invoices->sum('balance'),
        ];

        return view('livewire.parent.fee-invoices', compact('invoices', 'children', 'terms', 'totals'))
            ->layout('layouts.parent', ['title' => 'Fee Invoices']);
    }
}
