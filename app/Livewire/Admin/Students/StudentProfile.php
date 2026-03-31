<?php

namespace App\Livewire\Admin\Students;

use App\Models\AcademicSession;
use App\Models\Enrolment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use App\Notifications\EnrolmentRejectedNotification;
use App\Notifications\ParentWelcomeNotification;
use App\Services\FeeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProvisionParentWalletJob;
use Illuminate\Support\Str;
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

    // ── Approve modal ─────────────────────────────────────────────────────────
    public bool    $showApproveModal  = false;
    public string  $assignedClass     = '';
    public string  $admissionNumber   = '';

    // ── Reject modal ──────────────────────────────────────────────────────────
    public bool    $showRejectModal   = false;
    public string  $rejectionReason   = '';

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

    /**
     * Provision (or re-provision) a virtual bank account for this student.
     * Works for first-time provisioning and retry after failure.
     * Finds the parent row linked to this student and dispatches the job.
     */
    public function provisionWallet(): void
    {
        $parent = $this->student->parents
            ->filter(fn($p) => $p->user !== null)
            ->first();

        if (! $parent) {
            session()->flash('error', 'No parent portal account found. Approve the enrolment first.');
            return;
        }

        if ($parent->hasVirtualAccount()) {
            session()->flash('info', 'This student already has an active virtual account.');
            return;
        }

        $parent->update(['juicyway_wallet_status' => 'pending']);
        ProvisionParentWalletJob::dispatch($parent);

        // Reload so the blade shows "Provisioning…" immediately
        $this->student->load('parents.user');

        session()->flash('success', 'Provisioning queued for ' . $this->student->full_name . '. Refresh in about a minute to see the account details.');
    }


    // ── Approve enrolment from student profile page ───────────────────────────

    public function openApproveModal(): void
    {
        $this->assignedClass    = '';
        $this->admissionNumber  = $this->generateAdmissionNumber();
        $this->showApproveModal = true;
        $this->resetValidation();
    }

    public function confirmApproval(): void
    {
        $this->validate([
            'assignedClass'   => 'required|exists:school_classes,id',
            'admissionNumber' => 'required|string|unique:students,admission_number',
        ]);

        $student = $this->student;

        DB::transaction(function () use ($student) {
            $student->update([
                'status'           => 'active',
                'admission_number' => $this->admissionNumber,
                'approved_at'      => now(),
                'approved_by'      => auth()->id(),
            ]);

            $session = AcademicSession::current();
            $class   = SchoolClass::findOrFail($this->assignedClass);

            if ($session && $class) {
                Enrolment::firstOrCreate(
                    ['student_id' => $student->id, 'academic_session_id' => $session->id],
                    ['school_class_id' => $class->id, 'enrolled_at' => now(), 'status' => 'active']
                );
            }

            foreach ($student->parents as $parent) {
                if ($parent->user_id) continue;
                $tempEmail = $parent->_temp_email;
                if (! $tempEmail) continue;

                $existingUser = User::where('email', $tempEmail)->first();
                if ($existingUser) {
                    $parent->update(['user_id' => $existingUser->id]);
                    $existingUser->notify(new ParentWelcomeNotification($existingUser, $student, null, $parent));
                } else {
                    $tempPassword = Str::random(10);
                    $user = User::create([
                        'name'      => $parent->_temp_name,
                        'email'     => $tempEmail,
                        'password'  => Hash::make($tempPassword),
                        'user_type' => 'parent',
                        'is_active' => true,
                    ]);
                    $user->assignRole('parent');
                    $parent->update(['user_id' => $user->id]);
                    $user->notify(new ParentWelcomeNotification($user, $student, $tempPassword, $parent));
                }
            }
        });

        $student->refresh();
        $student->load('parents.user', 'enrolments.schoolClass', 'enrolments.session');

        foreach ($student->parents as $parent) {
            if ($parent->user && ! $parent->hasVirtualAccount()) {
                ProvisionParentWalletJob::dispatch($parent)->onQueue('provisioning');
            }
        }

        $this->showApproveModal = false;
        session()->flash('success', 'Student approved, parents notified, and bank account provisioning queued.');
    }

    protected function generateAdmissionNumber(): string
    {
        $year   = now()->format('Y');
        $prefix = "NV/{$year}/";
        $last   = Student::where('admission_number', 'like', $prefix . '%')
            ->where('status', 'active')
            ->orderByRaw('CAST(SUBSTRING_INDEX(admission_number, "/", -1) AS UNSIGNED) DESC')
            ->value('admission_number');
        $next = $last ? ((int) last(explode('/', $last))) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    // ── Reject enrolment from student profile page ────────────────────────────

    public function openRejectModal(): void
    {
        $this->rejectionReason = '';
        $this->showRejectModal = true;
        $this->resetValidation();
    }

    public function confirmRejection(): void
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:5|max:500',
        ]);

        $student = $this->student;
        $student->update(['status' => 'withdrawn']);

        foreach ($student->parents as $parent) {
            $email      = $parent->_temp_email ?? $parent->user?->email;
            $parentName = $parent->_temp_name  ?? $parent->user?->name ?? 'Parent';
            if (! $email) continue;

            try {
                \Illuminate\Support\Facades\Notification::route('mail', $email)
                    ->notify(new EnrolmentRejectedNotification(
                        parentName:       $parentName,
                        studentFirstName: $student->first_name,
                        studentLastName:  $student->last_name,
                        classAppliedFor:  $student->class_applied_for ?? 'the applied class',
                        rejectionReason:  $this->rejectionReason,
                    ));
            } catch (\Exception $e) {
                Log::error('Rejection email failed from student profile', [
                    'email'   => $email,
                    'student' => $student->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        $this->showRejectModal = false;
        $this->rejectionReason = '';
        $this->student->refresh();
        session()->flash('success', 'Enrolment rejected and parent(s) notified by email.');
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
