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
    public function index()
    {
        $reports = BugReport::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/BugReports/Index', [
            'reports' => $reports->map(function ($report) {
                return [
                    'id' => $report->id,
                    'title' => $report->title,
                    'category' => $report->category,
                    'user' => $report->user ? $report->user->name : 'Unbekannt',
                    'created_at' => $report->created_at->format('Y-m-d H:i'),
                    'url' => route('admin.bugreports.show', $report->id),
                ];
            }),
        ]);
    }

    public function show(BugReport $bugreport)
    {
        return Inertia::render('Admin/BugReports/Show', [
            'report' => [
                'id' => $bugreport->id,
                'title' => $bugreport->title,
                'description' => $bugreport->description,
                'category' => $bugreport->category,
                'steps' => $bugreport->steps,
                'url' => $bugreport->url,
                'user' => $bugreport->user ? $bugreport->user->name : 'Unbekannt',
                'created_at' => $bugreport->created_at->format('Y-m-d H:i'),
            ],
        ]);
    }

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

    public function destroy(BugReport $report)
    {
        $report->delete();

        return redirect()->route('admin.bugreports.index')->with('success', 'Bug-Report gelöscht.');
    }
}
