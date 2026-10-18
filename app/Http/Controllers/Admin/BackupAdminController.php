<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunBackupCommand;
use App\Models\BackupRun;
use App\Services\Backup\BackupException;
use App\Services\Backup\BackupFiles;
use App\Services\Backup\BackupRunner;
use App\Services\Backup\RemoteBackupStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Backups in der Weboberfläche. Das Erstellen läuft über die Queue denselben Weg wie Cron (backup:run);
 * produktive Wiederherstellungen gibt es bewusst nur auf der Kommandozeile.
 */
class BackupAdminController extends Controller
{
    // Muss zum Bestätigen einer produktiven Wiederherstellung eingetippt werden
    public const CONFIRM_WORD = 'WIEDERHERSTELLEN';

    public function index()
    {
        BackupRun::finalizeStale();

        $runs = BackupRun::with('user:id,name')->latest()->limit(30)->get();
        $transferred = BackupRun::where('action', 'transfer')->where('status', 'success')->pluck('file')->flip();
        $verified = BackupRun::where('action', 'verify')->where('status', 'success')->pluck('finished_at', 'file');
        $restored = BackupRun::where('action', 'restore-test')->whereIn('status', ['success', 'warning'])->pluck('finished_at', 'file');
        $downloads = BackupRunner::downloadOnlyFiles()->flip();

        // Sicherheits-Backups vor produktiven Wiederherstellungen (Eintrag überlebt die Wiederherstellung)
        $restores = BackupRun::where('action', 'restore-production')->latest()->get(['file', 'status', 'details', 'finished_at']);
        $safety = $restores->filter(fn(BackupRun $r) => filled($r->details['safety_backup'] ?? null))
            ->mapWithKeys(fn(BackupRun $r) => [$r->details['safety_backup'] => $r->finished_at]);
        $lastRestore = $restores->first();

        $backups = BackupFiles::list()->map(fn(array $backup) => [
            'name' => $backup['name'],
            'size' => $backup['size'],
            'created_at' => $backup['created_at'],
            'sha256' => $backup['sha256'],
            'transferred' => isset($transferred[$backup['name']]),
            'verified_at' => $verified[$backup['name']] ?? null,
            'restore_tested_at' => $restored[$backup['name']] ?? null,
            'download_only' => isset($downloads[$backup['name']]),
            'safety_for' => $safety[$backup['name']] ?? null,
        ]);

        return Inertia::render('Admin/Development/Backups', [
            'backups' => $backups,
            'runs' => $runs->map(fn(BackupRun $run) => [
                'id' => $run->id,
                'action' => $run->action,
                'status' => $run->status,
                'trigger' => $run->trigger,
                'user' => $run->user?->name,
                'file' => $run->file,
                'size' => $run->size,
                'message' => $run->message,
                'download_only' => (bool) ($run->details['download_only'] ?? false),
                'mine' => $run->user_id === Auth::id(),
                'started_at' => $run->started_at,
                'finished_at' => $run->finished_at,
                'created_at' => $run->created_at,
            ]),
            // Hinweis nach einer Wiederherstellung: vorheriger Stand zum Herunterladen
            'lastRestore' => $lastRestore && filled($lastRestore->details['safety_backup'] ?? null)
                && is_file(BackupFiles::path($lastRestore->details['safety_backup']))
                && $lastRestore->finished_at?->gt(now()->subDays(7))
                ? [
                    'status' => $lastRestore->status,
                    'from' => $lastRestore->file,
                    'safety' => $lastRestore->details['safety_backup'],
                    'at' => $lastRestore->finished_at,
                ]
                : null,
            'busy' => $runs->contains(fn(BackupRun $run) => in_array($run->status, ['queued', 'running'], true)),
            // Eingeplant, aber seit über 2 Minuten nicht gestartet → vermutlich läuft kein Queue-Worker
            'waitingForWorker' => $runs->contains(fn(BackupRun $run) => $run->status === 'queued'
                && $run->created_at->lt(now()->subMinutes(2))),
            'config' => [
                'encrypted' => filled(config('backup.encryption_password')),
                'remote' => RemoteBackupStore::enabled() ? [
                    'host' => config('backup.remote.host'),
                    'path' => config('backup.remote.root'),
                    'fingerprint' => filled(config('backup.remote.host_fingerprint')),
                    'prune' => (bool) config('backup.remote.prune'),
                ] : null,
                'retention' => config('backup.retention'),
                'schedule' => config('backup.schedule.enabled') ? config('backup.schedule.time') : null,
                'test_database' => config('backup.restore.test_database'),
                'confirm_word' => self::CONFIRM_WORD,
                'keep_local' => (int) config('backup.retention.local.keep_latest'),
                'upload' => ['chunk_mb' => (int) config('backup.upload.chunk_mb'), 'max_mb' => (int) config('backup.upload.max_mb')],
            ],
        ]);
    }

    public function store()
    {
        // Backup aus der Weboberfläche: nur zum Herunterladen – nicht auf den Zweitserver übertragen.
        // Die Kopie auf diesem Server löscht der nächste nächtliche Lauf (BackupRunner::pruneLocal).
        return $this->queue('create', 'backup:run', null,
            'Backup wird erstellt – der Download startet automatisch, sobald es fertig ist.', ['--download' => true]);
    }

    /**
     * Hochladen eines (verschlüsselten) Backups in Teilstücken – unabhängig von upload_max_filesize/client_max_body_size.
     * Nach dem letzten Teil wird die Datei vollständig geprüft; ein ungültiger Upload wird wieder gelöscht.
     */
    public function upload(Request $request): JsonResponse
    {
        $chunkKb = (int) config('backup.upload.chunk_mb') * 1024;
        $data = $request->validate([
            'upload_id' => 'required|uuid',
            'name' => ['required', 'string', 'max:120', 'regex:' . BackupFiles::NAME_PATTERN],
            'index' => 'required|integer|min:0',
            'total' => 'required|integer|min:1|max:100000',
            'chunk' => 'required|file|max:' . ($chunkKb + 64),
        ], [
            'name.regex' => 'Nur PetriLog-Backups (petrilog-backup-JJJJ-MM-TT_HHMMSS.zip) können hochgeladen werden.',
        ]);

        $name = BackupFiles::safeName($data['name']);
        if (is_file(BackupFiles::path($name))) {
            return response()->json(['message' => "Das Backup „{$name}“ ist bereits vorhanden."], 422);
        }

        $partial = BackupFiles::directory() . DIRECTORY_SEPARATOR . '.upload-' . $data['upload_id'];
        $metaFile = "{$partial}.json";

        if ((int) $data['index'] === 0) {
            $this->removeStaleUploads();
            $meta = ['name' => $name, 'next' => 0, 'user_id' => Auth::id()];
            file_put_contents($partial, '');
            @chmod($partial, 0600);
        } else {
            $meta = is_file($metaFile) ? json_decode((string) file_get_contents($metaFile), true) : null;
            if (! $meta || $meta['name'] !== $name || $meta['user_id'] !== Auth::id() || $meta['next'] !== (int) $data['index']) {
                return response()->json(['message' => 'Upload unterbrochen – bitte erneut starten.'], 409);
            }
        }

        $in = fopen($request->file('chunk')->getRealPath(), 'rb');
        $out = fopen($partial, 'ab');
        stream_copy_to_stream($in, $out);
        fclose($in);
        fclose($out);

        clearstatcache(true, $partial);
        if (filesize($partial) > (int) config('backup.upload.max_mb') * 1048576) {
            @unlink($partial);
            @unlink($metaFile);
            return response()->json(['message' => 'Die Datei überschreitet die maximale Größe von ' . config('backup.upload.max_mb') . ' MB.'], 422);
        }

        $meta['next']++;

        if ($meta['next'] < (int) $data['total']) {
            file_put_contents($metaFile, json_encode($meta));
            return response()->json(['next' => $meta['next']]);
        }

        // Letzter Teil: an den endgültigen Namen und vollständig prüfen lassen
        @unlink($metaFile);
        rename($partial, BackupFiles::path($name));
        @chmod(BackupFiles::path($name), 0600);

        Log::channel(config('backup.log_channel'))->notice('Backup hochgeladen', ['file' => $name, 'user_id' => Auth::id()]);

        $run = BackupRun::create([
            'action' => 'verify', 'status' => 'queued', 'trigger' => 'web', 'user_id' => Auth::id(), 'file' => $name,
            'message' => 'Hochgeladen – wird geprüft.',
        ]);
        RunBackupCommand::dispatch('backup:verify', $run->id, $name, ['--uploaded' => true]);

        return response()->json(['done' => true, 'name' => $name]);
    }

    /**
     * Produktive Wiederherstellung – nur mit Eingabe des Datenbanknamens. Vorher entsteht automatisch ein
     * Sicherheits-Backup, während der Wiederherstellung ist die Website gesperrt.
     */
    public function restoreProduction(Request $request, string $name)
    {
        $name = $this->existing($name);
        $database = $this->productionDatabase();

        $request->validate([
            'confirm' => ['required', 'string', Rule::in([self::CONFIRM_WORD])],
            'acknowledge' => 'accepted',
        ], [
            'confirm.in' => 'Bitte genau ' . self::CONFIRM_WORD . ' eintippen.',
            'acknowledge.accepted' => 'Bitte bestätige, dass alle aktuellen Daten ersetzt werden.',
        ]);

        Log::channel(config('backup.log_channel'))->warning('Produktive Wiederherstellung über die Weboberfläche angefordert', [
            'file' => $name, 'user_id' => Auth::id(), 'ip' => $request->ip(),
        ]);

        return $this->queue('restore-production', 'backup:restore-production', $name,
            'Wiederherstellung gestartet. Zuerst wird ein Sicherheits-Backup erstellt, dann ist die Website für einige Minuten gesperrt. Danach bitte neu anmelden.',
            ['--confirm' => $database]);
    }

    public function verify(string $name)
    {
        return $this->queue('verify', 'backup:verify', $this->existing($name), 'Prüfung wurde eingeplant.');
    }

    public function restoreTest(string $name)
    {
        return $this->queue('restore-test', 'backup:restore-test', $this->existing($name),
            'Wiederherstellungstest in die isolierte Datenbank „' . config('backup.restore.test_database') . '“ wurde eingeplant.');
    }

    /**
     * Download nur nach erneuter Passwort-Bestätigung (Middleware password.confirm). Die Datei bleibt verschlüsselt.
     */
    public function download(string $name): BinaryFileResponse
    {
        $name = $this->existing($name);

        Log::channel(config('backup.log_channel'))->notice('Backup heruntergeladen', [
            'file' => $name, 'user_id' => Auth::id(), 'ip' => request()->ip(),
        ]);

        return response()->download(BackupFiles::path($name), $name, [
            'Content-Type' => 'application/zip',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /**
     * Lokales Backup löschen (z. B. ein hochgeladenes nach der Wiederherstellung).
     */
    public function destroy(string $name)
    {
        $name = $this->existing($name);
        BackupFiles::delete(BackupFiles::path($name));

        Log::channel(config('backup.log_channel'))->notice('Lokales Backup gelöscht', ['file' => $name, 'user_id' => Auth::id()]);

        return back()->with('success', "{$name} wurde von diesem Server gelöscht.");
    }

    private function productionDatabase(): string
    {
        return (string) config('database.connections.' . config('backup.database.connection') . '.database');
    }

    private function removeStaleUploads(): void
    {
        foreach (glob(BackupFiles::directory() . DIRECTORY_SEPARATOR . '.upload-*') ?: [] as $file) {
            if (filemtime($file) < time() - 86400) {
                @unlink($file);
            }
        }
    }

    private function queue(string $action, string $command, ?string $file, string $message, array $options = [])
    {
        BackupRun::finalizeStale();

        if (BackupRun::whereIn('status', ['queued', 'running'])->exists()) {
            return back()->with('error', 'Es läuft bereits ein Backup-Vorgang – bitte warten.');
        }

        $run = BackupRun::create([
            'action' => $action,
            'status' => 'queued',
            'trigger' => 'web',
            'user_id' => Auth::id(),
            'file' => $file,
        ]);

        RunBackupCommand::dispatch($command, $run->id, $file, $options);

        return back()->with('success', $message);
    }

    private function existing(string $name): string
    {
        try {
            $name = BackupFiles::safeName($name);
        } catch (BackupException) {
            abort(404);
        }

        abort_unless(is_file(BackupFiles::path($name)), 404);

        return $name;
    }
}
