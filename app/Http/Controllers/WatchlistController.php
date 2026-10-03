<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\TvShow;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WatchlistController extends Controller
{
    public function index(): Response
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        $items = Watchlist::where(function ($q) use ($userId, $sessionId) {
            if ($userId) {
                $q->where('user_id', $userId);
            } else {
                $q->where('session_id', $sessionId);
            }
        })->get();

        $movieIds = $items->where('media_type', 'movie')->pluck('media_id');
        $tvIds = $items->where('media_type', 'tv')->pluck('media_id');

        $movies = Movie::whereIn('id', $movieIds)->get();
        $shows = TvShow::whereIn('id', $tvIds)->get();

        return Inertia::render('Watchlist', [
            'movies' => $movies,
            'shows' => $shows,
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $mediaType = $request->input('media_type');
        $mediaId = (int) $request->input('media_id');
        $userId = Auth::id();
        $sessionId = session()->getId();

        $query = Watchlist::where('media_type', $mediaType)
            ->where('media_id', $mediaId);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $existing = $query->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['status' => 'removed', 'in_watchlist' => false]);
        }

        Watchlist::create([
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
            'media_type' => $mediaType,
            'media_id' => $mediaId,
        ]);

        return response()->json(['status' => 'added', 'in_watchlist' => true]);
    }

    public function check(Request $request): JsonResponse
    {
        $mediaType = $request->input('media_type');
        $mediaId = (int) $request->input('media_id');
        $userId = Auth::id();
        $sessionId = session()->getId();

        $inWatchlist = Watchlist::where('media_type', $mediaType)
            ->where('media_id', $mediaId)
            ->where(function ($q) use ($userId, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('session_id', $sessionId);
                }
            })->exists();

        return response()->json(['in_watchlist' => $inWatchlist]);
    }
}
