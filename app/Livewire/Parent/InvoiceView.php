<?php

namespace App\Livewire\Parent;

use App\Models\FeeInvoice;
use Livewire\Component;

class InvoiceView extends Component
{
    public FeeInvoice $invoice;

    public function mount(FeeInvoice $invoice): void
    {
        $parentProfile = auth()->user()->parentProfile;

        // Security: ensure this invoice belongs to one of this parent's children
        if (! $parentProfile) {
            abort(403);
        }

        $studentIds = $parentProfile->students()->pluck('students.id');

        if (! $studentIds->contains($invoice->student_id)) {
            abort(403);
        }

        $this->invoice = $invoice->load([
            'student.parents.user',
            'student.enrolments.schoolClass',
            'term.session',
            'items.feeItem',
            'payments',
        ]);
    }

    public function render()
    {
        return view('livewire.parent.invoice-view')
            ->layout('layouts.parent', ['title' => 'Invoice']);
    }
}
