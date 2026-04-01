<?php

namespace App\Livewire\Public;

use App\Models\TeacherRegistration;
use Livewire\Component;

/**
 * StaffRegistrationForm
 *
 * Public self-registration form for prospective teachers and TAs.
 * No authentication required — anyone with the link can apply.
 *
 * Submitted registrations land in the admin review queue
 * (TeacherManager → Registrations tab). An admin then approves
 * or rejects each application, which triggers account creation
 * and credential email on approval.
 *
 * Duplicate email detection prevents re-submissions.
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
            'email' => 'required|email|unique:teacher_registrations,email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role'  => 'required|in:teacher,teaching_assistant',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'email.unique' => 'An application with this email address already exists.',
        ];
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
