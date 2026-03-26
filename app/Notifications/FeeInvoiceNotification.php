<?php

namespace App\Notifications;

use App\Models\FeeInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class FeeInvoiceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public FeeInvoice  $invoice,
        public Collection  $feeItems,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $student  = $this->invoice->student;
        $term     = $this->invoice->term;
        $session  = $term->session;

        $studentName = "{$student->first_name} {$student->last_name}";
        $termLabel   = "{$term->name} — {$session->name}";
        $total       = '₦' . number_format($this->invoice->total_amount, 2);

        $mail = (new MailMessage)
            ->subject("Fee Invoice — {$studentName} ({$termLabel})")
            ->greeting("Dear {$notifiable->name},")
            ->line("A fee invoice has been generated for **{$studentName}** for **{$termLabel}**.")
            ->line("**Total Amount Due: {$total}**")
            ->line('---')
            ->line('**Invoice Breakdown:**');

        foreach ($this->feeItems as $item) {
            $amount = '₦' . number_format($item->amount, 2);
            $mail->line("- {$item->feeItem?->name ?? $item->name}: {$amount}");
        }

        $mail->line('---')
            ->line('Please make payment at the school bursary or via bank transfer.')
            ->action('View Parent Portal', url('/login'))
            ->salutation('The Nurtureville Bursary Team');

        return $mail;
    }
}
