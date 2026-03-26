<?php

namespace App\Livewire\Admin\Fees;

use App\Models\FeeItem;
use Livewire\Component;
use Livewire\WithPagination;

class FeeItemManager extends Component
{
    use WithPagination;

    // Form fields
    public string  $name        = '';
    public string  $description = '';
    public string  $type        = 'compulsory';
    public bool    $is_active   = true;

    // State
    public bool    $showForm    = false;
    public ?int    $editingId   = null;
    public ?int    $deletingId  = null;

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
        $this->showForm = true;
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
            FeeItem::create($data);
            session()->flash('success', 'Fee item created.');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
    }

    public function delete(): void
    {
        if (!$this->deletingId) return;

        $item = FeeItem::findOrFail($this->deletingId);

        // Check if used in any fee structure
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
        $item->update(['is_active' => !$item->is_active]);
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
        $items = FeeItem::orderBy('type')->orderBy('name')->paginate(20);

        return view('livewire.admin.fees.fee-item-manager', compact('items'))
            ->layout('layouts.admin', ['title' => 'Fee Items']);
    }
}
