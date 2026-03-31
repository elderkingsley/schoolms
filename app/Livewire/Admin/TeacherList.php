<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Notifications\UserWelcomeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class TeacherList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void { $this->resetPage(); }

    public function resetPassword(int $userId): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $user = User::findOrFail($userId);
        abort_if(! $user->isTeacher(), 403);

        $tempPassword = Str::upper(Str::random(4)) . rand(10, 99) . Str::lower(Str::random(4));

        $user->update([
            'password'              => Hash::make($tempPassword),
            'force_password_change' => true,
        ]);

        $user->notify(new UserWelcomeNotification($user, $tempPassword));
        session()->flash('success', "Password reset for {$user->name}. New credentials sent to {$user->email}.");
    }

    public function toggleActive(int $userId): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $user = User::findOrFail($userId);
        $user->update(['is_active' => ! $user->is_active]);
        $status = $user->is_active ? 'reactivated' : 'deactivated';
        session()->flash('success', "{$user->name}'s account has been {$status}.");
    }

    public function render()
    {
        $teachers = User::with([
                'roles',
                'formClasses.schoolClass',
            ])
            ->where('user_type', 'teacher')
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
            )
            ->orderBy('name')
            ->paginate(25);

        return view('livewire.admin.teacher-list', compact('teachers'))
            ->layout('layouts.admin', ['title' => 'Teachers']);
    }
}
