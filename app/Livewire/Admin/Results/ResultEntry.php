<?php

namespace App\Livewire\Admin\Results;

use App\Models\AcademicSession;
use App\Models\ClassSubject;
use App\Models\Enrolment;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Support\Collection;
use Livewire\Component;

class ResultEntry extends Component
{
    public ?int $selectedTermId    = null;
    public ?int $selectedClassId   = null;
    public ?int $selectedSubjectId = null;

    // scores[student_id] = ['ca' => '', 'exam' => '']
    public array $scores = [];

    public bool $saved = false;

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->selectedTermId = Term::current()?->id;
    }

    public function updatedSelectedTermId(): void
    {
        $this->selectedClassId   = null;
        $this->selectedSubjectId = null;
        $this->scores = [];
        $this->saved  = false;
    }

    public function updatedSelectedClassId(): void
    {
        $this->selectedSubjectId = null;
        $this->scores = [];
        $this->saved  = false;
    }

    public function updatedSelectedSubjectId(): void
    {
        $this->scores = [];
        $this->saved  = false;
        $this->loadScores();
    }

    // ── Load existing scores ──────────────────────────────────────────────────

    protected function loadScores(): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId || ! $this->selectedSubjectId) {
            return;
        }

        $students = $this->getStudents();

        // Pre-fill with empty entries
        foreach ($students as $student) {
            $this->scores[$student->id] = ['ca' => '', 'exam' => ''];
        }

        // Overwrite with any previously saved results
        $existing = Result::where('term_id', $this->selectedTermId)
            ->where('subject_id', $this->selectedSubjectId)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        foreach ($existing as $studentId => $result) {
            $this->scores[$studentId] = [
                'ca'   => $result->ca_score > 0 ? (string) $result->ca_score : '',
                'exam' => $result->exam_score > 0 ? (string) $result->exam_score : '',
            ];
        }
    }

    // ── Save scores ───────────────────────────────────────────────────────────

    public function save(bool $publish = false): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId || ! $this->selectedSubjectId) {
            return;
        }

        $this->validateScores();

        foreach ($this->scores as $studentId => $entry) {
            $ca   = max(0, min(40, (int) ($entry['ca']   ?? 0)));
            $exam = max(0, min(60, (int) ($entry['exam'] ?? 0)));

            // Skip completely blank rows — don't create empty result records
            if ($ca === 0 && $exam === 0 && empty($entry['ca']) && empty($entry['exam'])) {
                continue;
            }

            $total   = $ca + $exam;
            $grading = Subject::gradeFor($total);

            Result::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'subject_id' => $this->selectedSubjectId,
                    'term_id'    => $this->selectedTermId,
                ],
                [
                    'ca_score'     => $ca,
                    'exam_score'   => $exam,
                    'total'        => $total,
                    'grade'        => $grading['grade'],
                    'remark'       => $grading['remark'],
                    'is_published' => $publish,
                ]
            );
        }

        $this->saved = true;
        $verb = $publish ? 'saved and published' : 'saved';
        session()->flash('success', "Results {$verb} successfully.");
    }

    public function saveAndPublish(): void
    {
        $this->save(publish: true);
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

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function getStudents(): Collection
    {
        if (! $this->selectedClassId || ! $this->selectedTermId) return collect();

        $term = Term::find($this->selectedTermId);
        if (! $term) return collect();

        return Enrolment::with('student')
            ->where('school_class_id', $this->selectedClassId)
            ->where('academic_session_id', $term->academic_session_id)
            ->where('status', 'active')
            ->get()
            ->pluck('student')
            ->filter()
            ->sortBy('last_name');
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $terms   = Term::with('session')->orderByDesc('academic_session_id')->orderBy('id')->get();
        $classes = SchoolClass::orderBy('order')->get();

        // Subjects assigned to the selected class in the active session
        $subjects = collect();
        if ($this->selectedClassId && $this->selectedTermId) {
            $term = Term::find($this->selectedTermId);
            if ($term) {
                $subjects = Subject::active()
                    ->ordered()
                    ->whereHas('classes', fn($q) =>
                        $q->where('school_classes.id', $this->selectedClassId)
                          ->where('class_subjects.academic_session_id', $term->academic_session_id)
                    )
                    ->get();
            }
        }

        $students = $this->getStudents();

        return view('livewire.admin.results.result-entry',
            compact('terms', 'classes', 'subjects', 'students'))
            ->layout('layouts.admin', ['title' => 'Results Entry']);
    }
}
