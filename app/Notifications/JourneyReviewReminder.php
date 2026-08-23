<?php

namespace App\Notifications;

use App\Models\Trip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sends a real queued mail-channel reminder from the communication fixture. */
class JourneyReviewReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Trip $trip) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Kyoto review reminder')
            ->greeting('Hello Mara,')
            ->line('The Kyoto journey is ready for its final client review.')
            ->action('Open the journey', route('trips.show', $this->trip))
            ->line('This reminder was delivered by a real database queue worker.');
    }

    public function toArray(object $notifiable): array
    {
        return ['trip_id' => $this->trip->id, 'kind' => 'review-reminder'];
    }
}
