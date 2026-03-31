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

        // The parent this email is going to — load their virtual account details
        $parentGuardian = $student->parents
            ->filter(fn($p) => $p->user !== null && $p->user->email === $notifiable->email)
            ->first()
            ?? $student->parents->filter(fn($p) => $p->user !== null)->first();

        $hasVirtualAccount = $parentGuardian
            && ! empty($parentGuardian->juicyway_account_number);

        $mail = (new MailMessage)
            ->subject("Fee Invoice — {$studentName} ({$termLabel})")
            ->greeting("Dear {$notifiable->name},")
            ->line("A fee invoice has been issued for **{$studentName}** for **{$termLabel}**.")
            ->line("**Total Amount Due: {$total}**")
            ->line('---')
            ->line('**Fee Breakdown:**');

        foreach ($this->feeItems as $item) {
            $amount   = '₦' . number_format($item->amount, 0);
            $itemName = $item->feeItem?->name ?? $item->item_name ?? 'Fee Item';
            $mail->line("- {$itemName}: {$amount}");
        }

        $mail->line('---')
             ->line("**Balance Outstanding: {$balance}**")
             ->line('');

        if ($hasVirtualAccount) {
            // Parent has a dedicated virtual bank account — primary payment method
            $mail
                ->line('## Pay by Bank Transfer')
                ->line(
                    'Transfer directly into your dedicated school fees account. ' .
                    'Your payment will be confirmed automatically — no need to send a receipt.'
                )
                ->line('')
                ->line('**Bank:** ' . $parentGuardian->juicyway_bank_name)
                ->line('**Account Number:** ' . $parentGuardian->juicyway_account_number)
                ->line('**Account Name:** ' . $notifiable->name)
                ->line('')
                ->line(
                    'This is your personal school fees account. You can use it for ' .
                    'all future fee payments — no new account number needed each term.'
                )
                ->line('')
                ->line('## Or Pay at the School Bursary')
                ->line("Quote reference **{$reference}** when paying in person.")
                ->action('View Invoice in Parent Portal', url('/parent/fees'));
        } else {
            // Virtual account not yet provisioned — fall back to bursary instructions
            // (ProvisionParentWalletJob is running in the background)
            $mail
                ->line('## How to Pay')
                ->line(
                    'Please make payment at the school bursary or by bank transfer. ' .
                    'Quote the reference below so the bursary can match your payment.'
                )
                ->line("**Payment Reference: {$reference}**")
                ->line('')
                ->line(
                    '_A dedicated bank account for online payment is being set up for you. ' .
                    'You will receive an updated email with bank transfer details shortly._'
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
