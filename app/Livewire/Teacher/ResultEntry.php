<?php
// Deploy to: app/Livewire/Teacher/ResultEntry.php
// REPLACES existing file.

namespace App\Livewire\Teacher;

use App\Models\Enrolment;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\StudentTermComment;
use App\Models\StudentTraitScore;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Support\Collection;
use Livewire\Component;

class ResultEntry extends Component
{
    public ?int  $selectedClassId   = null;
    public ?int  $selectedSubjectId = null;
    public ?int  $selectedTermId    = null;
    public array $scores            = [];
    public bool  $saved             = false;
    public bool  $isLocked          = false;

    // Teacher general comments — keyed by student_id
    public array $teacherComments = [];

    // Trait scores — keyed by student_id, then by trait_key
    // $traitScores[student_id][trait_key] = '4'
    public array $traitScores = [];

    // Attendance — keyed by enrolment_id
    // $attendance[enrolment_id] = ['present' => '112', 'absent' => '12']
    public array $attendance = [];

    // ── Mount ─────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->selectedTermId  = Term::current()?->id;
        $this->selectedClassId = request('class') ? (int) request('class') : null;
        if ($this->selectedClassId) {
            $this->loadAll();
        }
    }

    // ── Watchers ──────────────────────────────────────────────────────────────

    public function updatedSelectedClassId(): void
    {
        $this->selectedSubjectId = null;
        $this->scores            = [];
        $this->teacherComments   = [];
        $this->traitScores       = [];
        $this->attendance        = [];
        $this->saved             = false;
        $this->loadAll();
    }

    public function updatedSelectedSubjectId(): void
    {
        $this->scores   = [];
        $this->saved    = false;
        $this->isLocked = false;
        $this->loadScores();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function getSelectedClass(): ?SchoolClass
    {
        return $this->selectedClassId ? SchoolClass::find($this->selectedClassId) : null;
    }

    protected function isRemarkOnly(): bool
    {
        return $this->getSelectedClass()?->isRemarkOnly() ?? false;
    }

    /**
     * Loads everything that depends on the selected class.
     * Called on mount and whenever selectedClassId changes.
     */
    protected function loadAll(): void
    {
        $this->loadTeacherComments();
        $this->loadTraitScores();
        $this->loadAttendance();
        if ($this->selectedSubjectId) {
            $this->loadScores();
        }
    }

    // ── Load scores ───────────────────────────────────────────────────────────

    protected function loadScores(): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId || ! $this->selectedSubjectId) return;

        $students     = $this->getStudents();
        $isRemarkOnly = $this->isRemarkOnly();

        foreach ($students as $student) {
            $this->scores[$student->id] = $isRemarkOnly
                ? ['remark' => '', 'eval' => '']
                : ['ca' => '', 'exam' => '', 'remark' => ''];
        }

        $existing = Result::where('term_id', $this->selectedTermId)
            ->where('subject_id', $this->selectedSubjectId)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()->keyBy('student_id');

        foreach ($existing as $sid => $result) {
            if ($isRemarkOnly) {
                $this->scores[$sid] = [
                    'remark' => $result->remark ?? '',
                    'eval'   => $result->admin_comment ?? '',
                ];
            } else {
                $this->scores[$sid] = [
                    'ca'     => $result->ca_score !== null ? (string) $result->ca_score : '',
                    'exam'   => $result->exam_score !== null ? (string) $result->exam_score : '',
                    'remark' => $result->remark ?? '',
                ];
            }
        }

        $this->isLocked = $existing->where('is_published', true)->isNotEmpty();
    }

    // ── Load teacher comments ─────────────────────────────────────────────────

    protected function loadTeacherComments(): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId) return;

        $students = $this->getStudents();
        foreach ($students as $student) {
            $this->teacherComments[$student->id] = '';
        }

        StudentTermComment::where('term_id', $this->selectedTermId)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->each(fn($c) => $this->teacherComments[$c->student_id] = $c->teacher_comment ?? '');
    }

    // ── Load trait scores ─────────────────────────────────────────────────────

    protected function loadTraitScores(): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId) return;

        $students      = $this->getStudents();
        $isPreschool   = $this->isRemarkOnly();
        $allTraitKeys  = array_keys(StudentTraitScore::allKeysFor($isPreschool));

        foreach ($students as $student) {
            $existing = StudentTraitScore::forStudentTerm($student->id, $this->selectedTermId);
            $row = [];
            foreach ($allTraitKeys as $key) {
                $row[$key] = isset($existing[$key]) ? (string) $existing[$key] : '';
            }
            $this->traitScores[$student->id] = $row;
        }
    }

    // ── Load attendance ───────────────────────────────────────────────────────

    protected function loadAttendance(): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId) return;

        $term = Term::find($this->selectedTermId);
        if (! $term) return;

        $enrolments = Enrolment::where('school_class_id', $this->selectedClassId)
            ->where('academic_session_id', $term->academic_session_id)
            ->where('status', 'active')
            ->get();

        foreach ($enrolments as $enrolment) {
            $this->attendance[$enrolment->id] = [
                'present' => $enrolment->times_present !== null ? (string) $enrolment->times_present : '',
                'absent'  => $enrolment->times_absent  !== null ? (string) $enrolment->times_absent  : '',
            ];
        }
    }

    // ── Save scores ───────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->persistScores(submit: false);
        session()->flash('success', 'Results saved as draft.');
    }

    public function submitForReview(): void
    {
        $this->persistScores(submit: true);
        session()->flash('success', 'Results submitted for admin review.');
    }

    protected function persistScores(bool $submit): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId || ! $this->selectedSubjectId) return;

        if ($this->isLocked) {
            session()->flash('error', 'These results have been published. Contact admin to make corrections.');
            return;
        }

        $this->validateScores();
        $isRemarkOnly = $this->isRemarkOnly();

        foreach ($this->scores as $studentId => $entry) {
            if ($isRemarkOnly) {
                // Preschool: save narrative eval + remark dropdown
                $remark = trim($entry['remark'] ?? '');
                $eval   = trim($entry['eval']   ?? '');
                if (empty($remark) && empty($eval)) continue;

                $data = [
                    'ca_score'      => null,
                    'exam_score'    => null,
                    'total'         => null,
                    'grade'         => null,
                    'remark'        => $remark ?: null,
                    'admin_comment' => $eval   ?: null,
                ];
            } else {
                // Primary: CA + Exam + teacher-chosen remark
                $ca   = max(0, min(40, (int) ($entry['ca']   ?? 0)));
                $exam = max(0, min(60, (int) ($entry['exam'] ?? 0)));

                if ($ca === 0 && $exam === 0 && empty($entry['ca']) && empty($entry['exam'])) continue;

                $total   = $ca + $exam;
                $grading = Subject::gradeFor($total);

                // Teacher's explicit remark takes priority; fall back to computed remark.
                $remark = trim($entry['remark'] ?? '');
                if (empty($remark)) {
                    $remark = $grading['remark'];
                }

                $data = [
                    'ca_score'   => $ca,
                    'exam_score' => $exam,
                    'total'      => $total,
                    'grade'      => $grading['grade'],
                    'remark'     => $remark,
                ];
            }

            if ($submit) {
                $data['submitted_by'] = auth()->id();
                $data['submitted_at'] = now();
            } else {
                $data['submitted_at'] = null;
                $data['submitted_by'] = null;
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

    // ── Save teacher comments ─────────────────────────────────────────────────

    public function saveTeacherComments(bool $submit = false): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId) return;

        if ($this->isLocked) {
            session()->flash('error', 'Results are published. Contact admin to make corrections.');
            return;
        }

        $rules = collect($this->teacherComments)
            ->mapWithKeys(fn($v, $id) => ["teacherComments.{$id}" => 'nullable|string|max:500'])
            ->toArray();
        $this->validate($rules);

        foreach ($this->teacherComments as $studentId => $comment) {
            StudentTermComment::updateOrCreate(
                ['student_id' => $studentId, 'term_id' => $this->selectedTermId],
                [
                    'teacher_comment' => trim($comment) ?: null,
                    'written_by'      => auth()->id(),
                    'submitted_at'    => $submit ? now() : null,
                ]
            );
        }

        session()->flash('success', $submit ? 'Comments submitted for review.' : 'Comments saved as draft.');
    }

    public function submitTeacherComments(): void
    {
        $this->saveTeacherComments(submit: true);
    }

    // ── Save trait scores ─────────────────────────────────────────────────────

    public function saveTraitScores(): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId) return;

        if ($this->isLocked) {
            session()->flash('error', 'Results are published. Contact admin to make corrections.');
            return;
        }

        $isPreschool  = $this->isRemarkOnly();
        $validKeys    = array_keys(StudentTraitScore::allKeysFor($isPreschool));
        $maxScore     = 5;

        // Build validation rules dynamically for all student × trait combinations
        $rules = [];
        foreach ($this->traitScores as $studentId => $traits) {
            foreach ($validKeys as $key) {
                $rules["traitScores.{$studentId}.{$key}"] = "nullable|integer|min:1|max:{$maxScore}";
            }
        }
        $this->validate($rules);

        foreach ($this->traitScores as $studentId => $traits) {
            // Only save the keys that belong to this class type
            $filtered = array_intersect_key($traits, array_flip($validKeys));
            StudentTraitScore::saveBatch((int) $studentId, $this->selectedTermId, $filtered);
        }

        session()->flash('success', 'Trait scores saved.');
    }

    // ── Save attendance ───────────────────────────────────────────────────────

    public function saveAttendance(): void
    {
        if (! $this->selectedTermId || ! $this->selectedClassId) return;

        if ($this->isLocked) {
            session()->flash('error', 'Results are published. Contact admin to make corrections.');
            return;
        }

        $rules = [];
        foreach ($this->attendance as $enrolmentId => $entry) {
            $rules["attendance.{$enrolmentId}.present"] = 'nullable|integer|min:0|max:366';
            $rules["attendance.{$enrolmentId}.absent"]  = 'nullable|integer|min:0|max:366';
        }
        $this->validate($rules);

        foreach ($this->attendance as $enrolmentId => $entry) {
            Enrolment::where('id', $enrolmentId)->update([
                'times_present' => $entry['present'] !== '' ? (int) $entry['present'] : null,
                'times_absent'  => $entry['absent']  !== '' ? (int) $entry['absent']  : null,
            ]);
        }

        session()->flash('success', 'Attendance saved.');
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function validateScores(): void
    {
        $rules = [];
        $validRemarks = Subject::remarkOptions();

        if ($this->isRemarkOnly()) {
            foreach ($this->scores as $id => $entry) {
                $rules["scores.{$id}.remark"] = 'nullable|string|in:' . implode(',', $validRemarks);
                $rules["scores.{$id}.eval"]   = 'nullable|string|max:1000';
            }
        } else {
            foreach ($this->scores as $id => $entry) {
                $rules["scores.{$id}.ca"]     = 'nullable|integer|min:0|max:40';
                $rules["scores.{$id}.exam"]   = 'nullable|integer|min:0|max:60';
                $rules["scores.{$id}.remark"] = 'nullable|string|in:' . implode(',', $validRemarks);
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
            ->get()->pluck('student')->filter()->sortBy('last_name');
    }

    // ── Enrolments (for attendance keying) ───────────────────────────────────

    protected function getEnrolmentsWithStudents(): Collection
    {
        if (! $this->selectedClassId || ! $this->selectedTermId) return collect();
        $term = Term::find($this->selectedTermId);
        if (! $term) return collect();

        return Enrolment::with('student')
            ->where('school_class_id', $this->selectedClassId)
            ->where('academic_session_id', $term->academic_session_id)
            ->where('status', 'active')
            ->get()
            ->filter(fn($e) => $e->student)
            ->sortBy('student.last_name');
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $user = auth()->user();

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

        $students              = $this->getStudents();
        $enrolmentsWithStudents = $this->getEnrolmentsWithStudents();
        $selectedClass         = $this->getSelectedClass();
        $isRemarkOnly          = $this->isRemarkOnly();

        // Lazy load if first render with class selected
        if ($this->selectedClassId && $this->selectedTermId && $students->isNotEmpty()) {
            if (empty($this->teacherComments)) $this->loadTeacherComments();
            if (empty($this->traitScores))     $this->loadTraitScores();
            if (empty($this->attendance))      $this->loadAttendance();
        }

        // Extract component state properties as local variables for compact()
        $isLocked = $this->isLocked;

        $isSubmitted = false;
        if ($this->selectedTermId && $this->selectedClassId && $this->selectedSubjectId && $students->isNotEmpty()) {
            $isSubmitted = Result::where('term_id', $this->selectedTermId)
                ->where('subject_id', $this->selectedSubjectId)
                ->whereIn('student_id', $students->pluck('id'))
                ->whereNotNull('submitted_at')
                ->exists();
        }

        $isPreschool    = $isRemarkOnly;
        $psychomotorDef = StudentTraitScore::PSYCHOMOTOR;
        $affectiveDef   = $isPreschool
            ? StudentTraitScore::AFFECTIVE_PRESCHOOL
            : StudentTraitScore::AFFECTIVE_PRIMARY;

        $remarkOptions = Subject::remarkOptions();

        // Pass term school_days_count so blade never calls model classes directly.
        $termSchoolDays = $this->selectedTermId
            ? Term::find($this->selectedTermId)?->school_days_count
            : null;

        return view('livewire.teacher.result-entry', compact(
            'myClasses', 'terms', 'subjects', 'students',
            'enrolmentsWithStudents',
            'isSubmitted', 'isLocked', 'selectedClass', 'isRemarkOnly',
            'psychomotorDef', 'affectiveDef', 'remarkOptions',
            'termSchoolDays'
        ))->layout('layouts.teacher', ['title' => 'Results Entry']);
    }
}
