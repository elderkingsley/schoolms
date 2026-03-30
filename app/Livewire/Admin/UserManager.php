<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Notifications\UserWelcomeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    // Filters
    public string $search     = '';
    public string $filterType = '';

    // Form state
    public bool    $showForm  = false;
    public ?int    $editingId = null;
    public ?int    $deletingId = null;
    public ?int    $deactivatingId = null;

    // Form fields
    public string $name     = '';
    public string $email    = '';
    public string $phone    = '';
    public string $userType = 'teacher';

    protected function rules(): array
    {
        $uniqueEmail = $this->editingId
            ? 'unique:users,email,' . $this->editingId
            : 'unique:users,email';

        return [
            'name'     => 'required|string|min:2|max:100',
            'email'    => ['required', 'email', $uniqueEmail],
            'phone'    => 'nullable|string|max:20',
            'userType' => 'required|in:admin,teacher,accountant',
            // Note: 'parent' is excluded — parents are created via enrolment approval
            // Note: 'super_admin' is excluded — only one super admin, set via seeder
        ];
    }

    // ── Filters ───────────────────────────────────────────────────────────────

    public function updatedSearch(): void  { $this->resetPage(); }
    public function updatedFilterType(): void { $this->resetPage(); }

    // ── Create / Edit ─────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm  = true;
        $this->editingId = null;
    }

    public function openEdit(int $id): void
    {
        // Prevent editing super_admin accounts
        $user = User::findOrFail($id);
        if ($user->isSuperAdmin()) return;

        $this->editingId = $id;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->phone     = $user->phone ?? '';
        $this->userType  = $user->user_type;
        $this->showForm  = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->update([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?: null,
                'user_type' => $data['userType'],
            ]);

            // If user type changed, sync role
            $user->syncRoles([$data['userType']]);

            session()->flash('success', "{$user->name}'s account updated.");
        } else {
            // Generate a secure temporary password
            $tempPassword = Str::upper(Str::random(4))
                . rand(10, 99)
                . Str::lower(Str::random(4));

            $user = User::create([
                'name'                  => $data['name'],
                'email'                 => $data['email'],
                'phone'                 => $data['phone'] ?: null,
                'user_type'             => $data['userType'],
                'password'              => Hash::make($tempPassword),
                'is_active'             => true,
                'force_password_change' => true, // must change on first login
            ]);

            $user->assignRole($data['userType']);

            // Email them their credentials
            $user->notify(new UserWelcomeNotification($user, $tempPassword));

            session()->flash('success', "{$user->name}'s account created. Login credentials sent to {$user->email}.");
        }

        $this->showForm = false;
        $this->resetForm();
    }

    // ── Deactivate / Reactivate ───────────────────────────────────────────────

    public function confirmDeactivate(int $id): void
    {
        $user = User::findOrFail($id);
        if ($user->isSuperAdmin()) return; // cannot deactivate super admin
        $this->deactivatingId = $id;
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);
        if ($user->isSuperAdmin()) return;

        $user->update(['is_active' => ! $user->is_active]);
        $this->deactivatingId = null;

        $status = $user->is_active ? 'reactivated' : 'deactivated';
        session()->flash('success', "{$user->name}'s account has been {$status}.");
    }

    // ── Reset Password ────────────────────────────────────────────────────────

    public function resetPassword(int $id): void
    {
        $user = User::findOrFail($id);
        if ($user->isSuperAdmin()) return;

        $tempPassword = Str::upper(Str::random(4))
            . rand(10, 99)
            . Str::lower(Str::random(4));

        $user->update([
            'password'              => Hash::make($tempPassword),
            'force_password_change' => true,
        ]);

        $user->notify(new UserWelcomeNotification($user, $tempPassword));

        session()->flash('success', "Password reset for {$user->name}. New credentials sent to {$user->email}.");
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function confirmDelete(int $id): void
    {
        $user = User::findOrFail($id);
        if ($user->isSuperAdmin()) return;
        $this->deletingId = $id;
    }

    public function delete(): void
    {
        if (! $this->deletingId) return;

        $user = User::findOrFail($this->deletingId);
        if ($user->isSuperAdmin()) return;

        // Block deletion of parents who have children linked
        if ($user->isParent() && $user->parentProfile?->students()->exists()) {
            session()->flash('error', "Cannot delete {$user->name} — they have students linked. Deactivate instead.");
            $this->deletingId = null;
            return;
        }

        $name = $user->name;
        $user->delete();
        $this->deletingId = null;
        session()->flash('success', "{$name}'s account has been permanently deleted.");
    }

    protected function resetForm(): void
    {
        $this->name     = '';
        $this->email    = '';
        $this->phone    = '';
        $this->userType = 'teacher';
        $this->editingId = null;
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        // Super admin only page — gate here as defence-in-depth
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $users = User::with('roles')
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->when($this->filterType, fn($q) => $q->where('user_type', $this->filterType))
            ->orderByRaw("FIELD(user_type, 'super_admin', 'admin', 'accountant', 'teacher', 'parent')")
            ->orderBy('name')
            ->paginate(25);

        return view('livewire.admin.user-manager', compact('users'))
            ->layout('layouts.admin', ['title' => 'Users']);
    }
}
