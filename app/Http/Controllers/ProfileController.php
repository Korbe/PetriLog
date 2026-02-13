<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\State;
use Illuminate\Http\Request;
use Laravel\Jetstream\Agent;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $states = State::all(['id', 'name']);

        return Inertia::render('Profile/Show', [
            'states' => $states,
            'sessions' => $this->sessions($request)->all(),
            'confirmsTwoFactorAuthentication' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
        ]);
    }


    public function sessions(Request $request)
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        return collect(
            DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->orderBy('last_activity', 'desc')
                ->get()
        )->map(function ($session) use ($request) {
            $agent = $this->createAgent($session);

            return (object) [
                'agent' => [
                    'is_desktop' => $agent->isDesktop(),
                    'platform' => $agent->platform(),
                    'browser' => $agent->browser(),
                ],
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === $request->session()->getId(),
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ];
        });
    }

    /**
     * Create a new agent instance from the given session.
     *
     * @param  mixed  $session
     * @return \Laravel\Jetstream\Agent
     */
    protected function createAgent($session)
    {
        return tap(new Agent(), fn($agent) => $agent->setUserAgent($session->user_agent));
    }


    public function updateNewsletterPreference(Request $request)
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

    public function updateState(Request $request)
    {
        $validated = $request->validate([
            'state_id' => 'required|exists:states,id',
        ]);

        $user = Auth::user();
        $user->state_id = $validated['state_id'];
        $user->save();

        return redirect()
            ->back()
            ->with('success', 'Deine Bundesland-Einstellung wurden aktualisiert.');
    }
}
