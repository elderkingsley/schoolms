<?php
// Deploy to: app/Livewire/Admin/Results/ResultEntry.php

namespace App\Livewire\Admin\Results;

use App\Models\Enrolment;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\StudentTermComment;
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

    // Head teacher general comments — keyed by student_id
    // Available for ALL class types (nursery and primary)
    public array $headComments = [];

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
        $this->scores       = [];
        $this->headComments = [];
        $this->saved        = false;
    }

    public function updatedSelectedClassId(): void
    {
        $this->selectedSubjectId = null;
        $this->scores       = [];
        $this->headComments = [];
        $this->saved        = false;
        $this->loadHeadComments();
    }

    public function updatedSelectedSubjectId(): void
    {
        $this->scores              = [];
        $this->saved               = false;
        $this->isPublished         = false;
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

    // ── Load scores/remarks ───────────────────────────────────────────────────

    protected function loadScores(): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId || ! $this->selectedSubjectId) {
            return;
        }

        $students     = $this->getStudents();
        $isRemarkOnly = $this->isRemarkOnly();

        foreach ($students as $student) {
            $this->scores[$student->id] = $isRemarkOnly
                ? ['remark' => '']
                : ['ca' => '', 'exam' => ''];
        }

        $existing = Result::where('term_id', $this->selectedTermId)
            ->where('subject_id', $this->selectedSubjectId)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        foreach ($existing as $studentId => $result) {
            if ($isRemarkOnly) {
                $this->scores[$studentId] = ['remark' => $result->remark ?? ''];
            } else {
                $this->scores[$studentId] = [
                    'ca'   => $result->ca_score !== null ? (string) $result->ca_score : '',
                    'exam' => $result->exam_score !== null ? (string) $result->exam_score : '',
                ];
            }
        }

        $this->isPublished = $existing->where('is_published', true)->isNotEmpty();
    }

    // ── Load head teacher comments ────────────────────────────────────────────

    protected function loadHeadComments(): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId) return;

        $students = $this->getStudents();

        foreach ($students as $student) {
            $this->headComments[$student->id] = '';
        }

        StudentTermComment::where('term_id', $this->selectedTermId)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->each(function ($comment) {
                $this->headComments[$comment->student_id] = $comment->head_teacher_comment ?? '';
            });
    }

    // ── Save scores ───────────────────────────────────────────────────────────

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

    // ── Save head teacher comments ────────────────────────────────────────────

    public function saveHeadComments(): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId) return;

        $rules = collect($this->headComments)
            ->mapWithKeys(fn($v, $id) => ["headComments.{$id}" => 'nullable|string|max:500'])
            ->toArray();

        $this->validate($rules);

        foreach ($this->headComments as $studentId => $comment) {
            StudentTermComment::updateOrCreate(
                ['student_id' => $studentId, 'term_id' => $this->selectedTermId],
                [
                    'head_teacher_comment' => trim($comment) ?: null,
                    'reviewed_by'          => auth()->id(),
                ]
            );
        }

        session()->flash('success', 'Head teacher comments saved.');
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function validateScores(): void
    {
        $rules = [];

        if ($this->isRemarkOnly()) {
            foreach ($this->scores as $id => $entry) {
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

    // ── Students ──────────────────────────────────────────────────────────────

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

        $students      = $this->getStudents();
        $selectedClass = $this->getSelectedClass();
        $isRemarkOnly  = $this->isRemarkOnly();

        if ($this->selectedClassId && $this->selectedTermId && empty($this->headComments) && $students->isNotEmpty()) {
            $this->loadHeadComments();
        }

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
