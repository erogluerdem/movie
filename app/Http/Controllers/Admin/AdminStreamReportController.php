<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\StreamReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminStreamReportController extends Controller
{
    public function index(Request $request): Response
    {
        $query = StreamReport::with('user:id,name,email');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($issueType = $request->input('issue_type')) {
            $query->where('issue_type', $issueType);
        }

        $reports = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $statusCounts = [
            'all' => StreamReport::count(),
            'pending' => StreamReport::where('status', 'pending')->count(),
            'investigating' => StreamReport::where('status', 'investigating')->count(),
            'resolved' => StreamReport::where('status', 'resolved')->count(),
            'dismissed' => StreamReport::where('status', 'dismissed')->count(),
        ];

        return Inertia::render('Admin/Reports/Index', [
            'reports' => $reports,
            'statusCounts' => $statusCounts,
            'filters' => $request->only(['status', 'issue_type']),
        ]);
    }

    public function updateStatus(Request $request, StreamReport $streamReport): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,investigating,resolved,dismissed'],
        ]);

        $oldStatus = $streamReport->status;
        $streamReport->update(['status' => $validated['status']]);

        AuditLog::log(
            'update_report_status',
            "Stream report #{$streamReport->id} status changed from {$oldStatus} to {$validated['status']}",
            $streamReport
        );

        return back()->with('success', 'Rapor durumu güncellendi.');
    }

    public function destroy(StreamReport $streamReport): RedirectResponse
    {
        $id = $streamReport->id;
        $streamReport->delete();

        AuditLog::log('delete_report', "Stream report #{$id} deleted");

        return back()->with('success', 'Rapor silindi.');
    }
}
