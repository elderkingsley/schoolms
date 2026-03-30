<?php

namespace App\Livewire\Admin\Fees;

use App\Jobs\SendInvoiceJob;
use App\Models\FeeInvoice;
use App\Models\Term;
use App\Services\FeeService;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceList extends Component
{
    use WithPagination;

    public ?int   $selectedTermId    = null;
    public string $search            = '';
    public string $filterStatus      = '';
    public string $tab               = 'all';

    // Bulk select
    public array  $selectedIds       = [];
    public bool   $selectAll         = false;

    // Modals
    public bool   $showConfirmModal  = false;
    public bool   $showSendModal     = false;
    public int    $sendBatchSize     = 10;

    public ?string $generationMessage = null;

    public function mount(): void
    {
        $this->selectedTermId = Term::current()?->id;
    }

    public function updatedSelectedTermId(): void
    {
        $this->resetPage(); $this->generationMessage = null;
        $this->selectedIds = []; $this->selectAll = false;
    }
    public function updatedTab(): void
    {
        $this->resetPage(); $this->selectedIds = []; $this->selectAll = false;
    }
    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedIds = $value
            ? $this->buildQuery()->paginate(25)->pluck('id')->map(fn($id) => (string)$id)->toArray()
            : [];
    }

    public function toggleSelect(int $id): void
    {
        $sid = (string)$id;
        if (in_array($sid, $this->selectedIds)) {
            $this->selectedIds = array_values(array_filter($this->selectedIds, fn($i) => $i !== $sid));
        } else {
            $this->selectedIds[] = $sid;
        }
        $this->selectAll = false;
    }

    public function confirmGenerate(): void { $this->showConfirmModal = true; }
    public function cancelGenerate(): void  { $this->showConfirmModal = false; }

    public function generateInvoices(FeeService $feeService): void
    {
        $this->showConfirmModal = false;
        if (! $this->selectedTermId) return;

        $term  = Term::findOrFail($this->selectedTermId);
        $count = $feeService->generateInvoicesForTerm($term);

        $this->generationMessage = $count > 0
            ? "✓ {$count} invoice(s) created as drafts. Review in the Drafts tab, then send."
            : "All active students already have invoices for this term.";

        $this->tab = 'draft';
        $this->resetPage();
    }

    public function sendInvoice(int $invoiceId): void
    {
        $invoice = FeeInvoice::with('student')->findOrFail($invoiceId);
        SendInvoiceJob::dispatch($invoice);
        session()->flash('success', "Invoice for {$invoice->student->full_name} queued for delivery.");
    }

    public function sendSelected(): void
    {
        if (empty($this->selectedIds)) return;
        $count = 0;
        foreach ($this->selectedIds as $id) {
            $invoice = FeeInvoice::find((int)$id);
            if ($invoice && $invoice->isDraft()) {
                SendInvoiceJob::dispatch($invoice);
                $count++;
            }
        }
        $this->selectedIds = []; $this->selectAll = false;
        session()->flash('success', "{$count} invoice(s) queued for delivery.");
    }

    public function openSendModal(): void  { $this->showSendModal = true; }

    public function sendBatch(): void
    {
        $this->validate(['sendBatchSize' => 'required|integer|min:1|max:500']);

        $drafts = FeeInvoice::draft()
            ->when($this->selectedTermId, fn($q) => $q->where('term_id', $this->selectedTermId))
            ->limit($this->sendBatchSize)->get();

        foreach ($drafts as $invoice) { SendInvoiceJob::dispatch($invoice); }

        $this->showSendModal = false;
        session()->flash('success', "{$drafts->count()} invoice(s) queued for delivery.");
    }

    public function sendAllDrafts(): void
    {
        $drafts = FeeInvoice::draft()
            ->when($this->selectedTermId, fn($q) => $q->where('term_id', $this->selectedTermId))
            ->get();

        foreach ($drafts as $invoice) { SendInvoiceJob::dispatch($invoice); }
        session()->flash('success', "{$drafts->count()} invoice(s) queued for delivery to all parents.");
    }

    public function deleteInvoice(int $invoiceId): void
    {
        $invoice = FeeInvoice::findOrFail($invoiceId);

        // Guard: only delete unpaid invoices with no payments
        if ($invoice->status !== 'unpaid' || $invoice->payments()->exists()) {
            session()->flash('error', 'Only unpaid invoices with no recorded payments can be deleted.');
            return;
        }

        $name = $invoice->student->full_name;
        $invoice->delete();
        $this->selectedIds = array_values(array_filter($this->selectedIds, fn($id) => $id !== (string)$invoiceId));
        session()->flash('success', "Invoice for {$name} deleted.");
    }

    protected function buildQuery()
    {
        return FeeInvoice::with('student', 'term.session')
            ->when($this->selectedTermId, fn($q) => $q->where('term_id', $this->selectedTermId))
            ->when($this->filterStatus,   fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->tab === 'draft', fn($q) => $q->draft())
            ->when($this->tab === 'sent',  fn($q) => $q->sent())
            ->when($this->search, fn($q) =>
                $q->whereHas('student', fn($sq) =>
                    $sq->where('first_name', 'like', "%{$this->search}%")
                       ->orWhere('last_name', 'like', "%{$this->search}%")
                       ->orWhere('admission_number', 'like', "%{$this->search}%")
                )
            )
            ->orderByDesc('created_at');
    }

    public function render()
    {
        $terms    = Term::with('session')->orderByDesc('academic_session_id')->orderBy('id')->get();
        $invoices = $this->buildQuery()->paginate(25);

        $base = FeeInvoice::when($this->selectedTermId, fn($q) => $q->where('term_id', $this->selectedTermId));

        $stats = [
            'total'       => (clone $base)->count(),
            'draft'       => (clone $base)->draft()->count(),
            'sent'        => (clone $base)->sent()->count(),
            'paid'        => (clone $base)->where('status','paid')->count(),
            'revenue'     => (clone $base)->sum('amount_paid'),
            'outstanding' => (clone $base)->sum('balance'),
        ];

        return view('livewire.admin.fees.invoice-list', compact('terms', 'invoices', 'stats'))
            ->layout('layouts.admin', ['title' => 'Fee Invoices']);
    }
}
