<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Fish extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

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

    public function catcheds()
    {
        return $this->hasMany(Catched::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('fish')
            ->singleFile();
    }
}
