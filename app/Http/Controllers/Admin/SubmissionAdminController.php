<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SubmissionController;
use App\Mail\SubmissionReviewedMail;
use App\Models\Catched;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Prüfung der von Usern vorgeschlagenen Fischarten und Gewässer.
 */
class SubmissionAdminController extends Controller
{
    private const CATCH_COLUMN = ['fish' => 'fish_id', 'lake' => 'lake_id', 'river' => 'river_id'];

    public function index()
    {
        $pending = collect(SubmissionController::TYPES)->map(function (string $model, string $type) {
            $query = $model::pending()->with('submitter:id,name,email')->oldest();

            if ($type !== 'fish') {
                $query->with('states:id,name');
            }

            return $query->get()->map(fn(Model $entry) => [
                'type' => $type,
                'id' => $entry->id,
                'name' => $entry->name,
                'desc' => $entry->desc,
                'fishing_rights' => $entry->fishing_rights ?? null,
                'ticket_sales' => $entry->ticket_sales ?? null,
                'states' => $type === 'fish' ? [] : $entry->states->pluck('name'),
                'submitter' => $entry->submitter?->only(['id', 'name', 'email']),
                'created_at' => $entry->created_at,
                'catches' => Catched::where(self::CATCH_COLUMN[$type], $entry->id)->count(),
            ]);
        })->flatten(1)->sortBy('created_at')->values();

        return Inertia::render('Admin/Submissions/Index', [
            'submissions' => $pending,
            // Zusammenführen mit bestehenden Einträgen (z. B. bei Dubletten oder Tippfehlern)
            'options' => collect(SubmissionController::TYPES)->map(
                fn(string $model) => $model::approved()->orderByDesc('is_default')->orderBy('name')->get(['id', 'name', 'is_default'])
            ),
        ]);
    }

    public function approve(string $type, int $id)
    {
        $entry = $this->pendingEntry($type, $id);
        $entry->forceFill(['is_approved' => true])->save();

        $this->notifySubmitter($entry, $type, [
            'approved' => true,
            'url' => $this->wikiUrl($entry, $type),
        ]);

        return back()->with('success', "„{$entry->name}“ ist jetzt offiziell freigegeben.");
    }

    /**
     * Ablehnen: Fänge werden auf einen bestehenden Eintrag umgehängt, danach wird der Vorschlag gelöscht.
     */
    public function reject(Request $request, string $type, int $id)
    {
        $entry = $this->pendingEntry($type, $id);
        $model = SubmissionController::TYPES[$type];

        $data = $request->validate([
            'replacement_id' => 'nullable|integer',
            'reason' => 'nullable|string|max:1000',
        ]);

        $replacement = isset($data['replacement_id'])
            ? $model::approved()->findOrFail($data['replacement_id'])
            : $model::approved()->where('is_default', true)->first();

        // fish_id ist Pflicht – ohne Ersatz würden die Fänge ungültig
        if (! $replacement && $type === 'fish' && Catched::where('fish_id', $entry->id)->exists()) {
            throw ValidationException::withMessages(['replacement_id' => 'Bitte wähle eine Fischart, der die Fänge zugeordnet werden.']);
        }

        $hadCatches = Catched::where(self::CATCH_COLUMN[$type], $entry->id)->exists();
        $submitter = $entry->submitter;

        DB::transaction(function () use ($entry, $type, $replacement) {
            Catched::where(self::CATCH_COLUMN[$type], $entry->id)
                ->update([self::CATCH_COLUMN[$type] => $replacement?->id]);

            if ($type === 'fish') {
                $entry->lakes()->detach();
                $entry->rivers()->detach();
            } else {
                $entry->states()->detach();
                $entry->fish()->detach();
            }

            $entry->delete();
        });

        $this->notifySubmitter($entry, $type, [
            'approved' => false,
            'replacement' => $hadCatches ? $replacement?->name : null,
            'reason' => isset($data['reason']) ? trim($data['reason']) : null,
        ], $submitter);

        $target = $replacement && $hadCatches ? " Fänge wurden „{$replacement->name}“ zugeordnet." : '';

        return back()->with('success', "Vorschlag „{$entry->name}“ wurde abgelehnt.{$target}");
    }

    /**
     * Mail an den Einreichenden – entfällt, wenn sein Konto inzwischen gelöscht wurde.
     */
    private function notifySubmitter(Model $entry, string $type, array $review, ?User $submitter = null): void
    {
        $submitter ??= $entry->submitter;

        if (! $submitter) {
            return;
        }

        Mail::to($submitter->email)->queue(new SubmissionReviewedMail([
            'user_name' => $submitter->name,
            'label' => SubmissionController::LABELS[$type],
            'name' => $entry->name,
            'url' => null,
            'replacement' => null,
            'reason' => null,
            ...$review,
        ]));
    }

    private function wikiUrl(Model $entry, string $type): ?string
    {
        if ($type === 'fish') {
            return route('app.fish.show', $entry);
        }

        $state = $entry->states()->orderBy('name')->first();

        return $state ? route($type === 'lake' ? 'app.lakes.show' : 'app.rivers.show', [$state, $entry]) : null;
    }

    private function pendingEntry(string $type, int $id): Model
    {
        $model = SubmissionController::TYPES[$type] ?? abort(404);

        return $model::pending()->findOrFail($id);
    }
}
