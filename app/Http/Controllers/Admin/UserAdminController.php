<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UserAdminController extends Controller
{
    public function index()
    {
        // Letzte Aktivität aus der Session-Tabelle (SESSION_DRIVER=database)
        $lastActivity = DB::table('sessions')
            ->whereNotNull('user_id')
            ->selectRaw('user_id, MAX(last_activity) as last_activity')
            ->groupBy('user_id')
            ->pluck('last_activity', 'user_id');

        $users = User::with(['state', 'subscriptions'])
            ->withCount(['catched', 'spots'])
            ->withMax('catched', 'created_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (User $user) use ($lastActivity) {
                $subscription = $user->subscription('default');
                $subscribed = $user->subscribed();
                $canceled = $subscription?->canceled() ?? false;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'tel' => $user->tel,
                    'subscribed' => $subscribed,
                    'subscription_expires_at' => $subscription?->ends_at,
                    'canceled' => $canceled,
                    // plus = läuft, canceled = gekündigt aber noch aktiv, ended = abgelaufen, free = nie abonniert
                    'plan' => match (true) {
                        $subscribed && ! $canceled => 'plus',
                        $subscribed && $canceled => 'canceled',
                        $subscription !== null => 'ended',
                        default => 'free',
                    },
                    'state' => $user->state?->name,
                    'created_at' => $user->created_at,
                    'is_admin' => $user->isAdmin(),
                    'verified' => $user->hasVerifiedEmail(),
                    'newsletter' => ! $user->newsletter_opt_out,
                    'catches_count' => $user->catched_count,
                    'spots_count' => $user->spots_count,
                    'last_catch_at' => $user->catched_max_created_at ? Carbon::parse($user->catched_max_created_at) : null,
                    'last_active_at' => isset($lastActivity[$user->id]) ? Carbon::createFromTimestamp($lastActivity[$user->id]) : null,
                ];
            });

        return Inertia::render('Admin/User/Index', [
            'users' => $users,
        ]);
    }
}
