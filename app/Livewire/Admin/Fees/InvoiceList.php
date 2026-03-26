<?php

namespace App\Livewire\Admin\Fees;

use App\Models\FeeInvoice;
use App\Models\Term;
use App\Services\FeeService;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceList extends Component
{
    use WithPagination;

    public ?int  $selectedTermId  = null;
    public string $search         = '';
    public string $filterStatus   = ''; // '', 'unpaid', 'partial', 'paid'

    // Controls the "Are you sure?" confirmation modal before generating
    public bool $showConfirmModal = false;

    // Feedback shown after generation runs
    public ?string $generationMessage = null;

    public function mount(): void
    {
        $activeTerm = Term::current();
        $this->selectedTermId = $activeTerm?->id;
    }

    // ─── Lifecycle ────────────────────────────────────────────────────────────

    // Reset pagination whenever the user changes filters — otherwise page 2
    // of a previous filter bleeds into the new one
    public function updatedSelectedTermId(): void
    {
        $this->resetPage();
        $this->generationMessage = null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    // ─── Generate invoices ────────────────────────────────────────────────────

    public function confirmGenerate(): void
    {
        $this->showConfirmModal = true;
    }

    public function cancelGenerate(): void
    {
        $this->showConfirmModal = false;
    }

    public function generateInvoices(FeeService $feeService): void
    {
        $this->showConfirmModal = false;

        if (! $this->selectedTermId) {
            return;
        }

        $term  = Term::findOrFail($this->selectedTermId);
        $count = $feeService->generateInvoicesForTerm($term);

        $this->generationMessage = $count > 0
            ? "✓ {$count} new invoice(s) generated and parents notified by email."
            : "All active students already have invoices for this term. No new invoices were created.";

        $this->resetPage();
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $terms = Term::with('session')
            ->orderByDesc('academic_session_id')
            ->orderBy('id')
            ->get();

        $invoicesQuery = FeeInvoice::with('student', 'term.session')
            ->when($this->selectedTermId, fn($q) => $q->where('term_id', $this->selectedTermId))
            ->when($this->filterStatus,   fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->search, function ($q) {
                $q->whereHas('student', function ($sq) {
                    $sq->where('first_name', 'like', "%{$this->search}%")
                       ->orWhere('last_name',  'like', "%{$this->search}%")
                       ->orWhere('admission_number', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('created_at', 'desc');

        // Summary counts for the stat bar — scoped to selected term only
        $summaryQuery = FeeInvoice::when(
            $this->selectedTermId,
            fn($q) => $q->where('term_id', $this->selectedTermId)
        );

        $stats = [
            'total'   => (clone $summaryQuery)->count(),
            'unpaid'  => (clone $summaryQuery)->where('status', 'unpaid')->count(),
            'partial' => (clone $summaryQuery)->where('status', 'partial')->count(),
            'paid'    => (clone $summaryQuery)->where('status', 'paid')->count(),
            'revenue' => (clone $summaryQuery)->sum('amount_paid'),
            'outstanding' => (clone $summaryQuery)->sum('balance'),
        ];

        $invoices = $invoicesQuery->paginate(25);

        return view('livewire.admin.fees.invoice-list', compact('terms', 'invoices', 'stats'))
            ->layout('layouts.admin', ['title' => 'Fee Invoices']);
    }
}
