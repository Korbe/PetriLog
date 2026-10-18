<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CorrectionReviewedMail;
use App\Models\Correction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Prüfung der Fehlermeldungen aus dem Wiki (Admins und Editoren).
 */
class CorrectionAdminController extends Controller
{
    public function index()
    {
        $map = fn(Correction $c) => [
            'id' => $c->id,
            'type' => $c->typeKey(),
            'type_label' => Correction::TYPE_LABELS[$c->typeKey()],
            'entry' => $c->correctable ? ['id' => $c->correctable->id, 'name' => $c->correctable->name] : null,
            'wiki_url' => $c->wikiUrl(),
            'category' => $c->categoryLabel(),
            'message' => $c->message,
            'source' => $c->source,
            'status' => $c->status,
            'user' => $c->user?->only(['id', 'name', 'email']),
            'reviewer' => $c->reviewer?->name,
            'review_note' => $c->review_note,
            'created_at' => $c->created_at,
            'reviewed_at' => $c->reviewed_at,
        ];

        return Inertia::render('Admin/Corrections/Index', [
            'open' => Correction::open()->with(['correctable', 'user'])->oldest()->get()->map($map),
            // Zuletzt bearbeitete zum Nachschlagen
            'recent' => Correction::where('status', '!=', Correction::STATUS_OPEN)
                ->with(['correctable', 'user', 'reviewer'])->latest('reviewed_at')->limit(20)->get()->map($map),
        ]);
    }

    public function resolve(Request $request, Correction $correction)
    {
        if ($correction->status !== Correction::STATUS_OPEN) {
            return back()->with('error', 'Diese Meldung wurde bereits bearbeitet.');
        }

        $data = $request->validate([
            'status' => ['required', Rule::in([Correction::STATUS_DONE, Correction::STATUS_REJECTED])],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $correction->forceFill([
            'status' => $data['status'],
            'review_note' => $data['note'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ])->save();

        if ($correction->user?->email) {
            Mail::to($correction->user)->queue(new CorrectionReviewedMail($correction));
        }

        return back()->with('success', $data['status'] === Correction::STATUS_DONE
            ? 'Als erledigt markiert – der User wurde informiert.'
            : 'Abgelehnt – der User wurde informiert.');
    }
}
