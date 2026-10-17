<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fischarten und Gewässer, die von Usern vorgeschlagen und von Admins freigegeben werden.
 */
trait Submittable
{
    public function initializeSubmittable(): void
    {
        $this->mergeCasts(['is_approved' => 'boolean']);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Offizielle Einträge (Wiki, Admin-Auswahllisten, andere User).
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_approved'), true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_approved'), false);
    }

    /**
     * Offizielle Einträge plus die eigenen, noch nicht geprüften Vorschläge.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(fn(Builder $q) => $q
            ->where($this->qualifyColumn('is_approved'), true)
            ->orWhere($this->qualifyColumn('submitted_by'), $user->id));
    }

    public function isVisibleTo(?User $user): bool
    {
        return $this->is_approved || ($user && ($user->isAdmin() || $this->submitted_by === $user->id));
    }
}
