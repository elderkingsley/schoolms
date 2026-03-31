<?php

namespace App\Livewire\Parent;

use App\Models\FeeInvoice;
use App\Models\ParentGuardian;
use Livewire\Component;

class InvoiceView extends Component
{
    public FeeInvoice $invoice;

    public function mount(FeeInvoice $invoice): void
    {
        $user = auth()->user();

        // Load ALL parent records for this user — covers parents with multiple children
        $parentProfiles = ParentGuardian::where('user_id', $user->id)->get();

        if ($parentProfiles->isEmpty()) abort(403);

        $studentIds = $parentProfiles
            ->flatMap(fn($p) => $p->students()->pluck('students.id'))
            ->unique();

        if (! $studentIds->contains($invoice->student_id)) abort(403);

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
        // Pass the parent's own record so the blade can show their NUBAN
        $userId = auth()->id();

        $parentProfile = $this->invoice->student->parents
            ->filter(fn($p) => $p->user_id === $userId)
            ->first();

        return view('livewire.parent.invoice-view', compact('parentProfile'))
            ->layout('layouts.parent', ['title' => 'Invoice']);
    }
}
