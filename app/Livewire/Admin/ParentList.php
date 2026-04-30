<?php
// Deploy to: /var/www/schoolms/app/Livewire/Admin/ParentList.php

namespace App\Livewire\Admin;

use App\Models\ParentGuardian;
use App\Models\User;
use App\Notifications\UserWelcomeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ParentList extends Component
{
    use WithPagination;

    public string $search          = '';
    public string $filterWallet    = '';

    // ── Edit modal ────────────────────────────────────────────────────────────
    public bool   $showEditModal   = false;
    public ?int   $editParentId    = null;
    public ?int   $editUserId      = null;
    public string $editName        = '';
    public string $editEmail       = '';
    public string $editPhone       = '';

    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedFilterWallet(): void { $this->resetPage(); }

    // ── Edit modal ────────────────────────────────────────────────────────────

    public function openEdit(int $parentId): void
    {
        abort_if(! auth()->user()->isAdmin(), 403);

        $parent = ParentGuardian::with('user')->findOrFail($parentId);

        $this->editParentId  = $parent->id;
        $this->editUserId    = $parent->user?->id;
        $this->editName      = $parent->user?->name ?? '';
        $this->editEmail     = $parent->user?->email ?? '';
        $this->editPhone     = $parent->phone ?? $parent->user?->phone ?? '';
        $this->showEditModal = true;
    }

    public function closeEdit(): void
    {
        $this->showEditModal = false;
        $this->reset(['editParentId', 'editUserId', 'editName', 'editEmail', 'editPhone']);
    }

    public function saveEdit(): void
    {
        abort_if(! auth()->user()->isAdmin(), 403);

        $this->validate([
            'editName'  => 'required|string|max:255',
            'editEmail' => 'required|email|max:255|unique:users,email,' . $this->editUserId,
            'editPhone' => 'nullable|string|max:20',
        ]);

        $parent = ParentGuardian::findOrFail($this->editParentId);
        $name   = trim($this->editName);

        // Update User record (name + email)
        if ($this->editUserId) {
            User::where('id', $this->editUserId)->update([
                'name'  => $name,
                'email' => trim($this->editEmail),
            ]);
        }

        // Update phone on the ParentGuardian record
        $parent->update([
            'phone' => trim($this->editPhone) ?: null,
        ]);

        $this->closeEdit();
        session()->flash('success', "Parent details updated for {$name}.");
    }

    // ── Existing actions ──────────────────────────────────────────────────────

    public function resetPassword(int $userId): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $user = User::findOrFail($userId);
        abort_if(! $user->isParent(), 403);

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

    public function retryProvisioning(int $parentId): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $parent = ParentGuardian::findOrFail($parentId);

        if (! empty($parent->budpay_account_number) && $parent->budpay_wallet_status !== 'active') {
            $parent->update(['budpay_wallet_status' => 'active']);
            session()->flash('success', "Status corrected for parent #{$parentId} — NUBAN was already provisioned.");
            return;
        }

        \App\Jobs\ProvisionParentWalletJob::dispatch($parent);
        $parent->update(['budpay_wallet_status' => 'pending']);
        session()->flash('success', "Provisioning job queued for parent #{$parentId}.");
    }

    public function render()
    {
        $query = ParentGuardian::with([
                'user',
                'students.enrolments.schoolClass',
            ])
            ->whereNotNull('user_id')
            ->when($this->search, fn($q) =>
                $q->whereHas('user', fn($u) =>
                    $u->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('phone', 'like', "%{$this->search}%")
                )
                ->orWhere('phone', 'like', "%{$this->search}%")
            )
            ->when($this->filterWallet === 'active', fn($q) =>
                $q->where(fn($w) =>
                    $w->where('korapay_wallet_status', 'active')
                      ->orWhere(fn($b) =>
                          $b->whereNull('korapay_wallet_status')
                            ->where('budpay_wallet_status', 'active')
                      )
                      ->orWhere(fn($j) =>
                          $j->whereNull('korapay_wallet_status')
                            ->whereNull('budpay_wallet_status')
                            ->where('juicyway_wallet_status', 'active')
                      )
                )
            )
            ->when($this->filterWallet === 'pending', fn($q) =>
                $q->where(fn($w) =>
                    $w->where('korapay_wallet_status', 'pending')
                      ->orWhere(fn($b) =>
                          $b->whereNull('korapay_wallet_status')
                            ->where('budpay_wallet_status', 'pending')
                      )
                      ->orWhere(fn($j) =>
                          $j->whereNull('korapay_wallet_status')
                            ->whereNull('budpay_wallet_status')
                            ->where('juicyway_wallet_status', 'pending')
                      )
                )
            )
            ->when($this->filterWallet === 'failed', fn($q) =>
                $q->where(fn($w) =>
                    $w->where('korapay_wallet_status', 'failed')
                      ->orWhere(fn($b) =>
                          $b->whereNull('korapay_wallet_status')
                            ->where('budpay_wallet_status', 'failed')
                      )
                      ->orWhere(fn($j) =>
                          $j->whereNull('korapay_wallet_status')
                            ->whereNull('budpay_wallet_status')
                            ->where('juicyway_wallet_status', 'failed')
                      )
                )
            )
            ->when($this->filterWallet === 'none', fn($q) =>
                $q->whereNull('korapay_wallet_status')
                  ->whereNull('budpay_wallet_status')
                  ->whereNull('juicyway_wallet_status')
            )
            ->orderByDesc('created_at');

        $parents = $query->paginate(25);

        $stats = [
            'total'          => ParentGuardian::whereNotNull('user_id')->count(),
            'wallet_active'  => ParentGuardian::whereNotNull('user_id')->where(fn($q) =>
                $q->where('korapay_wallet_status', 'active')
                  ->orWhere(fn($b) => $b->whereNull('korapay_wallet_status')->where('budpay_wallet_status', 'active'))
                  ->orWhere(fn($j) => $j->whereNull('korapay_wallet_status')->whereNull('budpay_wallet_status')->where('juicyway_wallet_status', 'active'))
            )->count(),
            'wallet_pending' => ParentGuardian::whereNotNull('user_id')->where(fn($q) =>
                $q->where('korapay_wallet_status', 'pending')
                  ->orWhere(fn($b) => $b->whereNull('korapay_wallet_status')->where('budpay_wallet_status', 'pending'))
                  ->orWhere(fn($j) => $j->whereNull('korapay_wallet_status')->whereNull('budpay_wallet_status')->where('juicyway_wallet_status', 'pending'))
            )->count(),
            'wallet_failed'  => ParentGuardian::whereNotNull('user_id')->where(fn($q) =>
                $q->where('korapay_wallet_status', 'failed')
                  ->orWhere(fn($b) => $b->whereNull('korapay_wallet_status')->where('budpay_wallet_status', 'failed'))
                  ->orWhere(fn($j) => $j->whereNull('korapay_wallet_status')->whereNull('budpay_wallet_status')->where('juicyway_wallet_status', 'failed'))
            )->count(),
        ];

        return view('livewire.admin.parent-list', compact('parents', 'stats'))
            ->layout('layouts.admin', ['title' => 'Parents']);
    }
}