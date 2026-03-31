<?php

namespace App\Notifications;

use App\Models\FeeInvoice;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * PaymentReceivedNotification
 *
 * Sent to a parent when their bank transfer into the student's JuicyWay
 * virtual account is confirmed and applied to one or more fee invoices.
 */
class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param FeeInvoice[] $settledInvoices */
    public function __construct(
        public Student $student,
        public float   $amountPaid,
        public string  $senderName,
        public string  $reference,
        public array   $settledInvoices,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $studentName  = $this->student->full_name;
        $amountFormatted = '₦' . number_format($this->amountPaid, 0);

        $mail = (new MailMessage)
            ->subject("Payment Confirmed — {$studentName}")
            ->greeting("Dear {$notifiable->name},")
            ->line("We have received your payment of **{$amountFormatted}** for **{$studentName}**.")
            ->line('Your payment has been automatically applied to the following invoice(s):')
            ->line('');

        foreach ($this->settledInvoices as $invoice) {
            $invoice->refresh();
            $termLabel  = $invoice->term->name . ' Term — ' . $invoice->term->session->name;
            $status     = ucfirst($invoice->status);
            $balance    = '₦' . number_format(max(0, $invoice->balance), 0);

            $statusNote = match ($invoice->status) {
                'paid'    => "✓ **Fully paid**",
                'partial' => "Partially paid — remaining balance: {$balance}",
                default   => "Balance: {$balance}",
            };

            $mail->line("**{$termLabel}** — {$statusNote}");
        }

        $mail->line('')
             ->line("**Payment Reference:** {$this->reference}")
             ->line('')
             ->line('No action is required from you. This is an automatic confirmation.')
             ->action('View Invoices in Parent Portal', url('/parent/fees'))
             ->line('If you have any questions, please contact the school bursary.')
             ->salutation('The Nurtureville Bursary Team');

        return $mail;
    }
}
