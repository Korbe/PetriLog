<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Catched;

class CatchedPolicy
{
    /**
     * Prüfen ob der Nutzer den Fang sehen darf.
     */
    public function view(User $user, Catched $catched): bool
    {
        return $this->isOwnerOrAdmin($user, $catched);
    }

    /**
     * Prüfen ob der Nutzer den Fang bearbeiten darf.
     */
    public function update(User $user, Catched $catched): bool
    {
        return $this->isOwnerOrAdmin($user, $catched);
    }

    /**
     * Prüfen ob der Nutzer den Fang löschen darf.
     */
    public function delete(User $user, Catched $catched): bool
    {
        return $this->isOwnerOrAdmin($user, $catched);
    }

    /**
     * Hilfsmethode: Besitzer oder Admin.
     */
    protected function isOwnerOrAdmin(User $user, Catched $catched): bool
    {
        return $user->id === $catched->user_id || $user->isAdmin();
    }
}
