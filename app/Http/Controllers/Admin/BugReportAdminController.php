<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\BugReport;
use App\Http\Controllers\Controller;

class BugReportAdminController extends Controller
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

    public function destroy(BugReport $report)
    {
        $report->delete();

        return redirect()->route('admin.bugreports.index')->with('success', 'Bug-Report gelöscht.');
    }
}
