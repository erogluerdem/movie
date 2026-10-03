<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Movie;
use App\Models\TvShow;
use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminTmdbBulkController extends Controller
{
    public function index(Request $request, TmdbService $tmdbService): Response
    {
        $feed = $request->input('feed', 'popular');
        $type = $request->input('type', 'movie');
        $page = (int) $request->input('page', 1);

        $feedResult = $tmdbService->fetchFeed($feed, $type, $page);
        $results = $feedResult['results'] ?? [];

        // Check which items are already imported in database
        $tmdbIds = array_column($results, 'tmdb_id');
        if ($type === 'tv') {
            $existingIds = TvShow::whereIn('tmdb_id', $tmdbIds)->pluck('tmdb_id')->map(fn ($id) => (int) $id)->toArray();
        } else {
            $existingIds = Movie::whereIn('tmdb_id', $tmdbIds)->pluck('tmdb_id')->map(fn ($id) => (int) $id)->toArray();
        }

        foreach ($results as &$item) {
            $item['is_imported'] = in_array((int) $item['tmdb_id'], $existingIds, true);
        }

        return Inertia::render('Admin/Tmdb/Bulk', [
            'feed' => $feed,
            'type' => $type,
            'page' => $page,
            'totalPages' => $feedResult['total_pages'] ?? 1,
            'items' => $results,
            'configured' => $tmdbService->isConfigured(),
            'error' => $feedResult['status'] === 'error' ? $feedResult['message'] : null,
        ]);
    }

    public function importBatch(Request $request, TmdbService $tmdbService): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:movie,tv'],
            'tmdb_ids' => ['required', 'array', 'min:1'],
            'tmdb_ids.*' => ['required', 'integer'],
        ]);

        $res = $tmdbService->bulkImport($validated['type'], $validated['tmdb_ids']);

        AuditLog::log(
            'bulk_import',
            "Bulk imported {$res['imported_count']} {$validated['type']}(s) from TMDB."
        );

        if ($request->wantsJson()) {
            return response()->json($res);
        }

        $message = "Successfully imported {$res['imported_count']} {$validated['type']}(s)!";
        if ($res['failed_count'] > 0) {
            $message .= " ({$res['failed_count']} failed or already exist).";
        }

        return back()->with('success', $message);
    }
}
