<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Info an das PetriLog-Team: Ein User hat eine Fischart oder ein Gewässer vorgeschlagen.
 */
class SubmissionAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array{label: string, name: string, desc: ?string, fishing_rights: ?string, ticket_sales: ?string,
     *     states: array<int, string>, submitter_name: string, submitter_email: string, created_at: string} $submission
     */
    public function __construct(public array $submission)
    {
    }

    public function build()
    {
        return $this->subject("Neuer Vorschlag ({$this->submission['label']}): {$this->submission['name']}")
            ->view('emails.submission-admin');
    }
}
