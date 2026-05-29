<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reportable_type' => 'required|in:comment,price,bug',
            'reportable_id' => 'nullable|integer',
            'type' => 'required|in:inappropriate_comment,price_anomaly,bug',
            'description' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $morphMap = [
            'comment' => 'App\\Models\\Comment',
            'price' => 'App\\Models\\Price',
            'bug' => 'App\\Models\\Report',
        ];

        $report = Report::create([
            'user_id' => auth('api')->id(),
            'reportable_type' => $morphMap[$request->reportable_type] ?? $request->reportable_type,
            'reportable_id' => $request->reportable_id ?? 0,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Laporan berhasil dikirim', 'report' => $report], 201);
    }
}
