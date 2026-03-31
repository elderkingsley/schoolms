<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

/**
 * In-portal password change component.
 * Works for any user type — admin, teacher, parent — renders inside their
 * own layout so they stay in context rather than being sent to the guest page.
 */
class ChangePassword extends Component
{
    public string $currentPassword    = '';
    public string $newPassword        = '';
    public string $newPasswordConfirm = '';
    public bool   $done               = false;

    public function save(): void
    {
        $this->validate([
            'currentPassword'    => ['required', 'string'],
            'newPassword'        => [
                'required', 'string',
                Password::min(8)->letters()->numbers(),
                'different:currentPassword',
            ],
            'newPasswordConfirm' => ['required', 'same:newPassword'],
        ], [
            'currentPassword.required'    => 'Please enter your current password.',
            'newPassword.different'       => 'New password must be different from your current password.',
            'newPasswordConfirm.same'     => 'Passwords do not match.',
        ]);

        $user = auth()->user();

        // Verify the current password before allowing the change
        if (! Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'That is not your current password.');
            return;
        }

        $user->update([
            'password'              => Hash::make($this->newPassword),
            'force_password_change' => false,
        ]);

        // Clear fields so the form doesn't show old values
        $this->currentPassword    = '';
        $this->newPassword        = '';
        $this->newPasswordConfirm = '';
        $this->done               = true;

        session()->flash('success', 'Password changed successfully.');
    }

    public function render()
    {
        $user   = auth()->user();
        $layout = match ($user->user_type) {
            'parent'     => 'layouts.parent',
            'teacher'    => 'layouts.teacher',
            'accountant' => 'layouts.accountant',
            default      => 'layouts.admin',
        };

        return view('livewire.change-password')
            ->layout($layout, ['title' => 'Change Password']);
    }
}
