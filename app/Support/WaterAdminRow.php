<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Zeile für die Admin-Listen der Seen und Flüsse – inkl. Vollständigkeit der Wiki-Angaben.
 * Erwartet geladene `states` sowie `fish_count` und `catched_count`.
 */
class WaterAdminRow
{
    public static function from(Model $water): array
    {
        $filled = fn(?string $html) => filled(trim(strip_tags((string) $html)));

        $fields = [
            'desc' => $filled($water->desc),
            'hint' => $filled($water->hint),
            'fishing_rights' => $filled($water->fishing_rights),
            'ticket_sales' => $filled($water->ticket_sales),
            'fish' => $water->fish_count > 0,
        ];

        return [
            'id' => $water->id,
            'name' => $water->name,
            'is_default' => (bool) $water->is_default,
            'states' => $water->states->map(fn($state) => ['id' => $state->id, 'name' => $state->name])->values(),
            'fish_count' => $water->fish_count,
            'catches_count' => $water->catched_count,
            'completeness' => $fields,
            'completeness_score' => count(array_filter($fields)),
            'updated_at' => $water->updated_at,
            'updated_by' => $water->editor?->name,
        ];
    }
}
