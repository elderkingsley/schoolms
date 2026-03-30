<?php

namespace App\Livewire\Admin\Fees;

use App\Jobs\SendInvoiceJob;
use App\Models\Enrolment;
use App\Models\FeeInvoice;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Services\FeeService;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceList extends Component
{
    use WithPagination;

    // ── Filters / tabs ────────────────────────────────────────────────────────
    public ?int   $selectedTermId  = null;
    public string $search          = '';
    public string $filterStatus    = '';
    public string $tab             = 'all';   // 'all' | 'draft' | 'sent'

    // ── Bulk selection ────────────────────────────────────────────────────────
    public array  $selectedIds     = [];
    public bool   $selectAll       = false;

    // ── Bulk generate (all students) modal ────────────────────────────────────
    public bool   $showConfirmModal = false;
    public ?string $generationMessage = null;

    // ── Bulk send modal ───────────────────────────────────────────────────────
    public bool   $showSendModal   = false;
    public int    $sendBatchSize   = 10;

    // ── Bulk delete modal ─────────────────────────────────────────────────────
    public bool   $showDeleteModal = false;
    public int    $deletableCount  = 0;
    public int    $skippedCount    = 0;
    public string $deleteScope     = '';     // 'selected' | 'all'

    // ── Create invoice modal ──────────────────────────────────────────────────
    public bool   $showCreateModal = false;
    public string $createMode      = 'single';  // 'single' | 'class'

    // Single-student mode
    public string $studentSearch   = '';
    public array  $studentResults  = [];
    public ?int   $createStudentId = null;
    public string $createStudentName = '';

    // Shared: term + preview
    public ?int   $createTermId    = null;
    public mixed  $createPreview   = null;   // null | 'already_exists' | 'no_fee_structure' | array

    // Class mode
    public ?int   $createClassId   = null;
    public int    $createClassEligible = 0; // students who would get a new invoice

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->selectedTermId = Term::current()?->id;
        $this->createTermId   = $this->selectedTermId;
    }

    public function updatedSelectedTermId(): void
    {
        $this->resetPage();
        $this->generationMessage = null;
        $this->selectedIds = [];
        $this->selectAll   = false;
    }
    public function updatedTab(): void
    {
        $this->resetPage();
        $this->selectedIds = [];
        $this->selectAll   = false;
    }
    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }

    // ── Bulk select ───────────────────────────────────────────────────────────

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedIds = $value
            ? $this->buildQuery()->paginate(25)->pluck('id')->map(fn($id) => (string)$id)->toArray()
            : [];
    }

    public function toggleSelect(int $id): void
    {
        $sid = (string)$id;
        if (in_array($sid, $this->selectedIds)) {
            $this->selectedIds = array_values(array_filter($this->selectedIds, fn($i) => $i !== $sid));
        } else {
            $this->selectedIds[] = $sid;
        }
        $this->selectAll = false;
    }

    // ── Bulk generate ─────────────────────────────────────────────────────────

    public function confirmGenerate(): void { $this->showConfirmModal = true; }
    public function cancelGenerate(): void  { $this->showConfirmModal = false; }

    public function generateInvoices(FeeService $feeService): void
    {
        $this->showConfirmModal = false;
        if (! $this->selectedTermId) return;

        $term  = Term::findOrFail($this->selectedTermId);
        $count = $feeService->generateInvoicesForTerm($term);

        $this->generationMessage = $count > 0
            ? "✓ {$count} invoice(s) created as drafts. Review in the Drafts tab, then send."
            : "All active students already have invoices for this term.";

        $this->tab = 'draft';
        $this->resetPage();
    }

    // ── Send ──────────────────────────────────────────────────────────────────

    public function sendInvoice(int $invoiceId): void
    {
        $invoice = FeeInvoice::with('student')->findOrFail($invoiceId);
        SendInvoiceJob::dispatch($invoice);
        session()->flash('success', "Invoice for {$invoice->student->full_name} queued for delivery.");
    }

    public function sendSelected(): void
    {
        if (empty($this->selectedIds)) return;
        $count = 0;
        foreach ($this->selectedIds as $id) {
            $invoice = FeeInvoice::find((int)$id);
            if ($invoice && $invoice->isDraft()) {
                SendInvoiceJob::dispatch($invoice);
                $count++;
            }
        }
        $this->selectedIds = [];
        $this->selectAll   = false;
        session()->flash('success', "{$count} invoice(s) queued for delivery.");
    }

    public function openSendModal(): void { $this->showSendModal = true; }

    public function sendBatch(): void
    {
        $this->validate(['sendBatchSize' => 'required|integer|min:1|max:500']);

        $drafts = FeeInvoice::draft()
            ->when($this->selectedTermId, fn($q) => $q->where('term_id', $this->selectedTermId))
            ->limit($this->sendBatchSize)
            ->get();

        foreach ($drafts as $invoice) { SendInvoiceJob::dispatch($invoice); }

        $this->showSendModal = false;
        session()->flash('success', "{$drafts->count()} invoice(s) queued for delivery.");
    }

    public function sendAllDrafts(): void
    {
        $drafts = FeeInvoice::draft()
            ->when($this->selectedTermId, fn($q) => $q->where('term_id', $this->selectedTermId))
            ->get();

        foreach ($drafts as $invoice) { SendInvoiceJob::dispatch($invoice); }
        session()->flash('success', "{$drafts->count()} invoice(s) queued for delivery to all parents.");
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function deleteInvoice(int $invoiceId): void
    {
        $invoice = FeeInvoice::findOrFail($invoiceId);

        if ($invoice->status !== 'unpaid' || $invoice->payments()->exists()) {
            session()->flash('error', 'Only unpaid invoices with no recorded payments can be deleted.');
            return;
        }

        $name = $invoice->student->full_name;
        $invoice->delete();
        $this->selectedIds = array_values(array_filter($this->selectedIds, fn($id) => $id !== (string)$invoiceId));
        session()->flash('success', "Invoice for {$name} deleted.");
    }

    public function confirmDeleteSelected(): void
    {
        if (empty($this->selectedIds)) return;

        $invoices = FeeInvoice::whereIn('id', $this->selectedIds)->withCount('payments')->get();

        $this->deletableCount  = $invoices->filter(fn($i) => $i->status === 'unpaid' && $i->payments_count === 0)->count();
        $this->skippedCount    = $invoices->count() - $this->deletableCount;
        $this->deleteScope     = 'selected';
        $this->showDeleteModal = true;
    }

    public function confirmDeleteAll(): void
    {
        $all = $this->buildQuery()->where('status', 'unpaid')->withCount('payments')->get();

        $this->deletableCount  = $all->filter(fn($i) => $i->payments_count === 0)->count();
        $this->skippedCount    = $all->count() - $this->deletableCount;
        $this->deleteScope     = 'all';
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        $this->showDeleteModal = false;

        $invoices = $this->deleteScope === 'selected'
            ? FeeInvoice::whereIn('id', $this->selectedIds)->withCount('payments')->get()
            : $this->buildQuery()->where('status', 'unpaid')->withCount('payments')->get();

        $deleted = 0;
        $skipped = 0;

        foreach ($invoices as $invoice) {
            if ($invoice->status === 'unpaid' && $invoice->payments_count === 0) {
                $invoice->delete();
                $deleted++;
            } else {
                $skipped++;
            }
        }

        $this->selectedIds    = [];
        $this->selectAll      = false;
        $this->deletableCount = 0;
        $this->skippedCount   = 0;

        $msg = "{$deleted} invoice(s) deleted.";
        if ($skipped > 0) {
            $msg .= " {$skipped} skipped (paid or part-paid invoices cannot be deleted).";
        }

        session()->flash('success', $msg);
        $this->resetPage();
    }

    // ── Create invoice (manual) ───────────────────────────────────────────────

    public function openCreateModal(): void
    {
        $this->createMode          = 'single';
        $this->studentSearch       = '';
        $this->studentResults      = [];
        $this->createStudentId     = null;
        $this->createStudentName   = '';
        $this->createTermId        = $this->selectedTermId ?? Term::current()?->id;
        $this->createClassId       = null;
        $this->createPreview       = null;
        $this->createClassEligible = 0;
        $this->showCreateModal     = true;
        $this->resetValidation();
    }

    public function updatedCreateMode(): void
    {
        // Reset mode-specific state when switching tabs
        $this->createStudentId     = null;
        $this->createStudentName   = '';
        $this->studentSearch       = '';
        $this->studentResults      = [];
        $this->createClassId       = null;
        $this->createPreview       = null;
        $this->createClassEligible = 0;
    }

    // ── Student search (single mode) ──────────────────────────────────────────

    public function updatedStudentSearch(): void
    {
        $term = trim($this->studentSearch);

        if ($term === '') {
            $this->studentResults = [];
            return;
        }

        $this->studentResults = Student::where('status', 'active')
            ->where(function ($q) use ($term) {
                $q->where('first_name',       'like', "{$term}%")
                  ->orWhere('last_name',       'like', "{$term}%")
                  ->orWhere('first_name',      'like', "%{$term}%")
                  ->orWhere('last_name',       'like', "%{$term}%")
                  ->orWhere('admission_number','like', "%{$term}%");
            })
            ->with(['enrolments' => fn($q) =>
                $q->where('status', 'active')
                  ->with('schoolClass')
                  ->when(Term::find($this->createTermId)?->academic_session_id,
                      fn($q, $sid) => $q->where('academic_session_id', $sid)
                  )
            ])
            ->limit(8)
            ->get()
            ->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->full_name,
                'adm'  => $s->admission_number,
                'class'=> $s->enrolments->first()?->schoolClass?->display_name ?? '—',
            ])
            ->toArray();
    }

    public function selectStudent(int $studentId, string $studentName): void
    {
        $this->createStudentId   = $studentId;
        $this->createStudentName = $studentName;
        $this->studentSearch     = '';
        $this->studentResults    = [];
        $this->refreshSinglePreview();
    }

    public function clearStudent(): void
    {
        $this->createStudentId   = null;
        $this->createStudentName = '';
        $this->createPreview     = null;
    }

    // ── Term / class change handlers ──────────────────────────────────────────

    public function updatedCreateTermId(): void
    {
        $this->createPreview       = null;
        $this->createClassEligible = 0;

        if ($this->createMode === 'single' && $this->createStudentId) {
            $this->refreshSinglePreview();
        } elseif ($this->createMode === 'class' && $this->createClassId) {
            $this->refreshClassCount();
        }
    }

    public function updatedCreateClassId(): void
    {
        $this->createPreview       = null;
        $this->createClassEligible = 0;
        $this->refreshClassCount();
    }

    protected function refreshSinglePreview(): void
    {
        if (! $this->createStudentId || ! $this->createTermId) {
            $this->createPreview = null;
            return;
        }

        $student = Student::find($this->createStudentId);
        $term    = Term::find($this->createTermId);

        if (! $student || ! $term) { $this->createPreview = null; return; }

        $this->createPreview = app(FeeService::class)->previewInvoice($student, $term);
    }

    protected function refreshClassCount(): void
    {
        if (! $this->createClassId || ! $this->createTermId) {
            $this->createClassEligible = 0;
            return;
        }

        $term = Term::find($this->createTermId);
        if (! $term) { $this->createClassEligible = 0; return; }

        // Count active students in this class for this session who don't yet have an invoice
        $enrolments = Enrolment::where('school_class_id', $this->createClassId)
            ->where('academic_session_id', $term->academic_session_id)
            ->where('status', 'active')
            ->pluck('student_id');

        $existingIds = FeeInvoice::where('term_id', $this->createTermId)
            ->whereIn('student_id', $enrolments)
            ->pluck('student_id');

        $this->createClassEligible = $enrolments->diff($existingIds)->count();
    }

    // ── Submit: create the invoice(s) ─────────────────────────────────────────

    public function createInvoices(FeeService $feeService): void
    {
        $this->validate(['createTermId' => 'required|exists:terms,id']);

        $term    = Term::findOrFail($this->createTermId);
        $created = 0;
        $skipped = 0;

        if ($this->createMode === 'single') {
            $this->validate(['createStudentId' => 'required|exists:students,id']);

            $student = Student::findOrFail($this->createStudentId);
            $invoice = $feeService->generateInvoiceForStudent($student, $term);

            if ($invoice) {
                $created = 1;
            } else {
                $skipped = 1;
            }
        } else {
            // Class mode — generate for all eligible students
            $this->validate(['createClassId' => 'required|exists:school_classes,id']);

            $enrolments = Enrolment::with('student')
                ->where('school_class_id', $this->createClassId)
                ->where('academic_session_id', $term->academic_session_id)
                ->where('status', 'active')
                ->get();

            foreach ($enrolments as $enrolment) {
                $invoice = $feeService->generateInvoiceForStudent($enrolment->student, $term);
                $invoice ? $created++ : $skipped++;
            }
        }

        $this->showCreateModal = false;

        $msg = "{$created} invoice(s) created as drafts.";
        if ($skipped > 0) {
            $msg .= " {$skipped} skipped (invoice already exists for this term).";
        }
        if ($created > 0) {
            $msg .= " Review and send from the Drafts tab.";
            $this->tab = 'draft';
        }

        session()->flash($created > 0 ? 'success' : 'error', $msg);
        $this->resetPage();
    }

    // ── Query / render ────────────────────────────────────────────────────────

    protected function buildQuery()
    {
        return FeeInvoice::with('student', 'term.session')
            ->when($this->selectedTermId, fn($q) => $q->where('term_id', $this->selectedTermId))
            ->when($this->filterStatus,   fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->tab === 'draft', fn($q) => $q->draft())
            ->when($this->tab === 'sent',  fn($q) => $q->sent())
            ->when($this->search, fn($q) =>
                $q->whereHas('student', fn($sq) =>
                    $sq->where('first_name', 'like', "%{$this->search}%")
                       ->orWhere('last_name',  'like', "%{$this->search}%")
                       ->orWhere('admission_number', 'like', "%{$this->search}%")
                )
            )
            ->orderByDesc('created_at');
    }

    public function render()
    {
        $terms    = Term::with('session')->orderByDesc('academic_session_id')->orderBy('id')->get();
        $classes  = SchoolClass::ordered()->get();
        $invoices = $this->buildQuery()->paginate(25);

        $base = FeeInvoice::when($this->selectedTermId, fn($q) => $q->where('term_id', $this->selectedTermId));

        $stats = [
            'total'       => (clone $base)->count(),
            'draft'       => (clone $base)->draft()->count(),
            'sent'        => (clone $base)->sent()->count(),
            'paid'        => (clone $base)->where('status', 'paid')->count(),
            'revenue'     => (clone $base)->sum('amount_paid'),
            'outstanding' => (clone $base)->sum('balance'),
        ];

        return view('livewire.admin.fees.invoice-list', compact('terms', 'classes', 'invoices', 'stats'))
            ->layout('layouts.admin', ['title' => 'Fee Invoices']);
    }
}
