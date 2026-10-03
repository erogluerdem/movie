<?php

namespace App\Http\Controllers;

use App\Models\ContentRequest;
use App\Models\Movie;
use App\Models\TvShow;
use App\Models\User;
use App\Models\WatchHistory;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // 1. Calculate user stats
        $watchlistCount = Watchlist::where('user_id', $user->id)->count();
        $moviesWatchedCount = WatchHistory::where('user_id', $user->id)->where('media_type', 'movie')->count();
        $seriesWatchedCount = WatchHistory::where('user_id', $user->id)->where('media_type', 'tv')->count();
        $requestsCount = ContentRequest::where('user_id', $user->id)->count();

        // Approximate hours watched: 1.8 hours per movie history, 0.8 hours per episode history
        $watchTimeHours = round(($moviesWatchedCount * 1.8) + ($seriesWatchedCount * 0.8), 1);

        // 2. Continue Watching (Incomplete items)
        $continueItems = WatchHistory::where('user_id', $user->id)
            ->where('completed', false)
            ->orderByDesc('last_watched_at')
            ->take(6)
            ->get()
            ->map(function ($hist) {
                return $this->formatHistoryItem($hist);
            })
            ->filter()
            ->values();

        // 3. Recent Activity (Latest 6 items overall)
        $recentHistories = WatchHistory::where('user_id', $user->id)
            ->orderByDesc('last_watched_at')
            ->take(5)
            ->get()
            ->map(function ($hist) {
                return $this->formatHistoryItem($hist);
            })
            ->filter()
            ->values();

        // 4. Recommended for You (Popular movies/shows)
        $recommendations = Movie::where('is_trending', true)
            ->take(6)
            ->get();

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'watchTimeHours' => $watchTimeHours,
                'moviesWatchedCount' => $moviesWatchedCount,
                'seriesWatchedCount' => $seriesWatchedCount,
                'watchlistCount' => $watchlistCount,
                'requestsCount' => $requestsCount,
            ],
            'continueWatching' => $continueItems,
            'recentActivity' => $recentHistories,
            'recommendations' => $recommendations,
        ]);
    }

    public function watchlist(Request $request): Response
    {
        $user = $request->user();

        $watchlistEntries = Watchlist::where('user_id', $user->id)->orderByDesc('created_at')->get();
        $movieIds = $watchlistEntries->where('media_type', 'movie')->pluck('media_id');
        $tvIds = $watchlistEntries->where('media_type', 'tv')->pluck('media_id');

        $movies = Movie::whereIn('id', $movieIds)->get();
        $shows = TvShow::whereIn('id', $tvIds)->get();

        return Inertia::render('Dashboard/Watchlist', [
            'movies' => $movies,
            'shows' => $shows,
        ]);
    }

    public function history(Request $request): Response
    {
        $user = $request->user();

        $historyItems = WatchHistory::where('user_id', $user->id)
            ->orderByDesc('last_watched_at')
            ->paginate(20)
            ->through(function ($hist) {
                return $this->formatHistoryItem($hist);
            });

        return Inertia::render('Dashboard/History', [
            'history' => $historyItems,
        ]);
    }

    public function requests(Request $request): Response
    {
        $user = $request->user();

        $requests = ContentRequest::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('Dashboard/Requests', [
            'requests' => $requests,
        ]);
    }

    public function settings(Request $request): Response
    {
        $user = $request->user();

        $avatars = [
            'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=150&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=150&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop',
        ];

        return Inertia::render('Dashboard/Settings', [
            'user' => $user,
            'avatars' => $avatars,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'preferred_quality' => ['required', 'string', 'in:720p,1080p,4K'],
            'preferred_language' => ['required', 'string', 'max:10'],
            'autoplay_next' => ['required', 'boolean'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Streaming preferences saved!');
    }

    public function submitRequest(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:movie,tv'],
            'release_year' => ['nullable', 'string', 'max:4'],
            'user_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        ContentRequest::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'release_year' => $validated['release_year'] ?? null,
            'user_notes' => $validated['user_notes'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Content request submitted! Our team will review it shortly.');
    }

    public function deleteRequest(int $id, Request $request): RedirectResponse
    {
        $user = $request->user();

        ContentRequest::where('id', $id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', 'Request deleted.');
    }

    public function clearHistory(Request $request): RedirectResponse
    {
        $user = $request->user();

        WatchHistory::where('user_id', $user->id)->delete();

        return back()->with('success', 'Watch history cleared.');
    }

    public function removeHistoryItem(int $id, Request $request): RedirectResponse
    {
        $user = $request->user();

        WatchHistory::where('id', $id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', 'Item removed from watch history.');
    }

    public function trackProgress(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['status' => 'guest_ignored']);
        }

        $mediaType = $request->input('media_type');
        $mediaId = $request->input('media_id');
        $progress = (int) $request->input('progress_percent', 0);
        $sNum = $request->input('season_number');
        $eNum = $request->input('episode_number');

        $history = WatchHistory::updateOrCreate(
            [
                'user_id' => $user->id,
                'media_type' => $mediaType,
                'media_id' => $mediaId,
            ],
            [
                'season_number' => $sNum,
                'episode_number' => $eNum,
                'progress_percent' => min(100, max(0, $progress)),
                'completed' => $progress >= 92,
                'last_watched_at' => now(),
            ]
        );

        return response()->json(['status' => 'saved', 'id' => $history->id]);
    }

    private function formatHistoryItem(WatchHistory $hist): ?array
    {
        $item = null;
        $title = '';
        $slug = '';
        $poster = '';
        $backdrop = '';

        if ($hist->media_type === 'movie') {
            $item = Movie::find($hist->media_id);
            if (! $item) {
                return null;
            }
            $title = $item->title;
            $slug = $item->slug;
            $poster = $item->poster_url ?: $item->poster_path;
            $backdrop = $item->backdrop_url ?: $item->backdrop_path;
            $playUrl = "/movie/{$slug}";
            $subtitle = 'Movie · '.($item->release_date ? substr($item->release_date, 0, 4) : '2025');
        } else {
            $item = TvShow::find($hist->media_id);
            if (! $item) {
                return null;
            }
            $title = $item->title;
            $slug = $item->slug;
            $poster = $item->poster_url ?: $item->poster_path;
            $backdrop = $item->backdrop_url ?: $item->backdrop_path;
            $s = $hist->season_number ?: 1;
            $e = $hist->episode_number ?: 1;
            $playUrl = "/tv-show/{$slug}?season={$s}&episode={$e}";
            $subtitle = "Season {$s}, Episode {$e}";
        }

        return [
            'id' => $hist->id,
            'media_type' => $hist->media_type,
            'media_id' => $hist->media_id,
            'title' => $title,
            'subtitle' => $subtitle,
            'slug' => $slug,
            'poster' => $poster,
            'backdrop' => $backdrop ?: $poster,
            'progress_percent' => $hist->progress_percent,
            'completed' => $hist->completed,
            'playUrl' => $playUrl,
            'last_watched_at' => $hist->last_watched_at ? $hist->last_watched_at->diffForHumans() : 'Recently',
        ];
    }
}
