<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Mail\NewsletterMail;
use Illuminate\Http\Request;
use App\Jobs\SendNewsletterMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\NewsletterRequest;
use Illuminate\Database\Eloquent\Builder;

class NewsletterAdminController extends Controller
{
    public function index()
    {
        $verified = User::query()->whereNotNull('email')->whereNotNull('email_verified_at');

        return inertia('Admin/Newsletter/Index', [
            'stats' => [
                'subscribers' => (clone $verified)->where('newsletter_opt_out', false)->count(),
                'optedOut' => (clone $verified)->where('newsletter_opt_out', true)->count(),
                'all' => (clone $verified)->count(),
                // Ohne bestätigte E-Mail-Adresse geht nie etwas raus
                'unverified' => User::whereNull('email_verified_at')->count(),
            ],
        ]);
    }

    /**
     * Empfänger: normaler Newsletter nur an angemeldete, eine Service-Mitteilung an alle bestätigten Adressen.
     */
    private function recipients(bool $service): Builder
    {
        return User::query()
            ->whereNotNull('email')
            ->whereNotNull('email_verified_at')
            ->when(! $service, fn(Builder $query) => $query->where('newsletter_opt_out', false));
    }

    public function send(NewsletterRequest $request)
    {
        $validated = $request->validated();
        $service = $request->isServiceMessage();

        $sentCount = 0;

        $this->recipients($service)->chunkById(100, function ($users) use ($validated, $service, &$sentCount) {
            foreach ($users as $user) {
                SendNewsletterMail::dispatch($user, $validated['subject'], $validated['content'], $service);
                $sentCount++;
            }
        });

        // Belegexemplar
        Mail::to('info@petrilog.com')->send(new NewsletterMail($validated['subject'], $validated['content'], null, $service));

        $what = $service ? 'Service-Mitteilung' : 'Newsletter';

        return redirect()
            ->route('admin.newsletter.index')
            ->with('success', "{$what} an {$sentCount} User verschickt 🎉");
    }

    public function sendTest(NewsletterRequest $request)
    {
        $data = $request->validated();

        SendNewsletterMail::dispatch(Auth::user(), $data['subject'], $data['content'], $request->isServiceMessage());

        return redirect()
            ->route('admin.newsletter.index')
            ->with('success', 'Test-Mail wurde an dich gesendet.');
    }

    /**
     * Vorschau: genau die E-Mail, die ein Empfänger bekommt (mit dem eigenen Konto als Beispiel).
     */
    public function preview(Request $request)
    {
        $data = $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'audience' => ['nullable', 'in:subscribers,all'],
        ]);

        $mail = new NewsletterMail(
            $data['subject'] ?: 'Betreff',
            $data['content'] ?: '<p><em>Hier erscheint dein Text …</em></p>',
            $request->user(),
            ($data['audience'] ?? 'subscribers') === 'all',
        );

        return response($mail->render())
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('X-Robots-Tag', 'noindex');
    }

    public function unsubscribe(User $user)
    {
        $user->update([
            'newsletter_opt_out' => true,
        ]);

        return inertia('Public/Newsletter/Unsubscribed');
    }
}
