<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\Student;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportCardController extends Controller
{
    public function __invoke(Student $student)
    {
        $termId = request()->query('term', Term::current()?->id);

        abort_if(! $termId, 404, 'Term not specified.');

        $term = Term::with('session')->findOrFail($termId);

        $results = Result::with('subject')
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->get()
            ->sortBy('subject.sort_order');

        // Get the student's class for this term
        $enrolment = $student->enrolments()
            ->with('schoolClass')
            ->where('academic_session_id', $term->academic_session_id)
            ->first();

        $subjectCount = $results->count();
        $totalScore   = $results->sum('total');
        $average      = $subjectCount > 0 ? round($totalScore / $subjectCount, 1) : 0;

        $pdf = Pdf::loadView('pdf.report-card', compact(
            'student', 'term', 'results', 'enrolment', 'average', 'subjectCount'
        ))->setPaper('a4', 'portrait');

        $filename = 'ReportCard-'
            . $student->admission_number
            . '-' . str_replace(' ', '', $term->name)
            . '-' . str_replace(['/', '\\', ' '], '-', $term->session->name)
            . '.pdf';

        return $pdf->stream($filename);
    }
}
