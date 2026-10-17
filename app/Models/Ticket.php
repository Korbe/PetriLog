<?php

namespace App\Models;

use App\Support\RichText;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    /**
     * Kategorien wie im Ticket-Formular (resources/js/Pages/Ticket/Create.vue)
     */
    public const CATEGORIES = [
        'ui' => 'UI / Design',
        'functionality' => 'Funktion',
        'performance' => 'Performance',
        'error' => 'Fehlermeldung',
        'auth' => 'Login / Zugriff',
        'abo' => 'Abo / Abrechnung',
        'data_issue' => 'Datenproblem',
        'feature_request' => 'Feature-Wunsch',
        'other' => 'Sonstiges',
    ];

    protected $fillable = [
        'title',
        'description',
        'category',
        'steps',
        'url',
        'user_id',
    ];

    /**
     * Beschreibung und Schritte kommen aus dem VEditor und landen als HTML in der Admin-Mail.
     */
    protected function description(): Attribute
    {
        return Attribute::make(
            set: fn(?string $value) => RichText::sanitize($value),
        );
    }

    protected function steps(): Attribute
    {
        return Attribute::make(
            set: fn(?string $value) => RichText::sanitize($value),
        );
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Ohne Kategorie';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}