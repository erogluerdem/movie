<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentRequest;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\Review;
use App\Models\SportMatch;
use App\Models\TvShow;
use App\Models\User;
use App\Models\WatchHistory;
use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(): Response
    {
        $totalMovies = Movie::count();
        $trendingMovies = Movie::where('is_trending', true)->count();
        $totalShows = TvShow::count();
        $totalEpisodes = Episode::count();
        $animeCount = TvShow::where('is_anime', true)->count();
        $totalSports = SportMatch::count();
        $liveSports = SportMatch::where('is_live', true)->count();
        $totalUsers = User::count();
        $adminCount = User::where('role', 'admin')->count();
        $pendingRequests = ContentRequest::where('status', 'pending')->count();
        $totalRequests = ContentRequest::count();
        $totalStreams = WatchHistory::count();

        // Community review statistics
        $totalReviews = Review::count();
        $avgRating = round((float) (Review::avg('rating') ?: 0), 1);
        $spoilerReviews = Review::where('has_spoiler', true)->count();

        // Recent content requests for instant actions
        $recentRequests = ContentRequest::with('user')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Recent registered users
        $recentUsers = User::orderByDesc('created_at')
            ->take(5)
            ->get(['id', 'name', 'email', 'avatar', 'role', 'created_at']);

        // Recent community reviews for dashboard stream
        $recentReviews = Review::with(['user', 'reviewable'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // System information & TMDB health
        $tmdbConfigured = ! empty(config('services.tmdb.read_token')) || ! empty(config('services.tmdb.api_key'));
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'db_driver' => config('database.default'),
            'server_time' => now()->toDateTimeString(),
            'tmdb_configured' => $tmdbConfigured,
        ];

        return Inertia::render('Admin/Index', [
            'stats' => [
                'totalMovies' => $totalMovies,
                'trendingMovies' => $trendingMovies,
                'totalShows' => $totalShows,
                'totalEpisodes' => $totalEpisodes,
                'animeCount' => $animeCount,
                'totalSports' => $totalSports,
                'liveSports' => $liveSports,
                'totalUsers' => $totalUsers,
                'adminCount' => $adminCount,
                'pendingRequests' => $pendingRequests,
                'totalRequests' => $totalRequests,
                'totalStreams' => $totalStreams,
                'totalReviews' => $totalReviews,
                'avgRating' => $avgRating,
                'spoilerReviews' => $spoilerReviews,
            ],
            'recentRequests' => $recentRequests,
            'recentUsers' => $recentUsers,
            'recentReviews' => $recentReviews,
            'systemInfo' => $systemInfo,
        ]);
    }

    public function clearCache(): RedirectResponse
    {
        try {
            Artisan::call('optimize:clear');

            return back()->with('success', 'Application cache cleared successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error clearing cache: '.$e->getMessage());
        }
    }

    public function fetchTmdb(Request $request, TmdbService $tmdbService): JsonResponse
    {
        $type = $request->input('type', 'movie');
        $tmdbId = trim((string) $request->input('tmdb_id', ''));

        if (empty($tmdbId)) {
            return response()->json(['status' => 'error', 'message' => 'TMDB ID is required.'], 422);
        }

        if ($type === 'tv') {
            $result = $tmdbService->fetchTv($tmdbId);
        } else {
            $result = $tmdbService->fetchMovie($tmdbId);
        }

        return response()->json($result);
    }
}
