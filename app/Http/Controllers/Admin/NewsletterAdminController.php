<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Mail\NewsletterMail;
use Illuminate\Http\Request;
use App\Jobs\SendNewsletterMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class NewsletterAdminController extends Controller
{
    public function index()
    {
        return inertia('Admin/Newsletter/Index');
    }

    protected function validateNewsletter(Request $request): array
    {
        return $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
    }

    public function send(Request $request)
    {
        $validated = $this->validateNewsletter($request);

        $sentCount = 0;

        User::query()
            ->whereNotNull('email')
            ->whereNotNull('email_verified_at')
            ->where('newsletter_opt_out', false)
            ->chunk(100, function ($users) use ($validated, &$sentCount) {
                foreach ($users as $user) {
                    SendNewsletterMail::dispatch(
                        $user,
                        $validated['subject'],
                        $validated['content']
                    );

                    $sentCount++;
                }
            });

        Mail::to('info@petrilog.com')->send(
            new NewsletterMail(
                $validated['subject'],
                $validated['content'],
                null // System-Mail
            )
        );

        return redirect()
            ->route('admin.newsletter.index')
            ->with(
                'success',
                "Newsletter an {$sentCount} User erfolgreich verschickt 🎉"
            );
    }

    public function sendTest(Request $request)
    {
        $data = $this->validateNewsletter($request);

        $user = Auth::user();

        SendNewsletterMail::dispatch(
            $user,
            $data['subject'],
            $data['content']
        );

        return redirect()
            ->route('admin.newsletter.index')
            ->with('success', 'Test-Mail wurde an dich gesendet.');
    }

    public function unsubscribe(Request $request, User $user)
    {
        $user->update([
            'newsletter_opt_out' => true,
        ]);

        return inertia('Public/Newsletter/Unsubscribed', [
            'email' => $user->email,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'newsletter_opt_out' => 'required|boolean',
        ]);

        $user = Auth::user();
        $user->newsletter_opt_out = $validated['newsletter_opt_out'];
        $user->save();

        return redirect()
            ->back()
            ->with('success', 'Deine Newsletter-Einstellungen wurden aktualisiert.');
    }
}
