<?php

namespace App\Models;

use App\Support\RichText;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'released',
        'user_id',
    ];

    // Werden ausschließlich vom FetchCatchWeather-Job gesetzt (forceFill)
    public const WEATHER_FIELDS = [
        'weather_temperature',
        'weather_pressure',
        'weather_pressure_trend',
        'weather_humidity',
        'weather_wind_speed',
        'weather_wind_direction',
        'weather_precipitation',
        'weather_status',
        'weather_fetched_at',
    ];

    protected $casts = [
        'date' => 'datetime',
        'released' => 'boolean',
        'weather_temperature' => 'float',
        'weather_pressure' => 'float',
        'weather_pressure_trend' => 'float',
        'weather_humidity' => 'integer',
        'weather_wind_speed' => 'float',
        'weather_wind_direction' => 'integer',
        'weather_precipitation' => 'float',
        'weather_fetched_at' => 'datetime',
    ];

    /**
     * Bemerkung wird per v-html angezeigt (auch auf der öffentlichen Teilen-Seite) und daher beim Speichern bereinigt.
     */
    protected function remark(): Attribute
    {
        return Attribute::make(
            set: fn(?string $value) => RichText::sanitize($value),
        );
    }

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
            ->width(2000)
            ->format('webp')
            ->quality(60)
            ->performOnCollections('photos')
            ->queued();
    }
}
