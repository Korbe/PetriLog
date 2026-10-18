<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Merkt sich, welcher Admin/Editor einen Eintrag zuletzt gespeichert hat (Spalte `updated_by`).
 * Speichern durch normale User (z. B. Vorschläge) oder Seeder/Konsole ändert den Wert nicht.
 */
trait TracksEditor
{
    public static function bootTracksEditor(): void
    {
        static::saving(function ($model) {
            $user = auth()->user();

            if ($user instanceof User && $user->isStaff()) {
                $model->updated_by = $user->id;
            }
        });
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Für die Admin-Listen: „geändert am … von …“
     */
    public function editorInfo(): array
    {
        return [
            'updated_at' => $this->updated_at,
            'updated_by' => $this->editor?->name,
        ];
    }
}
