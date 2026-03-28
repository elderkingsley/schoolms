<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    public function __invoke(FeeInvoice $invoice)
    {
        $invoice->load([
            'student.parents.user',
            'student.enrolments.schoolClass',
            'term.session',
            'items.feeItem',
            'payments',
        ]);

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'))
            ->setPaper('a4', 'portrait');

        $filename = 'Invoice-' . $invoice->student->admission_number
            . '-' . str_replace(' ', '', $invoice->term->name)
            . '.pdf';

        return $pdf->stream($filename);
    }
}
