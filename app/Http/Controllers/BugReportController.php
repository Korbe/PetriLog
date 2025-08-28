<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\BugReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\BugReportMail;


class BugReportController extends Controller
{
    public function create()
    {
        return Inertia::render('BugReport/Create');
    }

    // Store new bug report
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

        Mail::to('info@korbitsch.at')->send(new BugReportMail([
            'bug' => $bugReport,
            'user' => $user,
        ]));

        return redirect()->route('bug-report.create')->with('success', 'Fehlerbericht erfolgreich eingetragen.');
    }
}
