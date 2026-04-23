<?php
// Deploy to: app/Jobs/SendReportCardJob.php

namespace App\Jobs;

use App\Models\Enrolment;
use App\Models\Result;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentTermComment;
use App\Models\StudentTraitScore;
use App\Models\Term;
use App\Notifications\ReportCardNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendReportCardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 30;
    public int $timeout = 120;

    public function __construct(
        public Student $student,
        public Term    $term,
    ) {}

    public function handle(): void
    {
        $term    = $this->term->load('session');
        $student = $this->student->load('enrolments.schoolClass', 'parents.user');
        $termId  = $term->id;

        // Enrolment
        $enrolment = $student->enrolments
            ->where('academic_session_id', $term->academic_session_id)
            ->first();

        $isRemarkOnly = $enrolment?->schoolClass?->isRemarkOnly() ?? false;

        // Results
        $results = Result::with('subject')
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->where('is_published', true)
            ->get()
            ->sortBy('subject.sort_order');

        if ($results->isEmpty()) {
            Log::info("SendReportCard: no published results for student {$student->id} term {$termId}");
            return;
        }

        $subjectCount = $results->count();
        $average      = $subjectCount > 0 ? round($results->sum('total') / $subjectCount, 1) : 0;

        // Pass / Fail
        $passStatus = null;
        if (! $isRemarkOnly && $subjectCount > 0) {
            $passStatus = $average >= 40 ? 'PASS' : 'FAIL';
        }

        // Class lowest / highest
        $classLowest  = null;
        $classHighest = null;

        if (! $isRemarkOnly && $enrolment?->schoolClass) {
            $classStudentIds = Enrolment::where('school_class_id', $enrolment->school_class_id)
                ->where('academic_session_id', $term->academic_session_id)
                ->where('status', 'active')
                ->pluck('student_id');

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

        // Traits
        $traitScores    = StudentTraitScore::forStudentTerm($student->id, $termId);
        $psychomotorDef = StudentTraitScore::PSYCHOMOTOR;
        $affectiveDef   = $isRemarkOnly
            ? StudentTraitScore::AFFECTIVE_PRESCHOOL
            : StudentTraitScore::AFFECTIVE_PRIMARY;

        // Term comments
        $termComment = StudentTermComment::where('student_id', $student->id)
            ->where('term_id', $termId)
            ->first();

        // Student photo
        $photoBase64 = null;
        if ($student->photo && Storage::disk('public')->exists($student->photo)) {
            $raw         = Storage::disk('public')->get($student->photo);
            $mime        = Storage::disk('public')->mimeType($student->photo);
            $photoBase64 = "data:{$mime};base64," . base64_encode($raw);
        }

        // School settings
        $schoolName    = SchoolSetting::get('school_name', 'Nurtureville School');
        $schoolAddress = SchoolSetting::get('school_address', '112, Olaniyi Street, New Oko-Oba, Abule-Egba, Lagos');
        $logoBase64    = SchoolSetting::logoBase64();

        // Generate PDF — same template selection as ReportCardController
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

        // Base64-encode PDF bytes — raw binary cannot be JSON-serialized by the queue.
        // ReportCardNotification decodes it back to binary in toMail() before attaching.
        $pdfBase64 = base64_encode($pdf->output());

        $filename = 'ReportCard-'
            . str_replace(['/', '\\', ' '], '-', $student->admission_number)
            . '-' . str_replace(['/', '\\', ' '], '-', $term->name)
            . '-' . str_replace(['/', '\\', ' '], '-', $term->session->name)
            . '.pdf';

        // Send to every parent with a portal account
        foreach ($student->parents as $parent) {
            if (! $parent->user) continue;

            try {
                $parent->user->notify(
                    new ReportCardNotification($student, $term, $pdfBase64, $filename)
                );
                Log::info("SendReportCard: sent to parent {$parent->id} for student {$student->id}");
            } catch (\Throwable $e) {
                Log::error('SendReportCard: email failed', [
                    'student_id' => $student->id,
                    'parent_id'  => $parent->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }
    }
}
