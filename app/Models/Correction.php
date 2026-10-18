<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Fehlermeldung eines Users zu einem Wiki-Eintrag (Fisch, See, Fluss).
 */
class Correction extends Model
{
    public const TYPES = [
        'fish' => Fish::class,
        'lake' => Lake::class,
        'river' => River::class,
    ];

    public const TYPE_LABELS = [
        'fish' => 'Fischart',
        'lake' => 'See',
        'river' => 'Fluss',
    ];

    public const CATEGORIES = [
        'fish' => 'Fischbestand',
        'tickets' => 'Kartenverkauf / Preise',
        'rights' => 'Fischereirecht / Verein',
        'description' => 'Beschreibung / Tipps',
        'other' => 'Sonstiges',
    ];

    // Für Fischarten passen nur diese Kategorien
    public const FISH_CATEGORIES = ['description', 'other'];

    public const STATUS_OPEN = 'open';
    public const STATUS_DONE = 'done';
    public const STATUS_REJECTED = 'rejected';

    // Spam-Schutz: so viele offene Meldungen darf ein User gleichzeitig haben
    public const MAX_OPEN_PER_USER = 10;

    protected $fillable = ['category', 'message', 'source'];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function correctable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function typeKey(): string
    {
        return array_search($this->correctable_type, self::TYPES, true) ?: 'fish';
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Link zur Wiki-Seite des Eintrags (Seen/Flüsse hängen an einem Bundesland).
     */
    public function wikiUrl(): ?string
    {
        $entry = $this->correctable;
        if (! $entry) {
            return null;
        }

        if ($entry instanceof Fish) {
            return route('app.fish.show', $entry);
        }

        $state = $entry->states()->orderBy('name')->first();

        return $state ? route($entry instanceof Lake ? 'app.lakes.show' : 'app.rivers.show', [$state, $entry]) : null;
    }
}
