<?php
// Deploy to: app/Livewire/Public/StaffRegistrationForm.php

namespace App\Livewire\Public;

use App\Models\TeacherRegistration;
use App\Models\User;
use Livewire\Component;

/**
 * StaffRegistrationForm
 *
 * Public self-registration form for prospective teachers and TAs.
 * No authentication required — anyone with the link can apply.
 *
 * Submitted registrations land in the admin review queue
 * (TeacherManager → Registrations tab). An admin then approves
 * or rejects each application, which triggers account promotion
 * and credential email on approval.
 *
 * Email uniqueness rules:
 *   - Rejected outright if the email already has a pending or approved
 *     teacher registration (prevents duplicate applications).
 *   - Rejected outright if the email belongs to a non-parent user
 *     (admins, existing teachers, accountants cannot re-register).
 *   - ALLOWED if the email belongs to a parent — a parent applying
 *     to join as a teacher is a legitimate scenario. On approval,
 *     TeacherManager will promote their existing account rather than
 *     creating a duplicate.
 */
class StaffRegistrationForm extends Component
{
    public string $name  = '';
    public string $email = '';
    public string $phone = '';
    public string $role  = 'teacher';
    public string $notes = '';

    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'name'  => 'required|string|min:2|max:100',
            'email' => [
                'required',
                'email',
                    // Custom rule — blocks duplicates and non-parent existing users
                function (string $attribute, mixed $value, \Closure $fail) {
                    // Block if a pending or approved registration already exists.
                    // A rejected application is allowed to be resubmitted.
                    $existingRegistration = \App\Models\TeacherRegistration::where('email', $value)
                        ->whereIn('status', ['pending', 'approved'])
                        ->first();

                    if ($existingRegistration) {
                        $status = $existingRegistration->status;
                        if ($status === 'approved') {
                            $fail('This email address has already been approved as a staff account. Please contact the admin if you need help logging in.');
                        } else {
                            $fail('An application with this email address is already pending review.');
                        }
                        return;
                    }

                    $existingUser = User::where('email', $value)->first();

                    if (! $existingUser) {
                        // No user account at all — fine, proceed
                        return;
                    }

                    if ($existingUser->hasRole('parent') || $existingUser->user_type === 'parent') {
                        // Parent applying as teacher — allowed.
                        // TeacherManager::approveRegistration() will promote
                        // this existing account instead of creating a new one.
                        return;
                    }

                    // Any other user type (admin, teacher, accountant, etc.)
                    // already has a staff account — block the registration.
                    $fail('A staff account already exists for this email address. Please contact the admin if you need access.');
                },
            ],
            'phone' => 'nullable|string|max:20',
            'role'  => 'required|in:teacher,teaching_assistant',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [];
    }

    public function submit(): void
    {
        $this->validate();

        TeacherRegistration::create([
            'name'   => $this->name,
            'email'  => $this->email,
            'phone'  => $this->phone ?: null,
            'role'   => $this->role,
            'notes'  => $this->notes ?: null,
            'status' => 'pending',
        ]);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.public.staff-registration-form')
            ->layout('layouts.public', ['title' => 'Staff Registration — Nurtureville']);
    }
}
