<?php

namespace App\Listeners;

use App\Models\MailLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Address;
use Throwable;

/**
 * Protokolliert jede tatsächlich verschickte E-Mail für das Admin-Dashboard.
 */
class LogSentMail
{
    public function handle(MessageSent $event): void
    {
        try {
            $class = $event->data['__laravel_mailable'] ?? $event->data['__laravel_notification'] ?? null;
            $recipients = array_map(fn(Address $a) => $a->getAddress(), $event->message->getTo());

            MailLog::create([
                'type' => $class ? class_basename($class) : 'Sonstige',
                'subject' => mb_substr((string) $event->message->getSubject(), 0, 255) ?: null,
                'recipient' => mb_substr(implode(', ', $recipients), 0, 255),
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            // Das Protokoll darf den Mailversand nie stören
            Log::warning("Mail log failed: {$e->getMessage()}");
        }
    }
}
