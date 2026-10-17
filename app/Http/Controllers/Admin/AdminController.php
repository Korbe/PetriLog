<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\AdminStatistics;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'integer', Rule::in(AdminStatistics::PERIODS)],
        ]);

        $stats = (new AdminStatistics((int) ($validated['period'] ?? 30)))->toArray();

        return Inertia::render('Admin/Index', [
            'stats' => $stats,
            'pendingSubmissions' => $stats['totals']['pendingSubmissions'],
        ]);
    }
}
