<?php

namespace App\Livewire\Admin\Students;

use App\Models\SchoolClass;
use App\Models\Student;
use Livewire\Component;

class StudentProfile extends Component
{
    public Student $student;

    // Edit mode toggle
    public bool $editing = false;

    // Editable fields — mirrors all Student $fillable fields
    public string $firstName    = '';
    public string $lastName     = '';
    public string $otherName    = '';
    public string $gender       = '';
    public string $dateOfBirth  = '';
    public string $status       = '';
    public string $notes        = '';
    public string $medicalNotes = '';
    public string $classAppliedFor = '';

    public function mount(Student $student): void
    {
        $this->student = $student->load([
            'parents.user',
            'enrolments.schoolClass',
            'enrolments.session',
            'results.subject',
            'results.term',
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
        ]);

        $this->student->update([
            'first_name'       => $data['firstName'],
            'last_name'        => $data['lastName'],
            'other_name'       => $data['otherName'] ?: null,
            'gender'           => $data['gender'],
            'date_of_birth'    => $data['dateOfBirth'] ?: null,
            'status'           => $data['status'],
            'notes'            => $data['notes'] ?: null,
            'medical_notes'    => $data['medicalNotes'] ?: null,
            'class_applied_for'=> $data['classAppliedFor'] ?: null,
        ]);

        $this->student->refresh();
        $this->editing = false;

        session()->flash('success', 'Student record updated successfully.');
    }

    public function render()
    {
        return view('livewire.admin.students.student-profile')
            ->layout('layouts.admin', [
                'title' => $this->student->first_name . ' ' . $this->student->last_name,
            ]);
    }
}
