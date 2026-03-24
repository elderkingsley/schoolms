<?php

namespace App\Livewire\Admin\Enrolment;

use App\Models\AcademicSession;
use App\Models\Enrolment;
use App\Models\ParentGuardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Notifications\ParentWelcomeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class EnrolmentQueue extends Component
{
    use WithPagination;

    // Approval modal state
    public ?int  $reviewingId    = null;
    public string $assignedClass  = '';
    public string $admissionNumber = '';

    // Reject confirmation modal state
    public ?int  $rejectingId    = null;
    public string $rejectStudentName = '';

    // ── Approve flow ──────────────────────────────────────────────────────────

    public function approve(int $studentId): void
    {
        $student = Student::with('parents')->findOrFail($studentId);

        $this->reviewingId     = $studentId;
        $this->assignedClass   = $student->class_applied_for ?? '';
        $this->admissionNumber = $this->generateAdmissionNumber();
    }

    public function confirmApproval(): void
    {
        $this->validate([
            'assignedClass'   => 'required|exists:school_classes,name',
            'admissionNumber' => 'required|string|unique:students,admission_number',
        ]);

        $student = Student::with('parents')->findOrFail($this->reviewingId);

        DB::transaction(function () use ($student) {

            $student->update([
                'status'           => 'active',
                'admission_number' => $this->admissionNumber,
                'approved_at'      => now(),
                'approved_by'      => auth()->id(),
            ]);

            $session = AcademicSession::current();
            $class   = SchoolClass::where('name', $this->assignedClass)->first();

            if ($session && $class) {
                Enrolment::firstOrCreate([
                    'student_id'          => $student->id,
                    'academic_session_id' => $session->id,
                ], [
                    'school_class_id' => $class->id,
                    'enrolled_at'     => now(),
                    'status'          => 'active',
                ]);
            }

            foreach ($student->parents as $parent) {
                if ($parent->user_id) continue;

                $tempPassword = Str::random(10);

                $user = User::create([
                    'name'      => $parent->_temp_name,
                    'email'     => $parent->_temp_email,
                    'password'  => Hash::make($tempPassword),
                    'user_type' => 'parent',
                    'is_active' => true,
                ]);

                $user->assignRole('parent');
                $parent->update(['user_id' => $user->id]);
                $user->notify(new ParentWelcomeNotification($user, $student, $tempPassword));
            }
        });

        $this->reviewingId = null;
        session()->flash('success', "Student approved successfully. Parent login credentials have been sent.");
    }

    public function cancelApproval(): void
    {
        $this->reviewingId     = null;
        $this->assignedClass   = '';
        $this->admissionNumber = '';
    }

    // ── Reject flow ───────────────────────────────────────────────────────────

    // Step 1: show in-app confirmation modal
    public function confirmReject(int $studentId): void
    {
        $student = Student::findOrFail($studentId);
        $this->rejectingId       = $studentId;
        $this->rejectStudentName = "{$student->first_name} {$student->last_name}";
    }

    // Step 2: admin confirmed — do the rejection
    public function executeReject(): void
    {
        if (!$this->rejectingId) return;

        Student::findOrFail($this->rejectingId)->update(['status' => 'withdrawn']);

        $this->rejectingId       = null;
        $this->rejectStudentName = '';

        session()->flash('success', "Enrolment rejected and removed from queue.");
    }

    // Step 3: admin cancelled the rejection
    public function cancelReject(): void
    {
        $this->rejectingId       = null;
        $this->rejectStudentName = '';
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function generateAdmissionNumber(): string
    {
        $year  = now()->format('Y');
        $count = Student::whereYear('created_at', $year)->count() + 1;
        return "NV/{$year}/" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        $pending = Student::with('parents')
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        $classes = SchoolClass::orderBy('order')->pluck('name');

        return view('livewire.admin.enrolment.enrolment-queue', compact('pending', 'classes'))
            ->layout('layouts.admin', ['title' => 'Enrolment Queue']);
    }
}
