<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\ParentGuardian;
use App\Notifications\SchoolMessageNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 30;

    public function __construct(
        public Message $message,
        // For 'individual' type, pass specific parent IDs directly
        public array $individualParentIds = [],
    ) {}

    public function handle(): void
    {
        // Resolve the recipient list
        if ($this->message->recipient_type === 'individual') {
            $parents = ParentGuardian::whereIn('id', $this->individualParentIds)
                ->whereNotNull('user_id')
                ->get();
        } else {
            $parents = Message::resolveRecipients(
                $this->message->recipient_type,
                $this->message->school_class_id,
                $this->message->term_id,
            );
        }

        $count = 0;

        foreach ($parents as $parent) {
            // Idempotency — don't create duplicate recipient rows if job retries
            $recipient = MessageRecipient::firstOrCreate([
                'message_id' => $this->message->id,
                'parent_id'  => $parent->id,
            ]);

            // Send email notification to the parent's portal account
            if ($parent->user) {
                try {
                    $parent->user->notify(
                        new SchoolMessageNotification($this->message)
                    );
                } catch (\Throwable $e) {
                    Log::error('SchoolMessage email failed', [
                        'message_id' => $this->message->id,
                        'parent_id'  => $parent->id,
                        'error'      => $e->getMessage(),
                    ]);
                    // Don't re-throw — one failed email should not abort the whole job
                }
            }

            $count++;
        }

        // Update the snapshot count on the message
        $this->message->update([
            'recipient_count' => $count,
            'sent_at'         => now(),
        ]);
    }
}
