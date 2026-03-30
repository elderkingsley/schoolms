<?php

namespace App\Jobs;

use App\Models\Result;
use App\Models\Student;
use App\Models\Term;
use App\Notifications\ReportCardNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendReportCardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 30;

    public function __construct(
        public Student $student,
        public Term    $term,
    ) {}

    public function handle(): void
    {
        $term    = $this->term->load('session');
        $student = $this->student->load('enrolments.schoolClass', 'parents.user');

        $results = Result::with('subject')
            ->where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->where('is_published', true)
            ->get()
            ->sortBy('subject.sort_order');

        if ($results->isEmpty()) {
            Log::info("SendReportCard: no published results for student {$student->id} term {$term->id}");
            return;
        }

        $enrolment    = $student->enrolments
            ->where('academic_session_id', $term->academic_session_id)
            ->first();
        $subjectCount = $results->count();
        $average      = $subjectCount > 0 ? round($results->sum('total') / $subjectCount, 1) : 0;

        // Generate PDF in memory — do not write to disk
        $pdf = Pdf::loadView('pdf.report-card', compact(
            'student', 'term', 'results', 'enrolment', 'average', 'subjectCount'
        ))->setPaper('a4', 'portrait');

        $pdfContent = $pdf->output();

        $filename = 'ReportCard-' . $student->admission_number
            . '-' . str_replace(' ', '', $term->name)
            . '.pdf';

        // Send to every parent with a portal account
        foreach ($student->parents as $parent) {
            if (! $parent->user) continue;

            try {
                $parent->user->notify(
                    new ReportCardNotification($student, $term, $pdfContent, $filename)
                );
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
