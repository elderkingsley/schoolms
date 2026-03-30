<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User   $user,
        public string $tempPassword,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $roleLabel = User::userTypeLabel($this->user->user_type);
        $portalUrl = match($this->user->user_type) {
            'teacher'    => url('/teacher/dashboard'),
            'accountant' => url('/accountant/dashboard'),
            default      => url('/admin/dashboard'),
        };

        return (new MailMessage)
            ->subject("Your Nurtureville Portal Account — {$roleLabel}")
            ->greeting("Dear {$this->user->name},")
            ->line("An account has been created for you on the Nurtureville School Management Portal as **{$roleLabel}**.")
            ->line("**Login Email:** {$this->user->email}")
            ->line("**Temporary Password:** {$this->tempPassword}")
            ->action('Log In to Portal', url('/login'))
            ->line('You will be prompted to set a new password on your first login.')
            ->line('If you did not expect this email, please contact the school administrator.')
            ->salutation('The Nurtureville Team');
    }
}
