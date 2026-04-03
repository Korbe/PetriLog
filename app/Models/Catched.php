<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Catched extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

    protected $fillable = [
        'fish_id',
        'lake_id',
        'river_id',
        'length',
        'weight',
        'depth',
        'temperature',
        'date',
        'latitude',
        'longitude',
        'address',
        'remark',
        'air_pressure',
        'bait',
        'user_id',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fish()
    {
        return $this->belongsTo(Fish::class);
    }

    public function lake()
    {
        return $this->belongsTo(Lake::class);
    }

    public function river()
    {
        return $this->belongsTo(River::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')->useFallbackUrl('/logo.png');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('optimized')
            ->format('webp')
            ->quality(60)
            ->optimize()
            ->performOnCollections('photos')
            ->queued();
    }
}
