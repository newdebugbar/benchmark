<?php

namespace App\Mail;

use App\Models\Trip;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Delivers the latest client-ready journey for review. */
class JourneyReviewReady extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Trip $trip) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Kyoto journey is ready to review');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.journey-review-ready',
            text: 'mail.journey-review-ready-text',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn (): string => "Morrow journey brief for {$this->trip->title}",
                'kyoto-journey.txt',
            )->withMime('text/plain'),
        ];
    }
}
