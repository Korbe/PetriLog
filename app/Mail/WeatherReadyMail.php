<?php

namespace App\Mail;

use App\Models\Catched;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * „Die Wetterdaten zu deinem Fang sind da“ – auf Wunsch beim Eintragen des Fangs.
 */
class WeatherReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Catched $catched)
    {
    }

    public function build()
    {
        $this->catched->loadMissing(['user', 'fish', 'lake', 'river']);
        $fish = $this->catched->fish?->name ?? 'deinem Fang';

        return $this->subject("Das Wetter zu deinem {$fish} ist da")
            ->view('emails.weather-ready')
            ->with(['fish' => $fish]);
    }
}
