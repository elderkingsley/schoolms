<?php

namespace App\Livewire\Accountant;

use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\Term;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $activeTerm = Term::current();

        $stats = [
            'total_invoiced'    => 0,
            'total_collected'   => 0,
            'total_outstanding' => 0,
            'invoices_count'    => 0,
            'paid_count'        => 0,
            'unpaid_count'      => 0,
            'partial_count'     => 0,
        ];

        if ($activeTerm) {
            $stats['total_invoiced']    = FeeInvoice::where('term_id', $activeTerm->id)->sum('total_amount');
            $stats['total_collected']   = FeeInvoice::where('term_id', $activeTerm->id)->sum('amount_paid');
            $stats['total_outstanding'] = FeeInvoice::where('term_id', $activeTerm->id)->sum('balance');
            $stats['invoices_count']    = FeeInvoice::where('term_id', $activeTerm->id)->count();
            $stats['paid_count']        = FeeInvoice::where('term_id', $activeTerm->id)->where('status', 'paid')->count();
            $stats['partial_count']     = FeeInvoice::where('term_id', $activeTerm->id)->where('status', 'partial')->count();
            $stats['unpaid_count']      = FeeInvoice::where('term_id', $activeTerm->id)->where('status', 'unpaid')->count();
        }

        // Recent payments
        $recentPayments = FeePayment::with('invoice.student')
            ->latest('paid_at')
            ->limit(8)
            ->get();

        return view('livewire.accountant.dashboard', compact('stats', 'activeTerm', 'recentPayments'))
            ->layout('layouts.accountant', ['title' => 'Finance Dashboard']);
    }
}
