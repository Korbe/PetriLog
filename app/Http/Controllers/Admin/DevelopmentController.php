<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackupRun;
use App\Services\Backup\BackupFiles;
use App\Services\Backup\RemoteBackupStore;
use Inertia\Inertia;
use Throwable;

/**
 * Sammelstelle für Entwicklungs-, Wartungs- und Administrationswerkzeuge.
 * Neue Werkzeuge werden in tools() ergänzt – die Oberfläche baut sich daraus auf.
 */
class DevelopmentController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Development/Index', [
            'groups' => $this->tools(),
        ]);
    }

    /**
     * @return array<int, array{title: string, tools: array}>
     */
    private function tools(): array
    {
        return [
            [
                'title' => 'Daten & Sicherung',
                'tools' => [
                    [
                        'key' => 'backups',
                        'title' => 'Datenbank-Backups',
                        'desc' => 'Vollständige Backups von Datenbank und Storage erstellen, prüfen, testweise wiederherstellen und herunterladen.',
                        'href' => route('admin.development.backups.index'),
                        'icon' => 'backup',
                        'status' => $this->backupStatus(),
                    ],
                    [
                        'key' => 'wiki-export',
                        'title' => 'Wiki herunterladen',
                        'desc' => 'Fische, Seen, Flüsse, Bundesländer und Vereine im Seeder-Format für database/seeders.',
                        'href' => route('admin.development.wiki-export.index'),
                        'icon' => 'download',
                        'badge' => 'Seeder',
                    ],
                ],
            ],
            [
                'title' => 'Monitoring',
                'tools' => [
                    [
                        'key' => 'pulse',
                        'title' => 'Laravel Pulse',
                        'desc' => 'Performance, langsame Abfragen, Queue-Jobs und Fehler in Echtzeit.',
                        'href' => url('/pulse'),
                        'icon' => 'chart',
                        'external' => true,
                    ],
                ],
            ],
        ];
    }

    private function backupStatus(): ?array
    {
        try {
            $last = BackupRun::where('action', 'create')->whereIn('status', ['success', 'warning'])->latest('finished_at')->first();
            $failed = BackupRun::where('action', 'create')->where('status', 'failed')
                ->when($last, fn($q) => $q->where('created_at', '>', $last->created_at))
                ->exists();

            return [
                'tone' => $failed ? 'danger' : (! $last || $last->finished_at->lt(now()->subDays(2)) ? 'warning' : 'success'),
                'label' => $failed
                    ? 'Letzter Lauf fehlgeschlagen'
                    : ($last ? 'Letztes Backup ' . $last->finished_at->diffForHumans() : 'Noch kein Backup'),
                'detail' => BackupFiles::list()->count() . ' lokal · Zweitserver ' . (RemoteBackupStore::enabled() ? 'aktiv' : 'aus'),
            ];
        } catch (Throwable) {
            return null;
        }
    }
}
