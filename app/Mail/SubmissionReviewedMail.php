<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Rückmeldung an den User, ob sein Vorschlag freigegeben oder abgelehnt wurde.
 * Bekommt nur einfache Werte, da abgelehnte Einträge beim Versand bereits gelöscht sind.
 */
class SubmissionReviewedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array{user_name: string, label: string, name: string, approved: bool, url: ?string,
     *     replacement: ?string, reason: ?string} $review
     */
    public function __construct(public array $review)
    {
    }

    public function build()
    {
        return $this->review['approved']
            ? $this->subject("Freigegeben: „{$this->review['name']}“ ist jetzt im PetriLog-Wiki")
                ->view('emails.submission-approved')
            : $this->subject("Dein Vorschlag „{$this->review['name']}“ wurde nicht übernommen")
                ->view('emails.submission-rejected');
    }
}
