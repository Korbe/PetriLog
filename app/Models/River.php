<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class River extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    protected $fillable = [
        'name',
        'desc',
        'hint',
        'state_id',
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
}