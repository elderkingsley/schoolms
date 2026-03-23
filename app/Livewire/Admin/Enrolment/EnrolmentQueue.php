<?php
// app/Livewire/Admin/Enrolment/EnrolmentQueue.php

namespace App\Livewire\Admin\Enrolment;

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

    public ?int $reviewingId = null;    // student being reviewed
    public string $assignedClass = '';
    public string $admissionNumber = '';

    public function approve(int $studentId): void
    {
        $student = Student::with('parents')->findOrFail($studentId);

        $this->reviewingId    = $studentId;
        $this->assignedClass  = $student->class_applied_for ?? '';
        $this->admissionNumber = $this->generateAdmissionNumber();
    }

    public function confirmApproval(): void
    {
        $this->validate([
            'assignedClass'    => 'required|exists:school_classes,name',
            'admissionNumber'  => 'required|string|unique:students,admission_number',
        ]);

        $student = Student::with('parents')->findOrFail($this->reviewingId);

        DB::transaction(function () use ($student) {

            // 1. Update student record
            $student->update([
                'status'           => 'active',
                'admission_number' => $this->admissionNumber,
                'approved_at'      => now(),
                'approved_by'      => auth()->id(),
            ]);

            // 2. Enrol in class for active session
            $session = \App\Models\AcademicSession::current();
            $class   = \App\Models\SchoolClass::where('name', $this->assignedClass)->first();

            if ($session && $class) {
                \App\Models\Enrolment::firstOrCreate([
                    'student_id'          => $student->id,
                    'academic_session_id' => $session->id,
                ], [
                    'school_class_id' => $class->id,
                    'enrolled_at'     => now(),
                    'status'          => 'active',
                ]);
            }

            // 3. Create User accounts for each parent
            foreach ($student->parents as $parent) {
                if ($parent->user_id) continue; // already has account

                $tempPassword = Str::random(10);

                $user = User::create([
                    'name'      => $parent->_temp_name,
                    'email'     => $parent->_temp_email,
                    'password'  => Hash::make($tempPassword),
                    'user_type' => 'parent',
                    'is_active' => true,
                ]);

                $user->assignRole('parent');

                // Link user to parent record
                $parent->update(['user_id' => $user->id]);

                // Send welcome email with credentials
                $user->notify(new ParentWelcomeNotification($user, $student, $tempPassword));
            }
        });

        $this->reviewingId = null;
        session()->flash('success', "Student approved and parents notified.");
    }

    public function reject(int $studentId): void
    {
        Student::findOrFail($studentId)->update(['status' => 'withdrawn']);
        session()->flash('success', "Enrolment rejected.");
    }

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
