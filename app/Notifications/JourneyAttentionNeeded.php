<?php

namespace App\Notifications;

use App\Models\Trip;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Tells a travel designer that one client detail still needs attention. */
class JourneyAttentionNeeded extends Notification
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
            ->subject('One detail needs your attention')
            ->greeting('Hello Mara,')
            ->line('Elise still needs to add her passport number for the Arashiyama boat reservation.')
            ->action('Open the journey', route('trips.show', $this->trip))
            ->line('The rest of the Kyoto journey is ready for review.');
    }

    public function toArray(object $notifiable): array
    {
        return ['trip_id' => $this->trip->id, 'kind' => 'missing-passport'];
    }
}
