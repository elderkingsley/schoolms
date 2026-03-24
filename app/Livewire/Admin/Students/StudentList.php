<?php

namespace App\Livewire\Admin\Students;

use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class StudentList extends Component
{
    use WithPagination;

    public string $search       = '';
    public string $filterClass  = '';
    public string $filterStatus = 'active';

    public function updatedSearch(): void      { $this->resetPage(); }
    public function updatedFilterClass(): void  { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    public function render()
    {
        $activeSession = AcademicSession::current();

        $students = Student::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('admission_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterClass && $activeSession, function ($q) use ($activeSession) {
                $q->whereHas('enrolments', function ($q) use ($activeSession) {
                    $q->where('academic_session_id', $activeSession->id)
                      ->whereHas('schoolClass', fn($q) =>
                          $q->where('name', $this->filterClass)
                      );
                });
            })
            ->with(['enrolments' => function ($q) use ($activeSession) {
                if ($activeSession) {
                    $q->where('academic_session_id', $activeSession->id)
                      ->with('schoolClass');
                }
            }, 'parents'])
            ->latest()
            ->paginate(20);

        $classes = SchoolClass::orderBy('order')->pluck('name');

        return view('livewire.admin.students.student-list', compact('students', 'classes', 'activeSession'))
            ->layout('layouts.admin', ['title' => 'Students']);
    }
}
