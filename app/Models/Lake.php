<?php

namespace App\Models;

use App\Models\Catched;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lake extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

    protected $fillable = [
        'name',
        'desc',
        'hint',
        'fishing_rights',
        'ticket_sales',
    ];

    public function states()
    {
        return $this->belongsToMany(State::class);
    }

    public function fish(): BelongsToMany
    {
        return $this->belongsToMany(Fish::class);
    }

    /**
     * Alle Fänge in diesem See
     */
    public function catched(): HasMany
    {
        return $this->hasMany(Catched::class);
    }
}
