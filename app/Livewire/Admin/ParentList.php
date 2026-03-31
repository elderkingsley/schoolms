<?php

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
            ->when($this->filterWallet === 'active',  fn($q) => $q->where('juicyway_wallet_status', 'active'))
            ->when($this->filterWallet === 'pending', fn($q) => $q->where('juicyway_wallet_status', 'pending'))
            ->when($this->filterWallet === 'failed',  fn($q) => $q->where('juicyway_wallet_status', 'failed'))
            ->when($this->filterWallet === 'none',    fn($q) => $q->whereNull('juicyway_wallet_status'))
            ->orderByDesc('created_at');

        $parents = $query->paginate(25);

        $stats = [
            'total'          => ParentGuardian::whereNotNull('user_id')->count(),
            'wallet_active'  => ParentGuardian::where('juicyway_wallet_status', 'active')->count(),
            'wallet_pending' => ParentGuardian::where('juicyway_wallet_status', 'pending')->count(),
            'wallet_failed'  => ParentGuardian::where('juicyway_wallet_status', 'failed')->count(),
        ];

        return view('livewire.admin.parent-list', compact('parents', 'stats'))
            ->layout('layouts.admin', ['title' => 'Parents']);
    }
}
