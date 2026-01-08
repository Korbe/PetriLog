<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lake extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'state_id',
        'name',
        'desc',
        'hint',
    ];

    public function states()
    {
        return $this->belongsToMany(State::class);
    }

    public function fish(): BelongsToMany
    {
        return $this->belongsToMany(Fish::class);
    }
}
