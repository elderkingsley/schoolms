<?php

namespace App\Livewire\Admin\Enrolment;

use App\Models\AcademicSession;
use App\Models\Enrolment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Notifications\EnrolmentRejectedNotification;
use App\Notifications\ParentWelcomeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class EnrolmentQueue extends Component
{
    use WithPagination;

    public ?int  $reviewingId    = null;
    public ?int  $rejectingId    = null;
    public string $assignedClass  = '';
    public string $admissionNumber = '';

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
                Enrolment::firstOrCreate(
                    ['student_id' => $student->id, 'academic_session_id' => $session->id],
                    ['school_class_id' => $class->id, 'enrolled_at' => now(), 'status' => 'active']
                );
            }

            foreach ($student->parents as $parent) {
                // Skip if this parent already has a portal account linked
                if ($parent->user_id) continue;

                $tempEmail = $parent->_temp_email;

                if (! $tempEmail) continue;

                // A parent may have a second child being enrolled. Their User row
                // already exists from the first approval — if we try to INSERT again
                // MySQL throws a unique constraint violation on users.email.
                // Solution: find the existing User by email, or create a fresh one.
                $existingUser = User::where('email', $tempEmail)->first();

                if ($existingUser) {
                    // Parent already has an account — just link this parent record
                    // to it and send a simpler notification (no temp password needed,
                    // they already know their credentials)
                    $parent->update(['user_id' => $existingUser->id]);
                    // Notify about the new student being linked to their account
                    $existingUser->notify(new ParentWelcomeNotification($existingUser, $student, null));
                } else {
                    // Brand new parent — create the User account as before
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
                    $user->notify(new ParentWelcomeNotification($user, $student, $tempPassword));
                }
            }
        });

        $this->reviewingId = null;
        session()->flash('success', 'Student approved and parents notified.');
    }

    public function reject(int $studentId): void
    {
        $student = Student::with('parents')->findOrFail($studentId);
        $student->update(['status' => 'withdrawn']);

        foreach ($student->parents as $parent) {
            $email      = $parent->_temp_email ?? $parent->user?->email;
            $parentName = $parent->_temp_name  ?? $parent->user?->name ?? 'Parent';

            if (!$email) continue;

            try {
                \Illuminate\Support\Facades\Notification::route('mail', $email)
                    ->notify(new EnrolmentRejectedNotification(
                        $parentName,
                        $student->first_name,
                        $student->last_name,
                        $student->class_applied_for ?? 'the applied class',
                    ));
            } catch (\Exception $e) {
                Log::error('Rejection email failed', [
                    'email'   => $email,
                    'student' => $student->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        $this->rejectingId = null;
        session()->flash('success', 'Enrolment rejected and parent(s) notified by email.');
    }

    protected function generateAdmissionNumber(): string
    {
        $year   = now()->format('Y');
        $prefix = "NV/{$year}/";

        // Find the highest sequence number already used this year.
        // We look only at approved students (status = active) whose admission
        // number matches the NV/YYYY/NNNN format, so pending TEMP- numbers
        // are never counted and two simultaneous approvals can't collide.
        $last = Student::where('admission_number', 'like', $prefix . '%')
            ->where('status', 'active')
            ->orderByRaw('CAST(SUBSTRING_INDEX(admission_number, "/", -1) AS UNSIGNED) DESC')
            ->value('admission_number');

        if ($last) {
            // Extract the numeric part after the last "/" and add 1
            $lastSequence = (int) last(explode('/', $last));
            $next         = $lastSequence + 1;
        } else {
            // No approved students this year yet — start at 1
            $next = 1;
        }

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
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
