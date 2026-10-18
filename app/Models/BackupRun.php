<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Status eines Backup-Vorgangs. Fällt die Datenbank selbst aus, protokolliert der Vorgang trotzdem ins Backup-Log.
 */
class BackupRun extends Model
{
    use MassPrunable;

    protected $guarded = [];

    // Statusprotokoll zwei Jahre aufbewahren
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subYears(2));
    }

    protected $casts = [
        'details' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Legt einen Lauf an – schlägt das fehl (z. B. DB nicht erreichbar), läuft das Backup ohne Statuszeile weiter.
     */
    public static function begin(string $action, string $trigger = 'cli', ?int $userId = null, ?string $file = null): ?self
    {
        try {
            return static::create([
                'action' => $action,
                'status' => 'running',
                'trigger' => $trigger,
                'user_id' => $userId,
                'file' => $file,
                'started_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::channel(config('backup.log_channel'))->warning("Backup-Status konnte nicht gespeichert werden: {$e->getMessage()}");
            return null;
        }
    }

    // Eingeplant, aber nie gestartet → der Auftrag ging verloren (kein Queue-Worker, Queue geleert …)
    public const STALE_QUEUED_HOURS = 2;

    // Gestartet, aber nie fertig → Prozess wurde beendet (Neustart, Deploy, Speicher …). Großzügig wegen langer Restores.
    public const STALE_RUNNING_HOURS = 6;

    /**
     * Hängengebliebene Einträge abschließen, damit die Weboberfläche nicht dauerhaft „läuft“ anzeigt.
     */
    public static function finalizeStale(): int
    {
        try {
            $queued = static::where('status', 'queued')
                ->where('created_at', '<', now()->subHours(self::STALE_QUEUED_HOURS))
                ->update([
                    'status' => 'failed',
                    'message' => 'Nie gestartet – der Hintergrundauftrag wurde nicht abgearbeitet. Läuft der Queue-Worker?',
                    'finished_at' => now(),
                ]);

            $running = static::where('status', 'running')
                ->where(fn($q) => $q->where('started_at', '<', now()->subHours(self::STALE_RUNNING_HOURS))
                    ->orWhere(fn($q) => $q->whereNull('started_at')->where('created_at', '<', now()->subHours(self::STALE_RUNNING_HOURS))))
                ->update([
                    'status' => 'failed',
                    'message' => 'Abgebrochen – der Vorgang wurde unterbrochen (z. B. Neustart oder Deploy) und nicht abgeschlossen.',
                    'finished_at' => now(),
                ]);

            return $queued + $running;
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * Übernimmt einen von der Weboberfläche angelegten Eintrag („eingeplant“) oder legt einen neuen an.
     */
    public static function adoptOrBegin(?string $runId, string $action, string $trigger, ?string $file = null): ?self
    {
        $run = $runId ? static::query()->whereKey($runId)->where('action', $action)->first() : null;

        if (! $run) {
            return static::begin($action, $trigger, null, $file);
        }

        $run->update(['status' => 'running', 'started_at' => now(), 'file' => $file ?? $run->file]);

        return $run;
    }

    public function finish(string $status, ?string $message = null, array $attributes = []): void
    {
        try {
            $this->fill([...$attributes, 'status' => $status, 'message' => $message, 'finished_at' => now()])->save();
        } catch (Throwable $e) {
            Log::channel(config('backup.log_channel'))->warning("Backup-Status konnte nicht aktualisiert werden: {$e->getMessage()}");
        }
    }
}
