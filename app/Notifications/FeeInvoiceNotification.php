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
        public FeeInvoice $invoice,
        public Collection $feeItems,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $student     = $this->invoice->student;
        $term        = $this->invoice->term;
        $session     = $term->session;
        $studentName = $student->full_name;
        $termLabel   = "{$term->name} Term — {$session->name}";
        $total       = '₦' . number_format($this->invoice->total_amount, 0);
        $balance     = '₦' . number_format($this->invoice->balance, 0);
        $reference   = $this->invoice->payment_link_reference
            ?? "INV-{$this->invoice->id}-T{$this->invoice->term_id}";

        $mail = (new MailMessage)
            ->subject("Fee Invoice — {$studentName} ({$termLabel})")
            ->greeting("Dear {$notifiable->name},")
            ->line("A fee invoice has been issued for **{$studentName}** for **{$termLabel}**.")
            ->line("**Total Amount Due: {$total}**");

        // ── Fee breakdown ─────────────────────────────────────────────────
        $mail->line('---')->line('**Fee Breakdown:**');

        foreach ($this->feeItems as $item) {
            $amount   = '₦' . number_format($item->amount, 0);
            $itemName = $item->feeItem?->name ?? $item->item_name ?? $item->name ?? 'Fee Item';
            $mail->line("- {$itemName}: {$amount}");
        }

        $mail->line('---');

        // ── Payment options ───────────────────────────────────────────────
        if ($this->invoice->payment_link_url) {
            // Payment link is available — primary CTA
            $mail
                ->line("**Balance Outstanding: {$balance}**")
                ->line('')
                ->line('## Pay Online')
                ->line(
                    'Click the button below to pay securely online by card or bank transfer. ' .
                    'Your payment will be automatically confirmed — no need to send a receipt.'
                )
                ->action('Pay Now — ' . $balance, $this->invoice->payment_link_url)
                ->line('')
                ->line('## Or Pay at the School Bursary')
                ->line(
                    'If you prefer to pay in person or by bank transfer, quote the following ' .
                    'reference so the bursary team can match your payment:'
                )
                ->line("**Payment Reference: {$reference}**")
                ->line(
                    'Once your payment is recorded by the bursary, this invoice will be ' .
                    'updated automatically in your parent portal.'
                );
        } else {
            // Payment link not yet generated — fall back to manual instructions
            $mail
                ->line("**Balance Outstanding: {$balance}**")
                ->line('Please make payment at the school bursary or by bank transfer.')
                ->line("**Payment Reference: {$reference}**")
                ->line(
                    'Quote this reference when paying so the bursary team can match your payment.'
                )
                ->action('View Invoice in Parent Portal', url('/parent/fees'));
        }

        $mail
            ->line('---')
            ->line('If you have already made this payment, please disregard this email.')
            ->salutation('The Nurtureville Bursary Team');

        return $mail;
    }
}
