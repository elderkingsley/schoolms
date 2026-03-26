<?php

namespace App\Livewire\Admin\Fees;

use App\Models\AcademicSession;
use App\Models\FeeItem;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\Term;
use Livewire\Component;

class FeeStructureManager extends Component
{
    public ?int $selectedTermId = null;

    // amounts[fee_item_id][school_class_id] = amount string
    public array $amounts = [];

    public bool $saved = false;

    public function mount(): void
    {
        $activeTerm = Term::current();
        $this->selectedTermId = $activeTerm?->id;
        $this->loadAmounts();
    }

    public function updatedSelectedTermId(): void
    {
        $this->loadAmounts();
        $this->saved = false;
    }

    protected function loadAmounts(): void
    {
        $this->amounts = [];

        if (!$this->selectedTermId) return;

        $structures = FeeStructure::where('term_id', $this->selectedTermId)->get();

        foreach ($structures as $s) {
            if ($s->fee_item_id && $s->school_class_id) {
                $this->amounts[$s->fee_item_id][$s->school_class_id] =
                    number_format((float) $s->amount, 0, '.', '');
            }
        }
    }

    public function save(): void
    {
        if (!$this->selectedTermId) return;

        $term = Term::with('session')->findOrFail($this->selectedTermId);

        foreach ($this->amounts as $itemId => $classAmounts) {
            foreach ($classAmounts as $classId => $amount) {
                $numericAmount = (float) str_replace(',', '', $amount ?? '0');

                if ($numericAmount <= 0) {
                    // Remove if amount cleared
                    FeeStructure::where([
                        'fee_item_id'          => $itemId,
                        'school_class_id'      => $classId,
                        'term_id'              => $this->selectedTermId,
                        'academic_session_id'  => $term->academic_session_id,
                    ])->delete();
                    continue;
                }

                FeeStructure::updateOrCreate(
                    [
                        'fee_item_id'         => $itemId,
                        'school_class_id'     => $classId,
                        'term_id'             => $this->selectedTermId,
                        'academic_session_id' => $term->academic_session_id,
                    ],
                    ['amount' => $numericAmount]
                );
            }
        }

        $this->saved = true;
        session()->flash('success', 'Fee structure saved successfully.');
    }

    public function copyFromTerm(int $sourceTermId): void
    {
        if (!$this->selectedTermId || $sourceTermId === $this->selectedTermId) return;

        $sourceTerm = Term::with('session')->findOrFail($sourceTermId);
        $targetTerm = Term::with('session')->findOrFail($this->selectedTermId);

        $sourceStructures = FeeStructure::where('term_id', $sourceTermId)->get();

        foreach ($sourceStructures as $s) {
            FeeStructure::updateOrCreate(
                [
                    'fee_item_id'         => $s->fee_item_id,
                    'school_class_id'     => $s->school_class_id,
                    'term_id'             => $this->selectedTermId,
                    'academic_session_id' => $targetTerm->academic_session_id,
                ],
                ['amount' => $s->amount]
            );
        }

        $this->loadAmounts();
        session()->flash('success', 'Fee structure copied from previous term.');
    }

    public function render()
    {
        $sessions = AcademicSession::orderByDesc('id')->with('terms')->get();

        $terms = Term::with('session')
            ->orderByDesc('academic_session_id')
            ->orderBy('id')
            ->get();

        $compulsoryItems = FeeItem::active()->compulsory()->orderBy('name')->get();
        $optionalItems   = FeeItem::active()->optional()->orderBy('name')->get();
        $classes         = SchoolClass::orderBy('order')->get();

        // Previous term for "copy from" convenience
        $previousTerm = null;
        if ($this->selectedTermId) {
            $previousTerm = Term::where('id', '<', $this->selectedTermId)
                ->orderByDesc('id')->first();
        }

        return view('livewire.admin.fees.fee-structure-manager', compact(
            'sessions', 'terms', 'compulsoryItems', 'optionalItems',
            'classes', 'previousTerm'
        ))->layout('layouts.admin', ['title' => 'Fee Structure']);
    }
}
