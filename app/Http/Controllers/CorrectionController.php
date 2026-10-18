<?php

namespace App\Http\Controllers;

use App\Mail\CorrectionAdminMail;
use App\Models\Correction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * „Fehler melden“ auf den Wiki-Seiten. Die Meldung geht an Admins und Editoren, die den Eintrag selbst anpassen.
 */
class CorrectionController extends Controller
{
    public function store(Request $request, string $type, int $id)
    {
        $model = Correction::TYPES[$type] ?? abort(404);
        $entry = $model::approved()->findOrFail($id);
        $user = $request->user();

        $categories = $type === 'fish' ? Correction::FISH_CATEGORIES : array_keys(Correction::CATEGORIES);

        $data = $request->validate([
            'category' => ['required', Rule::in($categories)],
            'message' => ['required', 'string', 'min:5', 'max:2000'],
            'source' => ['nullable', 'string', 'max:500'],
        ], [
            'category.required' => 'Bitte wähle aus, was betroffen ist.',
            'message.required' => 'Bitte beschreib kurz, was nicht stimmt.',
            'message.min' => 'Bitte beschreib kurz, was nicht stimmt.',
        ]);

        if (Correction::open()->where('user_id', $user->id)->count() >= Correction::MAX_OPEN_PER_USER) {
            throw ValidationException::withMessages([
                'message' => 'Du hast bereits ' . Correction::MAX_OPEN_PER_USER . ' offene Meldungen. Bitte warte, bis diese bearbeitet sind.',
            ]);
        }

        $correction = new Correction($data);
        $correction->correctable()->associate($entry);
        $correction->user()->associate($user);
        $correction->save();

        Mail::to($this->reviewerAddresses())->queue(new CorrectionAdminMail($correction));

        return back()->with('success', 'Danke! Wir prüfen deine Meldung und melden uns per E-Mail.');
    }

    /**
     * Team-Postfach plus alle Editoren (Admins lesen das Team-Postfach).
     */
    private function reviewerAddresses(): array
    {
        return User::where('role', User::ROLE_EDITOR)->pluck('email')
            ->prepend(SubmissionController::ADMIN_EMAIL)
            ->unique()
            ->values()
            ->all();
    }
}
