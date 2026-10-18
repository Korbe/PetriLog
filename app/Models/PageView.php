<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * Anonymer Seitenaufruf (siehe TrackPageView). Wird nach einem Jahr automatisch gelöscht.
 */
class PageView extends Model
{
    use MassPrunable;

    public const UPDATED_AT = null;

    // 3 Jahre, damit sich Jahre im Admin-Dashboard vergleichen lassen
    public const RETENTION_DAYS = 365 * 3;

    protected $guarded = [];

    // 'date' bleibt bewusst ein Y-m-d-String (ohne Uhrzeit), damit Bereichsabfragen in MySQL und SQLite gleich funktionieren
    protected $casts = [
        'authenticated' => 'boolean',
    ];

    public function prunable(): Builder
    {
        return static::where('date', '<', now()->subDays(self::RETENTION_DAYS)->toDateString());
    }
}
