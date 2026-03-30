<?php

namespace App\Livewire\Admin\Academics;

use App\Models\Subject;
use Livewire\Component;

class SubjectManager extends Component
{
    public string $name      = '';
    public string $code      = '';
    public bool   $is_active = true;

    public bool  $showForm  = false;
    public ?int  $editingId = null;
    public ?int  $deletingId = null;

    protected function rules(): array
    {
        $uniqueCode = $this->editingId
            ? 'unique:subjects,code,' . $this->editingId
            : 'unique:subjects,code';

        return [
            'name'      => 'required|string|min:2|max:100',
            'code'      => ['required', 'string', 'max:10', 'alpha_num', $uniqueCode],
            'is_active' => 'boolean',
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
        $subject = Subject::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $subject->name;
        $this->code      = $subject->code ?? '';
        $this->is_active = $subject->is_active;
        $this->showForm  = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            Subject::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Subject updated.');
        } else {
            $maxOrder = Subject::max('sort_order') ?? 0;
            Subject::create(array_merge($data, ['sort_order' => $maxOrder + 1]));
            session()->flash('success', 'Subject created.');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function moveUp(int $id): void
    {
        $subject = Subject::findOrFail($id);
        $above   = Subject::where('sort_order', '<', $subject->sort_order)
            ->orderByDesc('sort_order')->first();

        if (! $above) return;

        [$subject->sort_order, $above->sort_order] = [$above->sort_order, $subject->sort_order];
        $subject->save();
        $above->save();
    }

    public function moveDown(int $id): void
    {
        $subject = Subject::findOrFail($id);
        $below   = Subject::where('sort_order', '>', $subject->sort_order)
            ->orderBy('sort_order')->first();

        if (! $below) return;

        [$subject->sort_order, $below->sort_order] = [$below->sort_order, $subject->sort_order];
        $subject->save();
        $below->save();
    }

    public function toggleActive(int $id): void
    {
        $subject = Subject::findOrFail($id);
        $subject->update(['is_active' => ! $subject->is_active]);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
    }

    public function delete(): void
    {
        if (! $this->deletingId) return;

        $subject = Subject::findOrFail($this->deletingId);

        if ($subject->results()->exists()) {
            session()->flash('error', "Cannot delete \"{$subject->name}\" — it has results recorded. Deactivate it instead.");
            $this->deletingId = null;
            return;
        }

        // Also block if assigned to any class
        if ($subject->classes()->exists()) {
            session()->flash('error', "Cannot delete \"{$subject->name}\" — it is assigned to one or more classes. Remove the class assignment first.");
            $this->deletingId = null;
            return;
        }

        $subject->delete();
        $this->deletingId = null;
        session()->flash('success', 'Subject deleted.');
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->name      = '';
        $this->code      = '';
        $this->is_active = true;
        $this->editingId = null;
    }

    public function render()
    {
        $subjects = Subject::orderBy('sort_order')->orderBy('name')->get();

        return view('livewire.admin.academics.subject-manager', compact('subjects'))
            ->layout('layouts.admin', ['title' => 'Subjects']);
    }
}
