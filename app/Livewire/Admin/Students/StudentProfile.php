<?php

namespace App\Livewire\Admin\Students;

use App\Models\Student;
use Livewire\Component;

class StudentProfile extends Component
{
    public Student $student;

    // Called by the URL: /admin/students/{student}
    public function mount(Student $student): void
    {
        $this->student = $student->load([
            'parents',
            'enrolments.schoolClass',
            'enrolments.session',
            'results.subject',
            'results.term',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.students.student-profile')
            ->layout('layouts.admin', [
                'title' => $this->student->first_name . ' ' . $this->student->last_name,
            ]);
    }
}
