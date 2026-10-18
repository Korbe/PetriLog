<?php

namespace App\Mail;

use App\Models\Correction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Rückmeldung an den User: Seine Fehlermeldung wurde umgesetzt oder nicht übernommen.
 */
class CorrectionReviewedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Correction $correction)
    {
    }

    public function build()
    {
        $this->correction->loadMissing(['correctable', 'user']);
        $name = $this->correction->correctable?->name ?? 'Wiki-Eintrag';
        $done = $this->correction->status === Correction::STATUS_DONE;

        return $this->subject($done ? "Danke! „{$name}“ ist korrigiert" : "Deine Meldung zu „{$name}“")
            ->view('emails.correction-reviewed')
            ->with(['name' => $name, 'done' => $done]);
    }
}
