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
    public string $filterWallet    = ''; // '' | 'active' | 'pending' | 'failed' | 'none'

    public function updatedSearch(): void       { $this->resetPage(); }
    public function updatedFilterWallet(): void { $this->resetPage(); }

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

    /**
     * Manually re-trigger wallet provisioning for a parent whose job
     * previously failed or whose status is stuck.
     *
     * Self-heals: if the parent already has a NUBAN in the DB but the
     * status column is wrong (e.g. stuck at 'failed'), we fix the status
     * without hitting BudPay again.
     */
    public function retryProvisioning(int $parentId): void
    {
        abort_if(! auth()->user()->isSuperAdmin(), 403);

        $parent = \App\Models\ParentGuardian::findOrFail($parentId);

        // NUBAN already in DB but status is wrong — just correct the status.
        if (! empty($parent->budpay_account_number) && $parent->budpay_wallet_status !== 'active') {
            $parent->update(['budpay_wallet_status' => 'active']);
            session()->flash('success', "Status corrected for parent #{$parentId} — NUBAN was already provisioned.");
            return;
        }

        // No NUBAN yet — dispatch a fresh provisioning job.
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
