<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\ParentGuardian;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    public function __invoke(FeeInvoice $invoice)
    {
        // Authorise: confirm this invoice belongs to one of this parent's children.
        $user = auth()->user();

        $studentIds = ParentGuardian::where('user_id', $user->id)
            ->get()
            ->flatMap(fn($p) => $p->students()->pluck('students.id'))
            ->unique();

        if (! $studentIds->contains($invoice->student_id)) {
            abort(403, 'You do not have permission to view this invoice.');
        }

        $invoice->load([
            'student.parents.user',
            'student.enrolments.schoolClass',
            'term.session',
            'items.feeItem',
            'payments',
        ]);

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'))
            ->setPaper('a4', 'portrait');

        $filename = 'Invoice-' . str_replace(['/', '\\', ' '], '-', $invoice->student->admission_number)
            . '-' . str_replace(['/', '\\', ' '], '-', $invoice->term->name)
            . '.pdf';

        return $pdf->stream($filename);
    }
}
