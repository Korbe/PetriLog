<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\BugReportMail;
use App\Models\BugReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;


class BugReportController extends Controller
{
    public function create()
    {
        return Inertia::render('BugReport/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'steps' => 'nullable|string',
            'url' => 'nullable|string',
            'category' => 'nullable|string|in:ui,performance,data_issue,other',
        ]);

        $user = Auth::user();

        $data['user_id'] = $user->id;

        $bugReport = BugReport::create($data);

        Mail::to('info@petrilog.com')->queue(new BugReportMail([
            'bug' => $bugReport,
            'user' => $user,
        ]));

        return redirect()->route('app.bug-report.create')->with('success', 'Fehlerbericht erfolgreich eingetragen.');
    }
}
