<?php

/*
|--------------------------------------------------------------------------
| Vollständige Backups (Datenbank + Storage)
|--------------------------------------------------------------------------
|
| Siehe docs/backup.md für Einrichtung, Cron, Zweitserver und Wiederherstellung.
| Zugangsdaten stehen ausschließlich in der .env – nie in diesem File, in Logs oder Kommandozeilen.
|
*/

return [

    // Lokaler Ablageort – außerhalb von public/ und außerhalb der gesicherten Storage-Pfade
    'local_path' => env('BACKUP_LOCAL_PATH', storage_path('backups')),

    // Dateiname: {prefix}-{Y-m-d_His}.zip
    'prefix' => env('BACKUP_PREFIX', 'petrilog-backup'),

    // AES-256-Verschlüsselung des ZIP. Ohne Passwort entstehen unverschlüsselte Backups (nur lokal sinnvoll).
    // Das Passwort zusätzlich sicher außerhalb des Servers verwahren – ohne es ist keine Wiederherstellung möglich.
    'encryption_password' => env('BACKUP_ENCRYPTION_PASSWORD'),

    // In Produktion ohne Verschlüsselung abbrechen
    'require_encryption' => env('BACKUP_REQUIRE_ENCRYPTION', env('APP_ENV') === 'production'),

    'database' => [
        'connection' => env('BACKUP_DB_CONNECTION', env('DB_CONNECTION', 'mysql')),

        // Optional eigener, rein lesender MySQL-User für den Dump (siehe docs/backup.md)
        'username' => env('BACKUP_DB_USERNAME'),
        'password' => env('BACKUP_DB_PASSWORD'),

        // Pfade zu den MySQL-Clients; Standard: aus MYSQL_PATH abgeleitet bzw. im PATH
        'mysqldump_binary' => env('BACKUP_MYSQLDUMP_PATH'),
        'mysql_binary' => env('BACKUP_MYSQL_PATH', env('MYSQL_PATH')),

        'include_routines' => env('BACKUP_DUMP_ROUTINES', false),
        'include_events' => env('BACKUP_DUMP_EVENTS', false),

        'timeout' => (int) env('BACKUP_DUMP_TIMEOUT', 3600),

        // Nur die Struktur sichern, nicht den Inhalt: kurzlebige Daten, die nach einer Wiederherstellung
        // schaden würden (alte Sitzungen, veralteter Cache, Monitoring-Rohdaten). Alles andere wird vollständig gesichert.
        'structure_only_tables' => [
            'sessions', 'cache', 'cache_locks', 'pulse_entries', 'pulse_aggregates', 'pulse_values',
        ],
    ],

    // Storage-Verzeichnisse, die mitgesichert werden (relativ zu storage/)
    'storage_paths' => [
        'app/public',   // Fang-Fotos, Fisch-Bilder, Wappen (Spatie Media Library)
        'app/private',  // private Uploads
    ],

    // Dateien/Ordner innerhalb der Storage-Pfade, die nie ins Backup gehören
    'storage_exclude' => ['.gitignore', 'tmp/', 'livewire-tmp/'],

    'restore' => [
        // Standardziel für Wiederherstellungstests – niemals die produktive Datenbank
        'test_database' => env('BACKUP_RESTORE_TEST_DATABASE', env('DB_DATABASE', 'petrilog') . '_restore_test'),

        // Optional eigener MySQL-User mit CREATE/DROP-Rechten nur für die Test-Datenbank
        'username' => env('BACKUP_RESTORE_DB_USERNAME'),
        'password' => env('BACKUP_RESTORE_DB_PASSWORD'),
    ],

    // Aufbewahrung: so viele Backups je Kategorie werden behalten (Großvater-Vater-Sohn)
    'retention' => [
        'local' => [
            // Anzahl neuester Backups, die zusätzlich auf diesem Server bleiben (0 = keines; ohne Zweitserver immer mind. 1)
            'keep_latest' => (int) env('BACKUP_LOCAL_KEEP', 1),
        ],
        'remote' => [
            'daily' => (int) env('BACKUP_KEEP_DAILY', 7),
            'weekly' => (int) env('BACKUP_KEEP_WEEKLY', 5),
            'monthly' => (int) env('BACKUP_KEEP_MONTHLY', 12),
        ],
    ],

    // Zweitserver (SFTP mit SSH-Schlüssel)
    'remote' => [
        'enabled' => env('BACKUP_REMOTE_ENABLED', false),
        'host' => env('BACKUP_REMOTE_HOST'),
        'port' => (int) env('BACKUP_REMOTE_PORT', 22),
        'username' => env('BACKUP_REMOTE_USERNAME'),
        // Pfad zur privaten Schlüsseldatei (nicht der Schlüssel selbst!)
        'private_key' => env('BACKUP_REMOTE_PRIVATE_KEY'),
        'passphrase' => env('BACKUP_REMOTE_KEY_PASSPHRASE'),
        // SHA-256-Fingerprint des Server-Hostkeys (Schutz vor Man-in-the-Middle)
        'host_fingerprint' => env('BACKUP_REMOTE_HOST_FINGERPRINT'),
        'root' => env('BACKUP_REMOTE_PATH', '/backups/petrilog'),
        'timeout' => (int) env('BACKUP_REMOTE_TIMEOUT', 30),

        // Aufbewahrung auch remote durchsetzen. Sicherer ist es, das dem Backup-Server selbst zu überlassen
        // (scripts/backup-server/prune-backups.sh) – dann braucht der Produktivserver kein Löschrecht.
        'prune' => env('BACKUP_REMOTE_PRUNE', false),
    ],

    // Hochladen von Backups über die Weboberfläche (in Teilstücken – unabhängig von PHP-/nginx-Upload-Limits)
    'upload' => [
        'chunk_mb' => (int) env('BACKUP_UPLOAD_CHUNK_MB', 8),
        'max_mb' => (int) env('BACKUP_UPLOAD_MAX_MB', 5120),
    ],

    // Laravel-Scheduler (php artisan schedule:run per Cron). Alternativ backup:run direkt per Cron aufrufen.
    'schedule' => [
        'enabled' => env('BACKUP_SCHEDULE', false),
        'time' => env('BACKUP_SCHEDULE_TIME', '03:15'), // Europe/Vienna
    ],

    'log_channel' => 'backup',
];
