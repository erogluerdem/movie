<?php

namespace App\Http\Controllers;

use App\Models\StreamReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StreamReportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'media_type' => ['required', 'string', 'in:movie,tv,sport'],
            'media_id' => ['required', 'integer'],
            'server_name' => ['nullable', 'string', 'max:100'],
            'issue_type' => ['required', 'string', 'in:dead_link,audio_desync,wrong_video,buffering,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $report = StreamReport::create([
            'user_id' => auth()->id(),
            'media_type' => $validated['media_type'],
            'media_id' => $validated['media_id'],
            'server_name' => $validated['server_name'] ?? null,
            'issue_type' => $validated['issue_type'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sorun bildiriminiz başarıyla iletildi. Teşekkür ederiz!',
            'report_id' => $report->id,
        ]);
    }
}
