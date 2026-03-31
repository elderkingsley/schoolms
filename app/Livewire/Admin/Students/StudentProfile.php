<?php

namespace App\Livewire\Admin\Students;

use App\Models\Enrolment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Services\FeeService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class StudentProfile extends Component
{
    use WithFileUploads;

    public Student $student;
    public bool $editing = false;

    // Editable fields
    public string $firstName       = '';
    public string $lastName        = '';
    public string $otherName       = '';
    public string $gender          = '';
    public string $dateOfBirth     = '';
    public string $status          = '';
    public string $notes           = '';
    public string $medicalNotes    = '';
    public string $classAppliedFor = '';
    public $newPhoto               = null; // uploaded file (Livewire temp)

    // ── Invoice creation modal ────────────────────────────────────────────────
    public bool    $showInvoiceModal = false;
    public ?int    $invoiceTermId    = null;
    public mixed   $invoicePreview   = null;

    // ── Class change modal ────────────────────────────────────────────────────
    public bool    $showClassModal   = false;
    public ?int    $changeEnrolmentId = null;  // which enrolment to edit
    public ?int    $newClassId        = null;  // the chosen class

    public function mount(Student $student): void
    {
        $this->student = $student->load([
            'parents.user',
            'enrolments.schoolClass',
            'enrolments.session',
            'results.subject',
            'results.term.session',
            'feeInvoices.term.session',
            'feeInvoices.items',
            'feeInvoices.payments',
        ]);
    }

    // ── Edit mode ─────────────────────────────────────────────────────────────

    public function startEdit(): void
    {
        $s = $this->student;
        $this->firstName       = $s->first_name;
        $this->lastName        = $s->last_name;
        $this->otherName       = $s->other_name ?? '';
        $this->gender          = $s->gender;
        $this->dateOfBirth     = $s->date_of_birth?->format('Y-m-d') ?? '';
        $this->status          = $s->status;
        $this->notes           = $s->notes ?? '';
        $this->medicalNotes    = $s->medical_notes ?? '';
        $this->classAppliedFor = $s->class_applied_for ?? '';
        $this->editing         = true;
    }

    public function cancelEdit(): void
    {
        $this->editing = false;
        $this->resetValidation();
    }

    // ── Invoice creation ──────────────────────────────────────────────────────

    public function openInvoiceModal(): void
    {
        $this->invoiceTermId  = Term::current()?->id;
        $this->invoicePreview = null;
        $this->showInvoiceModal = true;
        $this->previewInvoice();
    }

    public function updatedInvoiceTermId(): void
    {
        $this->previewInvoice();
    }

    protected function previewInvoice(): void
    {
        if (! $this->invoiceTermId) {
            $this->invoicePreview = null;
            return;
        }

        $term = Term::find($this->invoiceTermId);
        if (! $term) { $this->invoicePreview = null; return; }

        $this->invoicePreview = app(FeeService::class)
            ->previewInvoice($this->student, $term);
    }

    public function createInvoice(): void
    {
        $this->validate(['invoiceTermId' => 'required|exists:terms,id']);

        $term    = Term::findOrFail($this->invoiceTermId);
        $invoice = app(FeeService::class)
            ->generateInvoiceForStudent($this->student, $term);

        $this->showInvoiceModal = false;
        $this->invoicePreview   = null;

        if ($invoice) {
            $this->student->load('feeInvoices.term.session', 'feeInvoices.items', 'feeInvoices.payments');
            session()->flash('success', "Invoice created for {$this->student->full_name} — {$term->name} Term. Review it in the Fees section below.");
        } else {
            session()->flash('error', 'An invoice already exists for this student and term.');
        }
    }

    // ── Class change ──────────────────────────────────────────────────────────

    /**
     * Open the change-class modal for a specific enrolment.
     * Pre-selects the enrolment's current class.
     */
    public function openClassModal(int $enrolmentId): void
    {
        $enrolment = Enrolment::findOrFail($enrolmentId);

        // Security: enrolment must belong to this student
        abort_if($enrolment->student_id !== $this->student->id, 403);

        $this->changeEnrolmentId = $enrolmentId;
        $this->newClassId        = $enrolment->school_class_id;
        $this->showClassModal    = true;
        $this->resetValidation();
    }

    public function saveClassChange(): void
    {
        $this->validate([
            'newClassId' => 'required|exists:school_classes,id',
        ]);

        $enrolment = Enrolment::findOrFail($this->changeEnrolmentId);
        abort_if($enrolment->student_id !== $this->student->id, 403);

        $oldClass = $enrolment->schoolClass?->display_name ?? '—';
        $newClass = SchoolClass::findOrFail($this->newClassId);

        // Prevent assigning a class the student is already in
        if ($enrolment->school_class_id === $this->newClassId) {
            session()->flash('error', "Student is already in {$newClass->display_name}.");
            $this->showClassModal = false;
            return;
        }

        $enrolment->update(['school_class_id' => $this->newClassId]);

        // Reload enrolments so the profile page reflects the change immediately
        $this->student->load(
            'enrolments.schoolClass',
            'enrolments.session'
        );

        $this->showClassModal    = false;
        $this->changeEnrolmentId = null;
        $this->newClassId        = null;

        session()->flash('success', "{$this->student->full_name} moved from {$oldClass} to {$newClass->display_name}.");
    }

    public function saveEdit(): void
    {
        $data = $this->validate([
            'firstName'       => 'required|string|min:1|max:100',
            'lastName'        => 'required|string|min:1|max:100',
            'otherName'       => 'nullable|string|max:100',
            'gender'          => 'required|in:Male,Female',
            'dateOfBirth'     => 'nullable|date|before:today',
            'status'          => 'required|in:pending,active,withdrawn',
            'notes'           => 'nullable|string|max:1000',
            'medicalNotes'    => 'nullable|string|max:1000',
            'classAppliedFor' => 'nullable|string|max:100',
            'newPhoto'        => 'nullable|image|max:2048|mimes:jpeg,png,webp',
        ]);

        $updates = [
            'first_name'        => $data['firstName'],
            'last_name'         => $data['lastName'],
            'other_name'        => $data['otherName'] ?: null,
            'gender'            => $data['gender'],
            'date_of_birth'     => $data['dateOfBirth'] ?: null,
            'status'            => $data['status'],
            'notes'             => $data['notes'] ?: null,
            'medical_notes'     => $data['medicalNotes'] ?: null,
            'class_applied_for' => $data['classAppliedFor'] ?: null,
        ];

        // Handle photo upload — store new, delete old
        if ($this->newPhoto) {
            // Delete the previous photo if one existed
            if ($this->student->photo && Storage::disk('public')->exists($this->student->photo)) {
                Storage::disk('public')->delete($this->student->photo);
            }

            $updates['photo'] = $this->newPhoto->store('student-photos', 'public');
            $this->newPhoto   = null;
        }

        $this->student->update($updates);
        $this->student->refresh();
        $this->editing = false;

        session()->flash('success', 'Student record updated successfully.');
    }

    /**
     * Remove the student's photo entirely (revert to initials avatar).
     */
    public function removePhoto(): void
    {
        if ($this->student->photo && Storage::disk('public')->exists($this->student->photo)) {
            Storage::disk('public')->delete($this->student->photo);
        }

        $this->student->update(['photo' => null]);
        $this->student->refresh();
        session()->flash('success', 'Photo removed.');
    }

    public function render()
    {
        $invoices = $this->student->feeInvoices->sortByDesc(fn($i) => $i->term_id);
        $activeTerm     = \App\Models\Term::current();
        $currentInvoice = $activeTerm
            ? $invoices->firstWhere('term_id', $activeTerm->id)
            : $invoices->first();

        $feeSummary = [
            'total_billed'      => $invoices->sum('total_amount'),
            'total_paid'        => $invoices->sum('amount_paid'),
            'total_outstanding' => $invoices->sum('balance'),
        ];

        // All terms for the invoice creation modal
        $terms = \App\Models\Term::with('session')
            ->orderByDesc('academic_session_id')
            ->orderBy('id')
            ->get();

        // All classes for the class-change modal
        $classes = SchoolClass::ordered()->get();

        return view('livewire.admin.students.student-profile',
            compact('invoices', 'currentInvoice', 'feeSummary', 'activeTerm', 'terms', 'classes'))
            ->layout('layouts.admin', [
                'title' => $this->student->first_name . ' ' . $this->student->last_name,
            ]);
    }
}
