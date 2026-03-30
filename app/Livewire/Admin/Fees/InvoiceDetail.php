<?php

namespace App\Livewire\Admin\Fees;

use App\Jobs\SendInvoiceJob;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeeItem;
use App\Models\FeeStructure;
use App\Services\FeeService;
use Livewire\Component;

class InvoiceDetail extends Component
{
    public FeeInvoice $invoice;

    // ── Record payment ────────────────────────────────────────────────────────
    public string $payAmount    = '';
    public string $payMethod    = 'Cash';
    public string $payReference = '';
    public bool   $showPayForm  = false;

    // ── Add item ──────────────────────────────────────────────────────────────
    // Two modes: 'catalogue' picks from fee_items; 'custom' is free-text
    public string $addMode       = 'catalogue';
    public ?int   $addItemId     = null;
    public string $addItemAmount = '';
    public string $addCustomName = '';  // free-text name for custom items
    public bool   $showAddItem   = false;

    // ── Edit item amount ──────────────────────────────────────────────────────
    public ?int   $editingItemId     = null;
    public string $editingItemAmount = '';
    public bool   $showEditItem      = false;

    // ── Delete invoice ────────────────────────────────────────────────────────
    public bool $showDeleteConfirm = false;

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

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function reload(): void
    {
        $this->invoice->refresh();
        $this->invoice->load('items.feeItem', 'payments');
    }

    /**
     * An invoice can be edited (items added/removed) if no payments have been
     * made against it. Once any payment is recorded the line items are locked
     * because removing them would distort the payment history.
     */
    public function canEdit(): bool
    {
        return $this->invoice->payments->isEmpty();
    }

    /**
     * An invoice can be deleted if it is unpaid and has no payments at all.
     * Paid or part-paid invoices are financial records and must not be deleted.
     */
    public function canDelete(): bool
    {
        return $this->invoice->status === 'unpaid'
            && $this->invoice->payments->isEmpty();
    }

    // ── Payment link ──────────────────────────────────────────────────────────

    /**
     * Manually retry payment link creation — shown when the automatic job failed.
     * Clears the error, re-dispatches CreatePaymentLinkJob.
     */
    public function retryPaymentLink(): void
    {
        $this->invoice->update([
            'payment_link_error' => null,
            'payment_link_url'   => null,
            'payment_link_id'    => null,
        ]);

        \App\Jobs\CreatePaymentLinkJob::dispatch($this->invoice);

        session()->flash('success', 'Payment link creation queued. Refresh in a few seconds.');
    }

    // ── Send invoice ──────────────────────────────────────────────────────────

    public function sendInvoice(): void
    {
        SendInvoiceJob::dispatch($this->invoice);
        $this->reload();
        session()->flash('success', 'Invoice queued for delivery. Parents will receive it by email shortly.');
    }

    // ── Record payment ────────────────────────────────────────────────────────

    public function openPayForm(): void
    {
        $this->payAmount    = number_format($this->invoice->balance, 0, '.', '');
        $this->payMethod    = 'Cash';
        $this->payReference = '';
        $this->showPayForm  = true;
        $this->resetValidation();
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

        $this->reload();
        $this->showPayForm = false;
        session()->flash('success', 'Payment of ₦' . number_format((float) str_replace(',', '', $this->payAmount), 0) . ' recorded.');
    }

    // ── Add item ──────────────────────────────────────────────────────────────

    public function openAddItem(): void
    {
        abort_if(! $this->canEdit(), 403, 'Cannot edit an invoice with recorded payments.');

        $this->addMode       = 'catalogue';
        $this->addItemId     = null;
        $this->addItemAmount = '';
        $this->addCustomName = '';
        $this->showAddItem   = true;
        $this->resetValidation();
    }

    /**
     * When admin selects a catalogue item, auto-fill the amount from
     * the fee structure for this invoice's class+term, if configured.
     */
    public function updatedAddItemId(?int $value): void
    {
        if (! $value) {
            $this->addItemAmount = '';
            return;
        }

        // Look up the fee structure amount for this class + term
        $enrolment = $this->invoice->student->enrolments
            ->where('academic_session_id', $this->invoice->term->academic_session_id)
            ->first();

        $structure = FeeStructure::where('fee_item_id', $value)
            ->where('term_id', $this->invoice->term_id)
            ->where(function ($q) use ($enrolment) {
                $q->where('school_class_id', $enrolment?->school_class_id)
                  ->orWhereNull('school_class_id');
            })
            ->orderByRaw('school_class_id IS NULL ASC') // prefer class-specific over school-wide
            ->first();

        $this->addItemAmount = $structure
            ? (string) (int) $structure->amount
            : '';
    }

    public function addItem(): void
    {
        abort_if(! $this->canEdit(), 403);

        if ($this->addMode === 'catalogue') {
            $this->validate([
                'addItemId'     => 'required|exists:fee_items,id',
                'addItemAmount' => 'required|numeric|min:1',
            ]);

            // Prevent duplicate catalogue items on the same invoice
            $alreadyExists = $this->invoice->items
                ->where('fee_item_id', $this->addItemId)
                ->isNotEmpty();

            if ($alreadyExists) {
                $this->addError('addItemId', 'This fee item is already on the invoice. Edit its amount instead.');
                return;
            }

            $feeItem = FeeItem::findOrFail($this->addItemId);

            FeeInvoiceItem::create([
                'fee_invoice_id' => $this->invoice->id,
                'fee_item_id'    => $this->addItemId,
                'item_name'      => $feeItem->name,
                'amount'         => (float) $this->addItemAmount,
                'added_by'       => 'admin',
            ]);

        } else {
            // Custom free-text item
            $this->validate([
                'addCustomName'  => 'required|string|min:2|max:150',
                'addItemAmount'  => 'required|numeric|min:1',
            ]);

            FeeInvoiceItem::create([
                'fee_invoice_id' => $this->invoice->id,
                'fee_item_id'    => null,  // no catalogue link
                'item_name'      => $this->addCustomName,
                'amount'         => (float) $this->addItemAmount,
                'added_by'       => 'admin',
            ]);
        }

        $this->invoice->recalculateTotal();
        $this->reload();
        $this->showAddItem = false;
        session()->flash('success', 'Item added. Invoice total updated.');
    }

    // ── Edit item amount ──────────────────────────────────────────────────────

    public function openEditItem(int $itemId): void
    {
        abort_if(! $this->canEdit(), 403);

        $item = FeeInvoiceItem::findOrFail($itemId);
        abort_if($item->fee_invoice_id !== $this->invoice->id, 403);

        $this->editingItemId     = $itemId;
        $this->editingItemAmount = (string) (int) $item->amount;
        $this->showEditItem      = true;
        $this->resetValidation();
    }

    public function saveItemAmount(): void
    {
        abort_if(! $this->canEdit(), 403);

        $this->validate([
            'editingItemAmount' => 'required|numeric|min:1',
        ]);

        $item = FeeInvoiceItem::findOrFail($this->editingItemId);
        abort_if($item->fee_invoice_id !== $this->invoice->id, 403);

        $item->update(['amount' => (float) $this->editingItemAmount]);

        $this->invoice->recalculateTotal();
        $this->reload();
        $this->showEditItem = false;
        session()->flash('success', 'Item amount updated.');
    }

    // ── Remove item ───────────────────────────────────────────────────────────

    public function removeItem(int $itemId): void
    {
        abort_if(! $this->canEdit(), 403);

        $item = FeeInvoiceItem::findOrFail($itemId);
        abort_if($item->fee_invoice_id !== $this->invoice->id, 403);

        // Guard: must always have at least one line item
        if ($this->invoice->items->count() <= 1) {
            session()->flash('error', 'Cannot remove the last item. An invoice must have at least one fee item. Delete the invoice instead.');
            return;
        }

        $item->delete();

        $this->invoice->recalculateTotal();
        $this->reload();
        session()->flash('success', 'Item removed. Invoice total updated.');
    }

    // ── Delete invoice ────────────────────────────────────────────────────────

    public function confirmDelete(): void
    {
        abort_if(! $this->canDelete(), 403, 'This invoice cannot be deleted.');
        $this->showDeleteConfirm = true;
    }

    public function deleteInvoice(): void
    {
        abort_if(! $this->canDelete(), 403);

        $studentName = $this->invoice->student->full_name;

        // Cascade deletes items via DB constraint (cascadeOnDelete on fee_invoice_items)
        $this->invoice->delete();

        session()->flash('success', "Invoice for {$studentName} deleted.");
        $this->redirect(route('admin.fees.invoices'));
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        // All active fee items not already on this invoice (for catalogue add)
        $existingItemIds = $this->invoice->items
            ->pluck('fee_item_id')
            ->filter()
            ->all();

        $availableItems = FeeItem::active()
            ->whereNotIn('id', $existingItemIds)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.fees.invoice-detail', compact('availableItems'))
            ->layout('layouts.admin', ['title' => 'Invoice — ' . $this->invoice->student->full_name]);
    }
}
