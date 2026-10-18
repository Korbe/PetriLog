<?php

namespace App\Mail;

use App\Models\Correction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Info an Admins/Editoren: Ein User hat einen Fehler im Wiki gemeldet.
 */
class CorrectionAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Correction $correction)
    {
    }

    public function build()
    {
        $this->correction->loadMissing(['correctable', 'user']);
        $name = $this->correction->correctable?->name ?? 'Eintrag';

        return $this->subject("Fehler gemeldet ({$this->correction->categoryLabel()}): {$name}")
            ->view('emails.correction-admin')
            ->with(['name' => $name]);
    }
}
