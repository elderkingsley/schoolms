<?php

namespace App\Livewire\Teacher;

use App\Models\Enrolment;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Support\Collection;
use Livewire\Component;

class ResultEntry extends Component
{
    public ?int   $selectedClassId   = null;
    public ?int   $selectedSubjectId = null;
    public ?int   $selectedTermId    = null;
    public array  $scores            = [];
    public bool   $saved             = false;
    public bool   $isLocked          = false; // true once submitted — teacher cannot edit

    public function mount(): void
    {
        $this->selectedTermId  = Term::current()?->id;
        $this->selectedClassId = request('class') ? (int) request('class') : null;
        if ($this->selectedClassId) {
            $this->loadScores();
        }
    }

    public function updatedSelectedClassId(): void
    {
        $this->selectedSubjectId = null;
        $this->scores = [];
        $this->saved  = false;
    }

    public function updatedSelectedSubjectId(): void
    {
        $this->scores    = [];
        $this->saved     = false;
        $this->isLocked  = false;
        $this->loadScores();
    }

    protected function loadScores(): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId || ! $this->selectedSubjectId) return;

        $students = $this->getStudents();
        foreach ($students as $student) {
            $this->scores[$student->id] = ['ca' => '', 'exam' => ''];
        }

        $existing = Result::where('term_id', $this->selectedTermId)
            ->where('subject_id', $this->selectedSubjectId)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()->keyBy('student_id');

        foreach ($existing as $sid => $result) {
            $this->scores[$sid] = [
                'ca'   => $result->ca_score > 0 ? (string) $result->ca_score : '',
                'exam' => $result->exam_score > 0 ? (string) $result->exam_score : '',
            ];
        }

        // Lock if any result in this set has been submitted
        $this->isLocked = $existing->whereNotNull('submitted_at')->isNotEmpty();
    }

    public function save(): void
    {
        $this->persistScores(submit: false);
        session()->flash('success', 'Results saved as draft.');
    }

    public function submitForReview(): void
    {
        $this->persistScores(submit: true);
        session()->flash('success', 'Results submitted for admin review. The admin will review and publish them.');
    }

    protected function persistScores(bool $submit): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId || ! $this->selectedSubjectId) return;

        // Hard server-side block — prevents direct method invocations bypassing the UI lock
        if ($this->isLocked) {
            session()->flash('error', 'These results are submitted and locked. Contact the admin to make changes.');
            return;
        }

        $this->validateScores();

        foreach ($this->scores as $studentId => $entry) {
            $ca   = max(0, min(40, (int) ($entry['ca']   ?? 0)));
            $exam = max(0, min(60, (int) ($entry['exam'] ?? 0)));

            if ($ca === 0 && $exam === 0 && empty($entry['ca']) && empty($entry['exam'])) continue;

            $total   = $ca + $exam;
            $grading = Subject::gradeFor($total);

            $data = [
                'ca_score'   => $ca,
                'exam_score' => $exam,
                'total'      => $total,
                'grade'      => $grading['grade'],
                'remark'     => $grading['remark'],
            ];

            if ($submit) {
                $data['submitted_by'] = auth()->id();
                $data['submitted_at'] = now();
            }

            Result::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'subject_id' => $this->selectedSubjectId,
                    'term_id'    => $this->selectedTermId,
                ],
                $data
            );
        }

        $this->saved = true;
    }

    protected function validateScores(): void
    {
        $rules = [];
        foreach ($this->scores as $id => $entry) {
            $rules["scores.{$id}.ca"]   = 'nullable|integer|min:0|max:40';
            $rules["scores.{$id}.exam"] = 'nullable|integer|min:0|max:60';
        }
        $this->validate($rules);
    }

    protected function getStudents(): Collection
    {
        if (! $this->selectedClassId || ! $this->selectedTermId) return collect();
        $term = Term::find($this->selectedTermId);
        if (! $term) return collect();

        return Enrolment::with('student')
            ->where('school_class_id', $this->selectedClassId)
            ->where('academic_session_id', $term->academic_session_id)
            ->where('status', 'active')
            ->get()->pluck('student')->filter()->sortBy('last_name');
    }

    public function render()
    {
        $user = auth()->user();

        // Only classes where this teacher is form teacher
        $myClasses = SchoolClass::where('form_teacher_id', $user->id)->ordered()->get();
        $terms     = Term::with('session')->orderByDesc('academic_session_id')->orderBy('id')->get();

        $subjects = collect();
        if ($this->selectedClassId && $this->selectedTermId) {
            $term = Term::find($this->selectedTermId);
            if ($term) {
                $subjects = Subject::active()->ordered()
                    ->whereHas('classes', fn($q) =>
                        $q->where('school_classes.id', $this->selectedClassId)
                          ->where('class_subjects.academic_session_id', $term->academic_session_id)
                    )->get();
            }
        }

        $students = $this->getStudents();

        // Check if this class/subject/term has already been submitted
        $isSubmitted = false;
        if ($this->selectedTermId && $this->selectedClassId && $this->selectedSubjectId && $students->isNotEmpty()) {
            $isSubmitted = Result::where('term_id', $this->selectedTermId)
                ->where('subject_id', $this->selectedSubjectId)
                ->whereIn('student_id', $students->pluck('id'))
                ->whereNotNull('submitted_at')
                ->exists();
        }

        $isLocked = $this->isLocked;

        return view('livewire.teacher.result-entry',
            compact('myClasses', 'terms', 'subjects', 'students', 'isSubmitted', 'isLocked'))
            ->layout('layouts.teacher', ['title' => 'Results Entry']);
    }
}
