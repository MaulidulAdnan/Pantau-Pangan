<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with('user', 'reportable');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->latest()->paginate(15);
        return response()->json($reports);
    }

    public function resolve($reportId)
    {
        $report = Report::findOrFail($reportId);
        $report->update(['status' => 'resolved']);
        return response()->json(['message' => 'Laporan telah diselesaikan']);
    }

    public function review($reportId)
    {
        $report = Report::findOrFail($reportId);
        $report->update(['status' => 'reviewed']);
        return response()->json(['message' => 'Laporan sedang ditinjau']);
    }
}
