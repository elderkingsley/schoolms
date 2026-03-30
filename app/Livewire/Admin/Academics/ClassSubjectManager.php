<?php

namespace App\Livewire\Admin\Academics;

use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use Livewire\Component;

class ClassSubjectManager extends Component
{
    public ?int $selectedSessionId = null;

    public function mount(): void
    {
        $this->selectedSessionId = AcademicSession::current()?->id;
    }

    public function updatedSelectedSessionId(): void
    {
        // Re-render picks up new assignments automatically
    }

    /**
     * Toggle a subject on/off for a given class in the selected session.
     * Called directly from the checkbox — saves instantly, no Save button needed.
     */
    public function toggle(int $classId, int $subjectId): void
    {
        if (! $this->selectedSessionId) return;

        $existing = ClassSubject::where([
            'school_class_id'     => $classId,
            'subject_id'          => $subjectId,
            'academic_session_id' => $this->selectedSessionId,
        ])->first();

        if ($existing) {
            // Already assigned — remove it
            // Block removal if results exist for this class/subject/session
            $hasResults = \App\Models\Result::where('subject_id', $subjectId)
                ->whereHas('student.enrolments', fn($q) =>
                    $q->where('school_class_id', $classId)
                      ->where('academic_session_id', $this->selectedSessionId)
                )
                ->exists();

            if ($hasResults) {
                session()->flash('error', 'Cannot remove — results have been recorded for this subject in this class.');
                return;
            }

            $existing->delete();
        } else {
            // Not assigned — add it
            ClassSubject::create([
                'school_class_id'     => $classId,
                'subject_id'          => $subjectId,
                'academic_session_id' => $this->selectedSessionId,
            ]);
        }
    }

    /**
     * Copy the entire class-subject assignment from one session to another.
     * Convenience for new academic year setup.
     */
    public function copyFromSession(int $sourceSessionId): void
    {
        if (! $this->selectedSessionId || $sourceSessionId === $this->selectedSessionId) return;

        $source = ClassSubject::where('academic_session_id', $sourceSessionId)->get();

        foreach ($source as $cs) {
            ClassSubject::firstOrCreate([
                'school_class_id'     => $cs->school_class_id,
                'subject_id'          => $cs->subject_id,
                'academic_session_id' => $this->selectedSessionId,
            ]);
        }

        session()->flash('success', 'Class-subject assignments copied successfully.');
    }

    public function render()
    {
        $sessions = AcademicSession::orderByDesc('id')->get();
        $classes  = SchoolClass::orderBy('order')->get();
        $subjects = Subject::active()->ordered()->get();

        // Build a lookup set: "classId-subjectId" => true
        // Much faster than querying per cell in the blade template
        $assigned = collect();
        if ($this->selectedSessionId) {
            $assigned = ClassSubject::where('academic_session_id', $this->selectedSessionId)
                ->get()
                ->mapWithKeys(fn($cs) => ["{$cs->school_class_id}-{$cs->subject_id}" => true]);
        }

        $previousSession = $this->selectedSessionId
            ? AcademicSession::where('id', '<', $this->selectedSessionId)->orderByDesc('id')->first()
            : null;

        return view('livewire.admin.academics.class-subject-manager',
            compact('sessions', 'classes', 'subjects', 'assigned', 'previousSession'))
            ->layout('layouts.admin', ['title' => 'Class Subjects']);
    }
}
