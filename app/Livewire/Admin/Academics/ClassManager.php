<?php
// Deploy to: app/Livewire/Admin/Academics/ClassManager.php

namespace App\Livewire\Admin\Academics;

use App\Models\SchoolClass;
use App\Models\User;
use Livewire\Component;

class ClassManager extends Component
{
    public string $name           = '';
    public string $level          = '';
    public string $arm            = '';
    public ?int   $formTeacherId  = null;
    public string $resultType     = 'scored';

    public bool  $showForm   = false;
    public ?int  $editingId  = null;
    public ?int  $deletingId = null;

    protected function rules(): array
    {
        return [
            'name'          => 'required|string|min:2|max:100',
            'level'         => 'required|string|min:2|max:100',
            'arm'           => 'nullable|string|max:50',
            'formTeacherId' => 'nullable|exists:users,id',
            'resultType'    => 'required|in:scored,remark_only',
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
        $class = SchoolClass::findOrFail($id);
        $this->editingId      = $id;
        $this->name           = $class->name;
        $this->level          = $class->level;
        $this->arm            = $class->arm ?? '';
        $this->formTeacherId  = $class->form_teacher_id;
        $this->resultType     = $class->result_type ?? 'scored';
        $this->showForm       = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $data['arm']             = $data['arm'] ?: null;
        $data['form_teacher_id'] = $data['formTeacherId'];
        $data['result_type']     = $data['resultType'];
        unset($data['formTeacherId'], $data['resultType']);

        if ($this->editingId) {
            SchoolClass::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Class updated.');
        } else {
            $maxOrder = SchoolClass::max('order') ?? 0;
            $data['order'] = $maxOrder + 1;
            SchoolClass::create($data);
            session()->flash('success', 'Class created.');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function moveUp(int $id): void
    {
        $class = SchoolClass::findOrFail($id);
        $above = SchoolClass::where('order', '<', $class->order)
            ->orderByDesc('order')->first();
        if (! $above) return;
        [$class->order, $above->order] = [$above->order, $class->order];
        $class->save(); $above->save();
    }

    public function moveDown(int $id): void
    {
        $class = SchoolClass::findOrFail($id);
        $below = SchoolClass::where('order', '>', $class->order)
            ->orderBy('order')->first();
        if (! $below) return;
        [$class->order, $below->order] = [$below->order, $class->order];
        $class->save(); $below->save();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
    }

    public function delete(): void
    {
        if (! $this->deletingId) return;

        $class = SchoolClass::findOrFail($this->deletingId);

        if ($class->enrolments()->exists()) {
            session()->flash('error', "Cannot delete \"{$class->display_name}\" — students are enrolled in it.");
            $this->deletingId = null;
            return;
        }

        $class->delete();
        $this->deletingId = null;
        session()->flash('success', 'Class deleted.');
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->name          = '';
        $this->level         = '';
        $this->arm           = '';
        $this->formTeacherId = null;
        $this->resultType    = 'scored';
        $this->editingId     = null;
    }

    public function render()
    {
        $classes  = SchoolClass::with('formTeacher')->ordered()->get();
        $teachers = User::where('user_type', 'teacher')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.academics.class-manager', compact('classes', 'teachers'))
            ->layout('layouts.admin', ['title' => 'Classes']);
    }
}
