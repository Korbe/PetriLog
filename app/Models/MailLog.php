<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * Protokoll verschickter E-Mails (siehe LogSentMail). Wird nach einem Jahr automatisch gelöscht.
 */
class MailLog extends Model
{
    use MassPrunable;

    public $timestamps = false;

    public const RETENTION_DAYS = 365;

    // Klassenname → Bezeichnung im Admin-Dashboard
    public const LABELS = [
        'WelcomeMail' => 'Willkommen',
        'NewUserMail' => 'Neuer User (Admin)',
        'SubscriptionCreatedMail' => 'Abo abgeschlossen',
        'TicketAdminMail' => 'Ticket (Admin)',
        'TicketUserMail' => 'Ticket-Bestätigung',
        'UserDeletedMail' => 'Konto gelöscht',
        'UserAdminDeletedMail' => 'Konto gelöscht (Admin)',
        'NewsletterMail' => 'Newsletter',
        'SubmissionAdminMail' => 'Vorschlag (Admin)',
        'SubmissionReviewedMail' => 'Vorschlag geprüft',
        'VerifyEmail' => 'E-Mail bestätigen',
        'ResetPassword' => 'Passwort zurücksetzen',
    ];

    protected $guarded = [];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public static function label(string $type): string
    {
        return self::LABELS[$type] ?? $type;
    }

    public function prunable(): Builder
    {
        return static::where('sent_at', '<', now()->subDays(self::RETENTION_DAYS));
    }
}
