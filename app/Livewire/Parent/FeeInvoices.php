<?php

namespace App\Livewire\Parent;

use App\Models\FeeInvoice;
use App\Models\Term;
use Livewire\Component;

class FeeInvoices extends Component
{
    public string $filterChild  = '';
    public string $filterStatus = '';
    public string $filterTerm   = '';

    public function mount(): void
    {
        // Pre-select child from dashboard quick-link ?child=X
        $this->filterChild = request('child', '');
    }

    public function render()
    {
        $parentProfile = auth()->user()->parentProfile;

        if (! $parentProfile) {
            return view('livewire.parent.fee-invoices', [
                'invoices'   => collect(),
                'children'   => collect(),
                'terms'      => collect(),
                'totals'     => ['outstanding' => 0, 'paid' => 0, 'total' => 0],
            ])->layout('layouts.parent', ['title' => 'Fee Invoices']);
        }

        // All student IDs belonging to this parent
        $studentIds = $parentProfile->students()->pluck('students.id');

        $terms    = Term::with('session')->orderByDesc('id')->get();
        $children = $parentProfile->students()->orderBy('first_name')->get();

        $query = FeeInvoice::with('student', 'term.session', 'items', 'payments')
            ->whereIn('student_id', $studentIds)
            ->when($this->filterChild,  fn($q) => $q->where('student_id', $this->filterChild))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterTerm,   fn($q) => $q->where('term_id', $this->filterTerm))
            ->orderByDesc('created_at');

        $invoices = $query->get();

        // Summary totals scoped to current filters
        $totals = [
            'total'       => $invoices->sum('total_amount'),
            'paid'        => $invoices->sum('amount_paid'),
            'outstanding' => $invoices->sum('balance'),
        ];

        return view('livewire.parent.fee-invoices', compact('invoices', 'children', 'terms', 'totals'))
            ->layout('layouts.parent', ['title' => 'Fee Invoices']);
    }
}
