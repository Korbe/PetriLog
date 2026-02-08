<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Association extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'desc',
        'link',
        'state_id',
    ];

    // Jede Association gehört zu einem State
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
