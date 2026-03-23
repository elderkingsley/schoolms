<?php
// app/Notifications/ParentWelcomeNotification.php

namespace App\Notifications;

use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParentWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User    $parent,
        public Student $student,
        public string  $tempPassword,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Nurtureville — Your Portal is Ready')
            ->greeting("Dear {$this->parent->name},")
            ->line("We are delighted to welcome {$this->student->full_name} to Nurtureville.")
            ->line("Your parent portal account has been created. You can log in to view your child's results, fees, and lesson notes.")
            ->line("**Login Email:** {$this->parent->email}")
            ->line("**Temporary Password:** {$this->tempPassword}")
            ->action('Log In to Parent Portal', url('/login'))
            ->line('Please change your password after your first login.')
            ->salutation('The Nurtureville Team');
    }
}
