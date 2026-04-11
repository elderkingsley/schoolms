<?php
// Deploy to: app/Livewire/Admin/Results/ResultEntry.php

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

    // For scored classes:  scores[student_id] = ['ca' => '', 'exam' => '']
    // For remark-only:     scores[student_id] = ['remark' => '']
    public array $scores = [];

    public bool $saved               = false;
    public bool $isPublished         = false;
    public bool $confirmingOverwrite = false;

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
        $this->scores             = [];
        $this->saved              = false;
        $this->isPublished        = false;
        $this->confirmingOverwrite = false;
        $this->loadScores();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function getSelectedClass(): ?SchoolClass
    {
        return $this->selectedClassId
            ? SchoolClass::find($this->selectedClassId)
            : null;
    }

    protected function isRemarkOnly(): bool
    {
        return $this->getSelectedClass()?->isRemarkOnly() ?? false;
    }

    // ── Load existing scores/remarks ──────────────────────────────────────────

    protected function loadScores(): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId || ! $this->selectedSubjectId) {
            return;
        }

        $students      = $this->getStudents();
        $isRemarkOnly  = $this->isRemarkOnly();

        // Pre-fill with empty entries matching the class format
        foreach ($students as $student) {
            $this->scores[$student->id] = $isRemarkOnly
                ? ['remark' => '']
                : ['ca' => '', 'exam' => ''];
        }

        // Overwrite with any previously saved results
        $existing = Result::where('term_id', $this->selectedTermId)
            ->where('subject_id', $this->selectedSubjectId)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        foreach ($existing as $studentId => $result) {
            if ($isRemarkOnly) {
                $this->scores[$studentId] = [
                    'remark' => $result->remark ?? '',
                ];
            } else {
                $this->scores[$studentId] = [
                    'ca'   => $result->ca_score !== null ? (string) $result->ca_score : '',
                    'exam' => $result->exam_score !== null ? (string) $result->exam_score : '',
                ];
            }
        }

        $this->isPublished = $existing->where('is_published', true)->isNotEmpty();
    }

    // ── Save ──────────────────────────────────────────────────────────────────

    public function requestEdit(): void
    {
        $this->confirmingOverwrite = true;
    }

    public function confirmOverwrite(): void
    {
        $this->confirmingOverwrite = false;
        $this->isPublished         = false;
        session()->flash('success', 'Results unlocked. Make your corrections then save or republish.');
    }

    public function unpublish(): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId || ! $this->selectedSubjectId) return;

        $students = $this->getStudents();

        Result::where('term_id', $this->selectedTermId)
            ->where('subject_id', $this->selectedSubjectId)
            ->whereIn('student_id', $students->pluck('id'))
            ->update([
                'is_published' => false,
                'submitted_at' => null,
                'submitted_by' => null,
            ]);

        $this->isPublished         = false;
        $this->confirmingOverwrite = false;
        $this->loadScores();

        session()->flash('success', 'Results unpublished. The teacher can now resubmit after corrections.');
    }

    public function save(bool $publish = false): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId || ! $this->selectedSubjectId) {
            return;
        }

        $this->validateScores();

        $isRemarkOnly = $this->isRemarkOnly();

        foreach ($this->scores as $studentId => $entry) {
            if ($isRemarkOnly) {
                // Remark-only mode — skip blank rows
                $remark = trim($entry['remark'] ?? '');
                if (empty($remark)) continue;

                Result::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $this->selectedSubjectId,
                        'term_id'    => $this->selectedTermId,
                    ],
                    [
                        'ca_score'     => null,
                        'exam_score'   => null,
                        'total'        => null,
                        'grade'        => null,
                        'remark'       => $remark,
                        'is_published' => $publish,
                    ]
                );
            } else {
                // Scored mode — existing behaviour
                $ca   = max(0, min(40, (int) ($entry['ca']   ?? 0)));
                $exam = max(0, min(60, (int) ($entry['exam'] ?? 0)));

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

        if ($this->isRemarkOnly()) {
            foreach ($this->scores as $id => $entry) {
                // Remark is optional (blank rows are skipped), but if filled it must be a string
                $rules["scores.{$id}.remark"] = 'nullable|string|max:200';
            }
        } else {
            foreach ($this->scores as $id => $entry) {
                $rules["scores.{$id}.ca"]   = 'nullable|integer|min:0|max:40';
                $rules["scores.{$id}.exam"] = 'nullable|integer|min:0|max:60';
            }
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

        $students     = $this->getStudents();
        $selectedClass = $this->getSelectedClass();
        $isRemarkOnly  = $this->isRemarkOnly();

        $isPublished         = $this->isPublished;
        $confirmingOverwrite = $this->confirmingOverwrite;

        return view('livewire.admin.results.result-entry',
            compact(
                'terms', 'classes', 'subjects', 'students',
                'isPublished', 'confirmingOverwrite',
                'selectedClass', 'isRemarkOnly'
            ))
            ->layout('layouts.admin', ['title' => 'Results Entry']);
    }
}
