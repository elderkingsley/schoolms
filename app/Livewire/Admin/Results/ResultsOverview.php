<?php

namespace App\Livewire\Admin\Results;

use App\Jobs\SendReportCardJob;
use App\Models\Enrolment;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Livewire\Component;

class ResultsOverview extends Component
{
    public ?int    $selectedTermId   = null;
    public ?int    $selectedClassId  = null;

    // Comment modal
    public ?int    $commentStudentId = null;
    public string  $commentText      = '';
    public bool    $showCommentModal = false;

    public function mount(): void
    {
        $this->selectedTermId = Term::current()?->id;
    }

    public function updatedSelectedTermId(): void { $this->selectedClassId = null; }

    // ── Publish / Unpublish ───────────────────────────────────────────────────

    public function publishAll(): void
    {
        Result::where('term_id', $this->selectedTermId)
            ->whereIn('student_id', $this->getStudentIds())
            ->update(['is_published' => true]);
        session()->flash('success', 'All results published. Parents can now view them.');
    }

    public function unpublishAll(): void
    {
        Result::where('term_id', $this->selectedTermId)
            ->whereIn('student_id', $this->getStudentIds())
            ->update(['is_published' => false]);
        session()->flash('success', 'Results unpublished.');
    }

    public function toggleStudentPublish(int $studentId): void
    {
        $results = Result::where('term_id', $this->selectedTermId)->where('student_id', $studentId)->get();
        $hasUnpublished = $results->where('is_published', false)->isNotEmpty();
        Result::where('term_id', $this->selectedTermId)->where('student_id', $studentId)
            ->update(['is_published' => $hasUnpublished]);
    }

    // ── Admin comment ─────────────────────────────────────────────────────────

    public function openComment(int $studentId): void
    {
        $this->commentStudentId = $studentId;
        // Load existing comment (from any result row for this student/term — same value on all)
        $existing = Result::where('term_id', $this->selectedTermId)
            ->where('student_id', $studentId)->value('admin_comment');
        $this->commentText     = $existing ?? '';
        $this->showCommentModal = true;
    }

    public function saveComment(): void
    {
        $this->validate(['commentText' => 'nullable|string|max:500']);

        Result::where('term_id', $this->selectedTermId)
            ->where('student_id', $this->commentStudentId)
            ->update(['admin_comment' => $this->commentText ?: null]);

        $this->showCommentModal = false;
        session()->flash('success', 'Comment saved.');
    }

    // ── Send report cards ─────────────────────────────────────────────────────

    public function sendReportCard(int $studentId): void
    {
        $student = Student::findOrFail($studentId);
        $term    = Term::findOrFail($this->selectedTermId);
        SendReportCardJob::dispatch($student, $term);
        session()->flash('success', "Report card queued for {$student->full_name}. Parents will receive it by email shortly.");
    }

    public function sendAllReportCards(): void
    {
        $term       = Term::findOrFail($this->selectedTermId);
        $studentIds = $this->getStudentIds();
        $count      = 0;

        foreach ($studentIds as $sid) {
            $student = Student::find($sid);
            if (! $student) continue;
            SendReportCardJob::dispatch($student, $term);
            $count++;
        }

        session()->flash('success', "{$count} report cards queued. Parents will receive them by email shortly.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function getStudentIds(): array
    {
        $term = Term::find($this->selectedTermId);
        if (! $term || ! $this->selectedClassId) return [];

        return Enrolment::where('school_class_id', $this->selectedClassId)
            ->where('academic_session_id', $term->academic_session_id)
            ->where('status', 'active')
            ->pluck('student_id')->toArray();
    }

    public function render()
    {
        $terms   = Term::with('session')->orderByDesc('academic_session_id')->orderBy('id')->get();
        $classes = SchoolClass::ordered()->get();
        $rows    = collect();

        if ($this->selectedTermId && $this->selectedClassId) {
            $term       = Term::find($this->selectedTermId);
            $studentIds = $this->getStudentIds();

            $results = Result::with('student', 'subject', 'submittedBy')
                ->where('term_id', $this->selectedTermId)
                ->whereIn('student_id', $studentIds)
                ->get()->groupBy('student_id');

            $enrolments = Enrolment::with('student')
                ->where('school_class_id', $this->selectedClassId)
                ->where('academic_session_id', $term->academic_session_id)
                ->where('status', 'active')->get()->sortBy('student.last_name');

            foreach ($enrolments as $enrolment) {
                $sid            = $enrolment->student_id;
                $studentResults = $results->get($sid, collect());
                $subjectCount   = $studentResults->count();
                $average        = $subjectCount > 0 ? round($studentResults->sum('total') / $subjectCount, 1) : null;
                $published      = $studentResults->isNotEmpty() && $studentResults->where('is_published', false)->isEmpty();
                $submitted      = $studentResults->isNotEmpty() && $studentResults->whereNotNull('submitted_at')->isNotEmpty();
                $submittedBy    = $studentResults->whereNotNull('submitted_at')->first()?->submittedBy?->name;
                $submittedAt    = $studentResults->whereNotNull('submitted_at')->first()?->submitted_at;
                $hasComment     = $studentResults->whereNotNull('admin_comment')->isNotEmpty();

                $rows->push([
                    'student'      => $enrolment->student,
                    'subject_count'=> $subjectCount,
                    'average'      => $average,
                    'published'    => $published,
                    'submitted'    => $submitted,
                    'submitted_by' => $submittedBy,
                    'submitted_at' => $submittedAt,
                    'has_results'  => $subjectCount > 0,
                    'has_comment'  => $hasComment,
                ]);
            }
        }

        return view('livewire.admin.results.results-overview', compact('terms', 'classes', 'rows'))
            ->layout('layouts.admin', ['title' => 'Results Overview']);
    }
}
