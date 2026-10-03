<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\TvShow;
use App\Models\WatchHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminAnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $daysCount = (int) $request->input('days', 14);
        if (! in_array($daysCount, [7, 14, 30])) {
            $daysCount = 14;
        }

        // 1. Daily Watch Trend for the selected period
        $chartDates = [];
        $chartCounts = [];
        for ($i = $daysCount - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = WatchHistory::whereDate('created_at', $date->toDateString())->count();
            $chartDates[] = $date->format('M d');
            $chartCounts[] = $count;
        }

        // 2. Top 10 Most Watched Movies
        $topMovieHistories = WatchHistory::where('media_type', 'movie')
            ->selectRaw('media_id, COUNT(*) as views')
            ->groupBy('media_id')
            ->orderByDesc('views')
            ->take(10)
            ->get();

        $topMovies = $topMovieHistories->map(function ($item) {
            $movie = Movie::find($item->media_id);

            return [
                'id' => $item->media_id,
                'title' => $movie?->title ?? 'Bilinmeyen Film',
                'poster_url' => $movie?->poster_url,
                'slug' => $movie?->slug,
                'views' => $item->views,
                'rating' => $movie?->vote_average,
            ];
        });

        // 3. Top 10 Most Watched TV Shows
        $topShowHistories = WatchHistory::where('media_type', 'tv')
            ->selectRaw('media_id, COUNT(*) as views')
            ->groupBy('media_id')
            ->orderByDesc('views')
            ->take(10)
            ->get();

        $topShows = $topShowHistories->map(function ($item) {
            $show = TvShow::find($item->media_id);

            return [
                'id' => $item->media_id,
                'title' => $show?->title ?? 'Bilinmeyen Dizi',
                'poster_url' => $show?->poster_url,
                'slug' => $show?->slug,
                'views' => $item->views,
                'rating' => $show?->vote_average,
            ];
        });

        // 4. Platform-wide stats
        $totalViews = WatchHistory::count();
        $completedViews = WatchHistory::where('completed', true)->count();
        $completionRate = $totalViews > 0 ? round(($completedViews / $totalViews) * 100) : 0;
        $avgProgress = round((float) (WatchHistory::avg('progress_percent') ?: 0));
        $uniqueViewers = WatchHistory::distinct('user_id')->count('user_id');

        return Inertia::render('Admin/Analytics/Index', [
            'days' => $daysCount,
            'chart' => [
                'labels' => $chartDates,
                'data' => $chartCounts,
            ],
            'topMovies' => $topMovies,
            'topShows' => $topShows,
            'stats' => [
                'totalViews' => $totalViews,
                'completedViews' => $completedViews,
                'completionRate' => $completionRate,
                'avgProgress' => $avgProgress,
                'uniqueViewers' => $uniqueViewers,
            ],
        ]);
    }
}
