<?php

namespace App\Services\Backup;

use RuntimeException;

/**
 * Fehler im Backup-Ablauf. Der Code ist zugleich der Exit-Code der Artisan-Befehle (siehe docs/backup.md).
 */
class BackupException extends RuntimeException
{
    public const GENERAL = 1;
    public const CONFIG = 2;
    public const LOCKED = 3;
    public const DATABASE = 4;
    public const ARCHIVE = 5;
    public const VERIFY = 6;
    public const TRANSFER = 7;
    public const RESTORE = 8;
}
