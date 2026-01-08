<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class State extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    protected $fillable = [
        'name',
        'desc',
    ];

    public function lakes()
    {
        return $this->belongsToMany(Lake::class);
    }

    public function rivers(): BelongsToMany
    {
        return $this->belongsToMany(River::class);
    }
}