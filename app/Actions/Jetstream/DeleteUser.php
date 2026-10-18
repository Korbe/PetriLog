<?php

namespace App\Actions\Jetstream;

use App\Models\User;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        // Einzeln über das Model löschen, damit die Fotos (Media Library) mit entfernt werden –
        // der Datenbank-Cascade allein ließe Dateien und Media-Einträge zurück.
        $user->catched()->each(fn($catched) => $catched->delete());

        $user->deleteProfilePhoto();
        $user->tokens->each->delete();
        $user->delete();
    }
}
