<?php

namespace App\Livewire\Admin\Fees;

use App\Models\FeeItem;
use Livewire\Component;

class FeeItemManager extends Component
{
    // Form fields
    public string $name        = '';
    public string $description = '';
    public string $type        = 'compulsory';
    public bool   $is_active   = true;

    // State
    public bool  $showForm  = false;
    public ?int  $editingId = null;
    public ?int  $deletingId = null;

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|min:2|max:100',
            'description' => 'nullable|string|max:300',
            'type'        => 'required|in:compulsory,optional',
            'is_active'   => 'boolean',
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm  = true;
        $this->editingId = null;
    }

    public function openEdit(int $id): void
    {
        $item = FeeItem::findOrFail($id);
        $this->editingId   = $id;
        $this->name        = $item->name;
        $this->description = $item->description ?? '';
        $this->type        = $item->type;
        $this->is_active   = $item->is_active;
        $this->showForm    = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            FeeItem::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Fee item updated.');
        } else {
            // New items go to the bottom of the list
            $maxOrder = FeeItem::max('sort_order') ?? 0;
            FeeItem::create(array_merge($data, ['sort_order' => $maxOrder + 1]));
            session()->flash('success', 'Fee item created.');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    // ─── Reordering ───────────────────────────────────────────────────────────

    public function moveUp(int $id): void
    {
        $item = FeeItem::findOrFail($id);

        // Find the item immediately above this one (next lower sort_order)
        $above = FeeItem::where('sort_order', '<', $item->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if (! $above) return; // Already at the top

        // Swap their sort_order values
        [$item->sort_order, $above->sort_order] = [$above->sort_order, $item->sort_order];
        $item->save();
        $above->save();
    }

    public function moveDown(int $id): void
    {
        $item = FeeItem::findOrFail($id);

        // Find the item immediately below this one (next higher sort_order)
        $below = FeeItem::where('sort_order', '>', $item->sort_order)
            ->orderBy('sort_order')
            ->first();

        if (! $below) return; // Already at the bottom

        // Swap their sort_order values
        [$item->sort_order, $below->sort_order] = [$below->sort_order, $item->sort_order];
        $item->save();
        $below->save();
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
    }

    public function delete(): void
    {
        if (! $this->deletingId) return;

        $item = FeeItem::findOrFail($this->deletingId);

        if ($item->feeStructures()->exists()) {
            session()->flash('error', "Cannot delete \"{$item->name}\" — it is used in a fee structure. Deactivate it instead.");
            $this->deletingId = null;
            return;
        }

        $item->delete();
        $this->deletingId = null;
        session()->flash('success', 'Fee item deleted.');
    }

    public function toggleActive(int $id): void
    {
        $item = FeeItem::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->name        = '';
        $this->description = '';
        $this->type        = 'compulsory';
        $this->is_active   = true;
        $this->editingId   = null;
    }

    public function render()
    {
        // Now ordered by sort_order, not name
        $items = FeeItem::orderBy('sort_order')->get();

        return view('livewire.admin.fees.fee-item-manager', compact('items'))
            ->layout('layouts.admin', ['title' => 'Fee Items']);
    }
}
