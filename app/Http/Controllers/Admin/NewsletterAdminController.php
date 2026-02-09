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

class NewsletterAdminController extends Controller
{
    public function index()
    {
        return inertia('Admin/Newsletter/Index');
    }

    public function send(NewsletterRequest $request)
    {
        $validated = $request->validated();

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

    public function sendTest(NewsletterRequest $request)
    {
        $data = $request->validated();

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

    public function unsubscribe(User $user)
    {
        $user->update([
            'newsletter_opt_out' => true,
        ]);

        return inertia('Public/Newsletter/Unsubscribed');
    }
}
