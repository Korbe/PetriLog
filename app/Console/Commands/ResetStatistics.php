<?php

namespace App\Console\Commands;

use App\Models\MailLog;
use App\Models\PageView;
use Illuminate\Console\Command;

/**
 * Zugriffsstatistik auf null setzen, z. B. nach Tests oder wenn Bot-Zugriffe die Zahlen verfälscht haben.
 * Fänge, User und alle anderen Daten bleiben unberührt.
 */
class ResetStatistics extends Command
{
    protected $signature = 'stats:reset
        {--mails : Zusätzlich das Mail-Protokoll leeren}
        {--force : Ohne Rückfrage ausführen}';

    protected $description = 'Zugriffsstatistik (Seitenaufrufe, Besucher, Geräte) auf null setzen – optional auch das Mail-Protokoll';

    public function handle(): int
    {
        $views = PageView::count();
        $mails = $this->option('mails') ? MailLog::count() : 0;

        $this->line("Gelöscht werden: {$views} Seitenaufrufe" . ($this->option('mails') ? " und {$mails} Einträge im Mail-Protokoll" : '') . '.');
        $this->line('Fänge, User und alle anderen Daten bleiben unberührt. Das lässt sich nicht rückgängig machen.');

        if (! $this->option('force') && ! $this->confirm('Statistik wirklich zurücksetzen?')) {
            $this->info('Abgebrochen – nichts gelöscht.');

            return self::SUCCESS;
        }

        PageView::query()->delete();
        if ($this->option('mails')) {
            MailLog::query()->delete();
        }

        $this->info('Statistik zurückgesetzt. Ab jetzt wird neu gezählt.');

        return self::SUCCESS;
    }
}
