<?php
// app/Notifications/AdminNewEnrolmentNotification.php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewEnrolmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Student $student) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Enrolment Submission — Action Required')
            ->greeting("Hello,")
            ->line("A new student enrolment has been submitted and is awaiting your review.")
            ->line("**Student:** {$this->student->full_name}")
            ->line("**Class Applied For:** {$this->student->class_applied_for}")
            ->action('Review in Admin Portal', url('/admin/enrolment/queue'))
            ->salutation('Nurtureville SMS');
    }
}
