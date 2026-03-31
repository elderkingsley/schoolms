<?php

namespace App\Notifications;

use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * ParentWelcomeNotification
 *
 * Sent when an enrolment is approved. Two scenarios:
 *   1. New parent (tempPassword set)   — full welcome with login credentials + NUBAN if ready
 *   2. Existing parent (tempPassword null) — child added to existing account + NUBAN if ready
 *
 * The $parentRow is the ParentGuardian record for this specific child. It carries
 * the JuicyWay NUBAN if provisioning has already completed by the time this email
 * sends. Since ProvisionParentWalletJob runs asynchronously in the queue,
 * provisioning may or may not be done yet — both cases are handled gracefully.
 */
class ParentWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User           $parent,
        public Student        $student,
        public ?string        $tempPassword, // null = parent already had an account
        public ?ParentGuardian $parentRow = null, // the parents row for this child
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $studentName = "{$this->student->first_name} {$this->student->last_name}";

        // Reload parent row to get latest NUBAN status (provisioning may have
        // completed in the ~seconds between dispatch and queue execution)
        $parentRow = $this->parentRow?->fresh();
        $hasNuban  = $parentRow && $parentRow->hasVirtualAccount();

        $mail = (new MailMessage)->greeting("Dear {$this->parent->name},");

        if ($this->tempPassword) {
            // ── New parent account ────────────────────────────────────────
            $mail->subject('Welcome to Nurtureville — Your Portal is Ready')
                 ->line("We are delighted to welcome **{$studentName}** to Nurtureville School.")
                 ->line("Your parent portal account has been created. Use it to view fees, results, messages, and lesson notes.")
                 ->line('')
                 ->line('**Your Login Details**')
                 ->line("**Email:** {$this->parent->email}")
                 ->line("**Temporary Password:** {$this->tempPassword}")
                 ->action('Log In to Parent Portal', url('/login'))
                 ->line('Please change your password after your first login.');
        } else {
            // ── Existing parent — new child linked ────────────────────────
            $mail->subject("New Student Enrolled — {$studentName}")
                 ->line("We are pleased to confirm that **{$studentName}** has been successfully enrolled at Nurtureville.")
                 ->line("This student has been linked to your existing parent portal account.")
                 ->action('View Parent Portal', url('/login'))
                 ->line('Log in with your existing email and password to view their profile, fees, and results.');
        }

        // ── Bank transfer details (shown if NUBAN is already provisioned) ──
        if ($hasNuban) {
            $mail->line('')
                 ->line('---')
                 ->line('**School Fees Payment Account**')
                 ->line(
                     "A dedicated bank account has been set up for paying **{$studentName}'s** school fees. " .
                     "Transfers to this account are automatically recorded — no receipt needed."
                 )
                 ->line("**Bank:** {$parentRow->juicyway_bank_name}")
                 ->line("**Account Number:** {$parentRow->juicyway_account_number}")
                 ->line("**Account Name:** {$studentName}")
                 ->line('')
                 ->line('This account is permanent — use it for all future fee payments.');
        } else {
            // Provisioning is running in the background — set expectation
            $mail->line('')
                 ->line('---')
                 ->line('**School Fees Payment**')
                 ->line(
                     'A dedicated bank transfer account is being set up for fee payments. ' .
                     "You will receive the account details in your next invoice email. " .
                     'In the meantime, you can pay at the school bursary.'
                 );
        }

        return $mail->salutation('The Nurtureville Team');
    }
}
