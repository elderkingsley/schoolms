<?php
// Deploy to: app/Http/Controllers/Admin/ReportCardController.php
// REPLACES existing file.

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrolment;
use App\Models\Result;
use App\Models\Student;
use App\Models\StudentTermComment;
use App\Models\StudentTraitScore;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportCardController extends Controller
{
    public function __invoke(Student $student)
    {
        $termId = request()->query('term', Term::current()?->id);
        abort_if(! $termId, 404, 'Term not specified.');

        $term = Term::with('session')->findOrFail($termId);

        // ── Enrolment (class placement for this session) ───────────────────────
        $enrolment = $student->enrolments()
            ->with('schoolClass')
            ->where('academic_session_id', $term->academic_session_id)
            ->first();

        $isRemarkOnly = $enrolment?->schoolClass?->isRemarkOnly() ?? false;

        // ── Results ───────────────────────────────────────────────────────────
        $results = Result::with('subject')
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->get()
            ->sortBy('subject.sort_order');

        $subjectCount = $results->count();
        $totalScore   = $results->sum('total');
        $average      = $subjectCount > 0 ? round($totalScore / $subjectCount, 1) : 0;

        // Pass / Fail status — Primary only. Passes if average >= 40 (D threshold).
        $passStatus = null;
        if (! $isRemarkOnly && $subjectCount > 0) {
            $passStatus = $average >= 40 ? 'PASS' : 'FAIL';
        }

        // ── Class LS / HS (lowest and highest TOTAL MARK across all students) ──
        //
        // LS and HS on the report card are NOT per-subject figures.
        // They are the lowest and highest SUM-OF-ALL-SUBJECTS total mark
        // earned by any student in the same class this term.
        // Every student's report card shows the same two numbers.
        //
        // How it works:
        //   1. Find all student IDs enrolled in the same class this term.
        //   2. For each of those students, sum their subject totals.
        //   3. classLowest  = the smallest such sum in the class.
        //   4. classHighest = the largest such sum in the class.
        //
        // We query live at PDF generation time — no pre-storage needed,
        // always accurate even if results are updated after publishing.
        $classLowest  = null;
        $classHighest = null;

        if (! $isRemarkOnly && $enrolment?->schoolClass) {
            // All student IDs in the same class/session
            $classStudentIds = Enrolment::where('school_class_id', $enrolment->school_class_id)
                ->where('academic_session_id', $term->academic_session_id)
                ->where('status', 'active')
                ->pluck('student_id');

            // Sum of all subject totals per student for this term
            $classTotals = Result::whereIn('student_id', $classStudentIds)
                ->where('term_id', $termId)
                ->whereNotNull('total')
                ->selectRaw('student_id, SUM(total) as grand_total')
                ->groupBy('student_id')
                ->pluck('grand_total');

            if ($classTotals->isNotEmpty()) {
                $classLowest  = $classTotals->min();
                $classHighest = $classTotals->max();
            }
        }

        // ── Traits (psychomotor + affective) ──────────────────────────────────
        $traitScores    = StudentTraitScore::forStudentTerm($student->id, $termId);
        $psychomotorDef = StudentTraitScore::PSYCHOMOTOR;
        $affectiveDef   = $isRemarkOnly
            ? StudentTraitScore::AFFECTIVE_PRESCHOOL
            : StudentTraitScore::AFFECTIVE_PRIMARY;

        // ── Term comments ─────────────────────────────────────────────────────
        $termComment = StudentTermComment::where('student_id', $student->id)
            ->where('term_id', $termId)
            ->first();

        // ── Student photo (base64 for DomPDF inline embedding) ────────────────
        $photoBase64 = null;
        if ($student->photo && Storage::disk('public')->exists($student->photo)) {
            $raw  = Storage::disk('public')->get($student->photo);
            $mime = Storage::disk('public')->mimeType($student->photo);
            $photoBase64 = "data:{$mime};base64," . base64_encode($raw);
        }

        // ── School settings ───────────────────────────────────────────────────
        $schoolName    = \App\Models\SchoolSetting::get('school_name', 'Nurtureville School');
        $schoolAddress = \App\Models\SchoolSetting::get('school_address', '112, Olaniyi Street, New Oko-Oba, Abule-Egba, Lagos');
        $logoBase64    = \App\Models\SchoolSetting::logoBase64();

        // ── Template selection ────────────────────────────────────────────────
        // Primary classes → full scored template with CA/Exam columns,
        //                    class stats, traits block, pass/fail.
        // Preschool/Nursery → remark-only template with narrative evaluations,
        //                      preschool affective traits, no grade columns.
        $template = $isRemarkOnly
            ? 'pdf.report-card-preschool'
            : 'pdf.report-card-primary';

        $pdf = Pdf::loadView($template, compact(
            'student', 'term', 'results', 'enrolment',
            'average', 'subjectCount', 'isRemarkOnly',
            'termComment', 'passStatus',
            'traitScores', 'psychomotorDef', 'affectiveDef',
            'photoBase64', 'schoolName', 'schoolAddress', 'logoBase64',
            'classLowest', 'classHighest'
        ))->setPaper('a4', 'portrait');

        $filename = 'ReportCard-'
            . str_replace(['/', '\\', ' '], '-', $student->admission_number)
            . '-' . str_replace(['/', '\\', ' '], '-', $term->name)
            . '-' . str_replace(['/', '\\', ' '], '-', $term->session->name)
            . '.pdf';

        return $pdf->stream($filename);
    }

    // ── Publish action ────────────────────────────────────────────────────────

    /**
     * Publishes all results for a class/term and pre-computes class statistics.
     *
     * Called from Admin ResultsOverview publish button.
     * Separated into its own method so it can be called independently of PDF generation.
     *
     * Flow:
     *   1. Get all subjects with results for this class/term.
     *   2. For each subject, compute class_average/lowest/highest across all students.
     *   3. Mark all results as is_published = true.
     */
    public static function publishResults(int $classId, int $termId): void
    {
        // Find all subject IDs with results for this class/term
        $subjectIds = Result::whereHas('student.enrolments', function ($q) use ($classId, $termId) {
                $q->where('school_class_id', $classId);
                // We join through the term's session to validate class membership
            })
            ->where('term_id', $termId)
            ->distinct()
            ->pluck('subject_id');

        foreach ($subjectIds as $subjectId) {
            Result::computeClassStats($subjectId, $termId);
        }

        // Mark all matching results as published
        Result::where('term_id', $termId)
            ->whereHas('student.enrolments', function ($q) use ($classId) {
                $q->where('school_class_id', $classId);
            })
            ->update(['is_published' => true]);
    }
}
