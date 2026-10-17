<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Privater Angelplatz – nur für den Besitzer sichtbar, wird nie öffentlich geteilt.
 */
class Spot extends Model
{
    protected $fillable = [
        'name',
        'note',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
