<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SchoolMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Message $message) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[Nurtureville] ' . $this->message->subject)
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('You have a new message from Nurtureville School.')
            ->line('**' . $this->message->subject . '**')
            ->line($this->message->body)
            ->action('View in Parent Portal', url('/parent/messages'))
            ->line('Log in to your parent portal to view all messages from the school.')
            ->salutation('The Nurtureville Team');
    }
}
