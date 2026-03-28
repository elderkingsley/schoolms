<?php

namespace App\Livewire\Admin\Fees;

use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeeItem;
use App\Services\FeeService;
use Livewire\Component;

class InvoiceDetail extends Component
{
    public FeeInvoice $invoice;

    // Record payment form
    public string $payAmount    = '';
    public string $payMethod    = 'Cash';
    public string $payReference = '';
    public bool   $showPayForm  = false;

    // Add optional item form
    public ?int   $addItemId     = null;
    public string $addItemAmount = '';
    public bool   $showAddItem   = false;

    public function mount(FeeInvoice $invoice): void
    {
        $this->invoice = $invoice->load([
            'student.parents.user',
            'student.enrolments.schoolClass',
            'term.session',
            'items.feeItem',
            'payments',
        ]);
    }

    // ── Record payment ────────────────────────────────────────────────────────

    public function openPayForm(): void
    {
        $this->payAmount    = number_format($this->invoice->balance, 0, '.', '');
        $this->payMethod    = 'Cash';
        $this->payReference = '';
        $this->showPayForm  = true;
    }

    public function recordPayment(FeeService $feeService): void
    {
        $this->validate([
            'payAmount'    => 'required|numeric|min:1|max:' . $this->invoice->balance,
            'payMethod'    => 'required|in:Cash,Bank Transfer,POS',
            'payReference' => 'nullable|string|max:100',
        ]);

        $feeService->recordPayment(
            $this->invoice,
            (float) str_replace(',', '', $this->payAmount),
            $this->payMethod,
            $this->payReference
        );

        // Refresh the invoice and its relations so the UI updates instantly
        $this->invoice->refresh();
        $this->invoice->load('items.feeItem', 'payments');

        $this->showPayForm = false;
        session()->flash('success', 'Payment of ₦' . number_format((float) str_replace(',', '', $this->payAmount), 0) . ' recorded successfully.');
    }

    // ── Add optional fee item ─────────────────────────────────────────────────

    public function openAddItem(): void
    {
        $this->addItemId     = null;
        $this->addItemAmount = '';
        $this->showAddItem   = true;
    }

    public function addOptionalItem(): void
    {
        $this->validate([
            'addItemId'     => 'required|exists:fee_items,id',
            'addItemAmount' => 'required|numeric|min:1',
        ]);

        FeeInvoiceItem::create([
            'fee_invoice_id' => $this->invoice->id,
            'fee_item_id'    => $this->addItemId,
            'item_name'      => FeeItem::find($this->addItemId)->name,
            'amount'         => (float) str_replace(',', '', $this->addItemAmount),
            'added_by'       => 'admin',
        ]);

        // Recalculate invoice total from line items
        $this->invoice->recalculateTotal();
        $this->invoice->refresh();
        $this->invoice->load('items.feeItem', 'payments');

        $this->showAddItem = false;
        session()->flash('success', 'Optional item added and invoice total updated.');
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        // Optional fee items not already on this invoice
        $existingItemIds = $this->invoice->items->pluck('fee_item_id')->filter()->all();

        $optionalItems = FeeItem::active()
            ->optional()
            ->orderBy('sort_order')
            ->get();

        return view('livewire.admin.fees.invoice-detail', compact('optionalItems'))
            ->layout('layouts.admin', ['title' => 'Invoice — ' . $this->invoice->student->full_name]);
    }
}
