<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrolmentRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $parentName,
        public string $studentFirstName,
        public string $studentLastName,
        public string $classAppliedFor,
        public string $rejectionReason = '',
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $studentName = "{$this->studentFirstName} {$this->studentLastName}";

        return (new MailMessage)
            ->subject("Enrolment Update — {$studentName}")
            ->greeting("Dear {$this->parentName},")
            ->line("Thank you for your interest in Nurtureville School.")
            ->line("We regret to inform you that the enrolment application for **{$studentName}** for **{$this->classAppliedFor}** has not been successful at this time.")
            ->when($this->rejectionReason, fn($mail) => $mail->line("**Reason:** {$this->rejectionReason}"))
            ->line("If you would like more information or wish to be considered for a future intake, please contact the school directly.")
            ->action('Contact School', url('/'))
            ->salutation("Yours sincerely,\nThe Nurtureville School Administration");
    }
}
