<?php

namespace App\Models;

use App\Models\Catched;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class River extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

    protected $fillable = [
        'state_id',
        'name',
        'desc',
        'hint',
        'fishing_rights',
        'ticket_sales',
    ];

    /**
     * n:n → Flüsse verlaufen durch mehrere Bundesländer
     */
    public function states(): BelongsToMany
    {
        return $this->belongsToMany(State::class);
    }

    /**
     * n:n → Flüsse haben viele Fischarten
     */
    public function fish(): BelongsToMany
    {
        return $this->belongsToMany(Fish::class);
    }

    /**
     * Alle Fänge in diesem Fluss
     */
    public function catched(): HasMany
    {
        return $this->hasMany(Catched::class);
    }
}
