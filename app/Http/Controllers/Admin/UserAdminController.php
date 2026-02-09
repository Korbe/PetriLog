<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class UserAdminController extends Controller
{
    public function index()
    {
        $users = User::with('state')
            ->select('id', 'name', 'email', 'created_at', 'is_admin', 'state_id')
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'subscribed' => $user->subscribed(),
                'subscription_expires_at' => optional(
                    $user->subscription('default')
                )->ends_at,
                'canceled' => $user->subscription('default')?->canceled() ?? false,
                'state' => $user->state?->name,
                'created_at' => $user->created_at,
                'is_admin' => $user->isAdmin(),
            ]);

        return Inertia::render('Admin/User/Index', [
            'users' => $users,
        ]);
    }
}
