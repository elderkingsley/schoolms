<?php

namespace App\Notifications;

use App\Models\Student;
use App\Models\Term;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportCardNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Student $student,
        public Term    $term,
        public string  $pdfContent,   // raw PDF bytes
        public string  $filename,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $studentName = $this->student->full_name;
        $termLabel   = "{$this->term->name} Term — {$this->term->session->name}";

        return (new MailMessage)
            ->subject("Report Card — {$studentName} ({$termLabel})")
            ->greeting("Dear {$notifiable->name},")
            ->line("Please find attached the report card for **{$studentName}** for **{$termLabel}**.")
            ->line("The report card contains subject scores, grades, and teacher comments for the term.")
            ->action('View Parent Portal', url('/parent/results'))
            ->line('If you have any questions about your child\'s results, please contact the school.')
            ->salutation('The Nurtureville Team')
            ->attachData(
                $this->pdfContent,
                $this->filename,
                ['mime' => 'application/pdf']
            );
    }
}
