<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnrolmentConfirmationMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public string $studentFirstName,
        public string $studentLastName,
        public string $parentName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Enrolment Received — Nurtureville School',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.enrolment-confirmation',
        );
    }
}
