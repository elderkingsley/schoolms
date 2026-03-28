<?php

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
        public ?string $tempPassword, // nullable — null means parent already had an account
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $studentName = "{$this->student->first_name} {$this->student->last_name}";

        $mail = (new MailMessage)
            ->greeting("Dear {$this->parent->name},");

        if ($this->tempPassword) {
            // New account — send full welcome with login credentials
            $mail->subject('Welcome to Nurtureville — Your Portal is Ready')
                ->line("We are delighted to welcome {$studentName} to Nurtureville.")
                ->line("Your parent portal account has been created. You can log in to view your child's results, fees, and lesson notes.")
                ->line("**Login Email:** {$this->parent->email}")
                ->line("**Temporary Password:** {$this->tempPassword}")
                ->action('Log In to Parent Portal', url('/login'))
                ->line('Please change your password after your first login.');
        } else {
            // Existing account — parent has another child now enrolled
            $mail->subject("New Student Added — {$studentName}")
                ->line("We are pleased to inform you that {$studentName} has been successfully enrolled at Nurtureville.")
                ->line("This student has been linked to your existing parent portal account.")
                ->action('View Parent Portal', url('/login'))
                ->line('Log in with your existing email and password to view their profile, fees, and results.');
        }

        return $mail->salutation('The Nurtureville Team');
    }
}
