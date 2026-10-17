<?php

namespace App\Http\Controllers;

use App\Mail\SubmissionAdminMail;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\River;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * User schlagen fehlende Fischarten, Seen und Flüsse vor. Der Vorschlag ist sofort für den eigenen
 * Fang nutzbar und wird nach Freigabe durch einen Admin für alle sichtbar.
 */
class SubmissionController extends Controller
{
    // Schutz vor Spam: so viele offene Vorschläge darf ein User gleichzeitig haben
    public const MAX_PENDING_PER_USER = 10;

    // Gleiche Adresse wie für neue Tickets
    public const ADMIN_EMAIL = 'info@petrilog.com';

    public const LABELS = [
        'fish' => 'Fischart',
        'lake' => 'See',
        'river' => 'Fluss',
    ];

    public const TYPES = [
        'fish' => Fish::class,
        'lake' => Lake::class,
        'river' => River::class,
    ];

    public function store(Request $request, string $type): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $model = self::TYPES[$type] ?? abort(404);
        $isWater = $type !== 'fish';

        $data = $request->validate([
            'name' => 'required|string|min:2|max:80',
            'desc' => 'nullable|string|max:2000',
            'states' => $isWater ? 'required|array|min:1' : 'prohibited',
            'states.*' => 'integer|exists:states,id',
            'fishing_rights' => $isWater ? 'nullable|string|max:500' : 'prohibited',
            'ticket_sales' => $isWater ? 'nullable|string|max:500' : 'prohibited',
        ], [
            'name.required' => 'Bitte gib einen Namen an.',
            'states.required' => 'Bitte wähle mindestens ein Bundesland.',
        ]);

        $name = trim(preg_replace('/\s+/', ' ', $data['name']));
        $this->ensureNotDuplicate($model, $name, $user);

        if ($this->pendingCount($user) >= self::MAX_PENDING_PER_USER) {
            throw ValidationException::withMessages([
                'name' => 'Du hast bereits ' . self::MAX_PENDING_PER_USER . ' Vorschläge in Prüfung. Bitte warte, bis diese bearbeitet sind.',
            ]);
        }

        $entry = DB::transaction(function () use ($model, $data, $name, $user, $isWater) {
            /** @var Model $entry */
            $entry = new $model();
            $entry->fill([
                'name' => $name,
                'desc' => $this->plainTextAsHtml($data['desc'] ?? null),
                ...($isWater ? [
                    'fishing_rights' => $this->plainTextAsHtml($data['fishing_rights'] ?? null),
                    'ticket_sales' => $this->plainTextAsHtml($data['ticket_sales'] ?? null),
                ] : []),
            ]);
            $entry->forceFill(['is_approved' => false, 'submitted_by' => $user->id])->save();

            if ($isWater) {
                $entry->states()->sync($data['states']);
            }

            return $entry;
        });

        Mail::to(self::ADMIN_EMAIL)->queue(new SubmissionAdminMail([
            'label' => self::LABELS[$type],
            'name' => $entry->name,
            'desc' => $entry->desc,
            'fishing_rights' => $entry->fishing_rights ?? null,
            'ticket_sales' => $entry->ticket_sales ?? null,
            'states' => $isWater ? $entry->states()->orderBy('name')->pluck('name')->all() : [],
            'submitter_name' => $user->name,
            'submitter_email' => $user->email,
            'created_at' => now('Europe/Vienna')->format('d.m.Y, H:i') . ' Uhr',
        ]));

        return response()->json([
            'id' => $entry->id,
            'name' => $entry->name,
            'message' => "„{$entry->name}“ wurde zur Prüfung eingereicht und ist für deine Fänge sofort verfügbar.",
        ], 201);
    }

    /**
     * Wiki-Texte werden per v-html angezeigt – Eingaben von Usern daher nur als escapter Klartext übernehmen.
     */
    private function plainTextAsHtml(?string $text): ?string
    {
        $text = trim((string) $text);

        return $text === '' ? null : '<p>' . nl2br(e($text), false) . '</p>';
    }

    private function ensureNotDuplicate(string $model, string $name, User $user): void
    {
        $existing = $model::query()
            ->visibleTo($user)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'name' => $existing->is_approved
                    ? "„{$existing->name}“ gibt es bereits – du findest den Eintrag in der Auswahlliste."
                    : "Du hast „{$existing->name}“ bereits vorgeschlagen.",
            ]);
        }
    }

    private function pendingCount(User $user): int
    {
        return collect(self::TYPES)->sum(fn(string $model) => $model::pending()->where('submitted_by', $user->id)->count());
    }
}
