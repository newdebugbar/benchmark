<?php

namespace App\Mail;

use App\Models\Trip;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

/** Provides deterministic retry and terminal-failure mail jobs for worker coverage. */
class JourneyReviewDeliveryProbe extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public const FAIL = 'fail';

    public const RETRY_ONCE = 'retry-once';

    public int $backoff = 0;

    public int $tries;

    public function __construct(public Trip $trip, public readonly string $mode)
    {
        if (! in_array($mode, [self::FAIL, self::RETRY_ONCE], true)) {
            throw new InvalidArgumentException("Unknown delivery probe mode [{$mode}].");
        }

        $this->tries = $mode === self::RETRY_ONCE ? 2 : 1;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: match ($this->mode) {
            self::RETRY_ONCE => 'Kyoto journey retry delivery',
            self::FAIL => 'Kyoto journey failed delivery',
        });
    }

    public function content(): Content
    {
        if ($this->mode === self::FAIL) {
            Log::error('Journey review delivery reached a terminal failure.', ['trip_id' => $this->trip->id]);

            throw new RuntimeException('Intentional terminal mail delivery failure.');
        }

        $marker = self::markerDirectory($this->trip).'/first-attempt';

        if (! File::exists($marker)) {
            File::ensureDirectoryExists(dirname($marker));
            File::put($marker, 'failed-once');
            Log::warning('Journey review delivery will retry once.', ['trip_id' => $this->trip->id]);

            throw new RuntimeException('Intentional first-attempt mail delivery failure.');
        }

        Log::info('Journey review delivery retry succeeded.', ['trip_id' => $this->trip->id]);

        return new Content(
            view: 'mail.journey-review-ready',
            text: 'mail.journey-review-ready-text',
        );
    }

    public static function markerDirectory(Trip $trip): string
    {
        $profilePath = config('newdebugbar.storage.path') ?: storage_path('framework/newdebugbar');

        return dirname($profilePath)."/morrow-communication-probes/trip-{$trip->getKey()}";
    }
}
