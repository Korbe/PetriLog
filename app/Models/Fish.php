<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Fish extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'desc',
    ];

    /**
     * n:n → Fischarten kommen in mehreren Seen vor
     */
    public function lakes(): BelongsToMany
    {
        return $this->belongsToMany(Lake::class);
    }

    /**
     * n:n → Fischarten kommen in mehreren Flüssen vor
     */
    public function rivers(): BelongsToMany
    {
        return $this->belongsToMany(River::class);
    }
}