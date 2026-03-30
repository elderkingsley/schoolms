<?php

namespace App\Livewire\Accountant;

use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\Term;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceList extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterStatus = '';
    public string $filterTerm   = '';

    // Payment recording modal
    public ?int   $payingInvoiceId = null;
    public string $payAmount       = '';
    public string $payMethod       = 'cash';
    public string $payNote         = '';
    public bool   $showPayModal    = false;

    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterTerm(): void   { $this->resetPage(); }

    public function openPayment(int $invoiceId): void
    {
        $invoice = FeeInvoice::findOrFail($invoiceId);
        $this->payingInvoiceId = $invoiceId;
        $this->payAmount       = $invoice->balance > 0 ? (string) $invoice->balance : '';
        $this->payMethod       = 'cash';
        $this->payNote         = '';
        $this->showPayModal    = true;
    }

    public function recordPayment(): void
    {
        $this->validate([
            'payAmount' => 'required|numeric|min:1',
            'payMethod' => 'required|in:cash,transfer,cheque,pos',
            'payNote'   => 'nullable|string|max:300',
        ]);

        $invoice = FeeInvoice::findOrFail($this->payingInvoiceId);

        $amount = min((float) $this->payAmount, $invoice->balance);

        FeePayment::create([
            'fee_invoice_id' => $invoice->id,
            'amount'         => $amount,
            'method'         => $this->payMethod,
            'note'           => $this->payNote ?: null,
            'paid_at'        => now(),
            'recorded_by'    => auth()->id(),
        ]);

        // Update invoice totals
        $totalPaid = $invoice->payments()->sum('amount');
        $status    = $totalPaid >= $invoice->total_amount
            ? 'paid'
            : ($totalPaid > 0 ? 'partial' : 'unpaid');

        $invoice->update([
            'amount_paid' => $totalPaid,
            'balance'     => max(0, $invoice->total_amount - $totalPaid),
            'status'      => $status,
        ]);

        $this->showPayModal = false;
        session()->flash('success', 'Payment of ₦' . number_format($amount, 0) . ' recorded successfully.');
    }

    public function render()
    {
        $terms = Term::with('session')->orderByDesc('id')->get();

        $invoices = FeeInvoice::with('student', 'term.session')
            ->when($this->search, fn($q) =>
                $q->whereHas('student', fn($s) =>
                    $s->where('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%")
                      ->orWhere('admission_number', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterTerm,   fn($q) => $q->where('term_id', $this->filterTerm))
            ->orderByDesc('created_at')
            ->paginate(25);

        $payingInvoice = $this->payingInvoiceId
            ? FeeInvoice::with('student')->find($this->payingInvoiceId)
            : null;

        return view('livewire.accountant.invoice-list',
            compact('invoices', 'terms', 'payingInvoice'))
            ->layout('layouts.accountant', ['title' => 'Invoices']);
    }
}
