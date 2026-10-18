<?php

namespace App\Models;

use Spark\Billable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    public const DEMO_EMAIL = 'demo@petrilog.com';

    // Rollen: Editoren dürfen alles im Admin-Bereich außer Development, Benutzer löschen und Rollen vergeben
    public const ROLE_USER = 'user';
    public const ROLE_EDITOR = 'editor';
    public const ROLE_ADMIN = 'admin';

    public const ROLES = [
        self::ROLE_USER => 'User',
        self::ROLE_EDITOR => 'Editor',
        self::ROLE_ADMIN => 'Admin',
    ];

    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use Billable;
    use Impersonate;

    protected static function booted()
    {
        static::deleting(function ($user) {
            foreach ($user->catched as $catch) {
                $catch->delete(); //delete it directly here to delete images too
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'tel',
        'password',
        'newsletter_opt_out',
        'state_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function catched()
    {
        return $this->hasMany(Catched::class);
    }

    public function spots()
    {
        return $this->hasMany(Spot::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Öffentlicher Demo-Zugang (siehe DemoUserSeeder) – Profil und Abo sind dort gesperrt.
     */
    public function isDemo(): bool
    {
        return strcasecmp((string) $this->email, self::DEMO_EMAIL) === 0;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isEditor(): bool
    {
        return $this->role === self::ROLE_EDITOR;
    }

    /**
     * Admin oder Editor – Zugang zum Admin-Bereich.
     */
    public function isStaff(): bool
    {
        return $this->isAdmin() || $this->isEditor();
    }

    public function canImpersonate(): bool
    {
        return $this->isStaff();
    }

    public function canBeImpersonated(): bool
    {
        // Admins nie; Editoren nur durch Admins
        if ($this->isAdmin()) {
            return false;
        }

        return ! $this->isEditor() || (bool) auth()->user()?->isAdmin();
    }
}
