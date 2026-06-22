<?php

namespace App\Notifications;

use App\Models\FeeInvoice;
use App\Models\FeeInvoiceAdjustment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public FeeInvoiceAdjustment $adjustment,
        public FeeInvoice $invoice,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $this->invoice->loadMissing('student', 'term.session');

        $studentName = $this->invoice->student->full_name;
        $termLabel = $this->invoice->label();
        $oldTotal = 'NGN '.number_format((float) $this->adjustment->old_total_amount, 0);
        $newTotal = 'NGN '.number_format((float) $this->adjustment->new_total_amount, 0);
        $paid = 'NGN '.number_format((float) $this->adjustment->new_amount_paid, 0);
        $displayBalance = method_exists($this->invoice, 'displayBalance')
            ? $this->invoice->displayBalance()
            : (float) $this->invoice->balance;
        $balance = ($displayBalance < 0 ? '-NGN ' : 'NGN ').number_format(abs($displayBalance), 0);

        $mail = (new MailMessage)
            ->subject("Invoice Updated - {$studentName} ({$termLabel})")
            ->greeting("Dear {$notifiable->name},")
            ->line("The fee invoice for {$studentName} has been updated.")
            ->line("Previous total: {$oldTotal}")
            ->line("New total: {$newTotal}")
            ->line("Amount already paid: {$paid}")
            ->line("Current balance: {$balance}")
            ->line('')
            ->line('Updated fee items:');

        foreach (($this->adjustment->after_snapshot['items'] ?? []) as $item) {
            $mail->line('- '.$item['name'].': NGN '.number_format((float) $item['amount'], 0));
        }

        $creditAmount = (float) $this->adjustment->credit_adjustment_amount;
        if ($creditAmount > 0) {
            $mail->line('')
                ->line('Because the revised total is lower than the amount already paid, the excess has been kept as parent credit.');
        }

        return $mail
            ->action('View Updated Invoice', url('/parent/fees/'.$this->invoice->id))
            ->line('Please contact the bursary if you have any questions.')
            ->salutation('The Nurtureville Bursary Team');
    }
}
