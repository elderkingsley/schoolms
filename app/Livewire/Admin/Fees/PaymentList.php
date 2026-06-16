<?php

namespace App\Livewire\Admin\Fees;

use App\Models\FeePayment;
use App\Models\Term;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterMethod = '';

    public string $filterTerm = '';

    public string $filterPaymentStatus = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterMethod(): void
    {
        $this->resetPage();
    }

    public function updatedFilterTerm(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPaymentStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterMethod', 'filterTerm', 'filterPaymentStatus', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        $terms = Term::with('session')->orderByDesc('id')->get();

        $methods = FeePayment::query()
            ->select('method')
            ->distinct()
            ->orderBy('method')
            ->pluck('method')
            ->filter()
            ->values();

        $baseQuery = FeePayment::query()
            ->with(['invoice.student', 'invoice.term.session'])
            ->when($this->search, function ($query) {
                $search = trim($this->search);

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('receipt_number', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhereHas('invoice.student', function ($studentQuery) use ($search) {
                            $studentQuery
                                ->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('admission_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when($this->filterMethod, fn ($query) => $query->where('method', $this->filterMethod))
            ->when($this->filterTerm, fn ($query) => $query->whereHas('invoice', fn ($invoiceQuery) => $invoiceQuery->where('term_id', $this->filterTerm)))
            ->when($this->filterPaymentStatus, fn ($query) => $query->whereHas('invoice', fn ($invoiceQuery) => $invoiceQuery->where('status', $this->filterPaymentStatus)))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('paid_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('paid_at', '<=', $this->dateTo));

        $summary = (clone $baseQuery)
            ->selectRaw('COUNT(*) as payments_count, COALESCE(SUM(amount), 0) as total_amount')
            ->first();

        $payments = $baseQuery
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->paginate(25);

        return view('livewire.admin.fees.payment-list', compact('payments', 'terms', 'methods', 'summary'))
            ->layout('layouts.admin', ['title' => 'Payments']);
    }
}
