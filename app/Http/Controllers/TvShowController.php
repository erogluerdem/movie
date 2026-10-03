<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\Movie;
use App\Models\TvShow;
use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TvShowController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TvShow::query()->where('is_anime', false);

        if ($request->filled('genre')) {
            $query->whereJsonContains('genres', $request->genre);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('year')) {
            $query->where('first_air_date', 'like', $request->year.'%');
        }

        if ($request->filled('min_rating')) {
            $query->where('vote_average', '>=', (float) $request->min_rating);
        }

        if ($request->filled('year_from')) {
            $query->where('first_air_date', '>=', $request->year_from.'-01-01');
        }

        if ($request->filled('year_to')) {
            $query->where('first_air_date', '<=', $request->year_to.'-12-31');
        }

        $sortBy = $request->get('sort', 'popular');
        if ($sortBy === 'rating') {
            $query->orderByDesc('vote_average');
        } elseif ($sortBy === 'newest') {
            $query->orderByDesc('first_air_date');
        } else {
            $query->orderByDesc('is_trending')->orderByDesc('vote_average');
        }

        $shows = $query->paginate(24)->withQueryString();

        $lead = TvShow::where('slug', 'like', '%fallout%')->first() ?? TvShow::first();

        $preferredSlugs = ['high-potential-2024', 'solo-leveling-2024', 'landman-2024'];
        $stack = TvShow::whereIn('slug', $preferredSlugs)->get()->sortBy(function ($item) use ($preferredSlugs) {
            return array_search($item->slug, $preferredSlugs);
        })->values();
        if ($stack->count() < 3) {
            $extra = TvShow::where('id', '!=', $lead?->id)->whereNotIn('id', $stack->pluck('id'))->take(3 - $stack->count())->get();
            $stack = $stack->merge($extra);
        }

        $allGenres = ['Mystery', 'Thriller', 'Animation', 'Comedy', 'Adventure', 'Family', 'Action', 'Crime', 'Drama', 'Science Fiction', 'Fantasy', 'Romance', 'History', 'Horror', 'War', 'Music', 'Action & Adventure', 'Sci-Fi & Fantasy', 'War & Politics', 'Talk', 'Soap', 'Reality'];

        return Inertia::render('TvShows/Index', [
            'shows' => $shows,
            'lead' => $lead,
            'stack' => $stack,
            'filters' => $request->only(['genre', 'search', 'year', 'sort', 'min_rating', 'year_from', 'year_to']),
            'genres' => $allGenres,
        ]);
    }

    public function anime(Request $request): Response
    {
        $query = TvShow::query()->where('is_anime', true);

        if ($request->filled('genre')) {
            $query->whereJsonContains('genres', $request->genre);
        }

        $search = $request->get('q', $request->get('search'));
        if ($search) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        $sortBy = $request->get('sort', 'popular');
        if ($sortBy === 'rating') {
            $query->orderByDesc('vote_average');
        } else {
            $query->orderByDesc('is_trending')->orderByDesc('vote_average');
        }

        $shows = $query->paginate(24)->withQueryString();

        $lead = TvShow::where('is_anime', true)->where('slug', 'like', '%solo-leveling%')->first()
            ?? TvShow::where('is_anime', true)->first();

        $trendingSide = TvShow::where('is_anime', true)->where('id', '!=', $lead?->id)->take(3)->get();

        $animeGenres = ['Action', 'Adventure', 'Animation', 'Comedy', 'Drama', 'Fantasy', 'Horror', 'Mystery', 'Sci-Fi'];

        return Inertia::render('Anime/Index', [
            'shows' => $shows,
            'lead' => $lead,
            'trendingSide' => $trendingSide,
            'filters' => $request->only(['genre', 'search', 'q', 'sort']),
            'genres' => $animeGenres,
        ]);
    }

    public function show(string $slug, Request $request, TmdbService $tmdbService): Response|RedirectResponse
    {
        $rawSlug = preg_replace('/^watch-/', '', $slug);

        $show = TvShow::with(['seasons.episodes'])
            ->where('slug', $slug)
            ->orWhere('slug', 'watch-'.$slug)
            ->orWhere('slug', $rawSlug)
            ->first();

        if (! $show) {
            // Check if user visited a movie with /tv-show/ prefix
            $movie = Movie::where('slug', $slug)
                ->orWhere('slug', 'watch-'.$slug)
                ->orWhere('slug', $rawSlug)
                ->first();

            if ($movie) {
                return redirect()->route('movies.show', $movie->slug);
            }

            abort(404);
        }

        $seasonNum = (int) $request->get('season', 1);
        $episodeNum = (int) $request->get('episode', 1);

        $selectedSeason = $show->seasons->firstWhere('season_number', $seasonNum) ?? $show->seasons->first();
        $selectedEpisode = null;
        if ($selectedSeason) {
            $selectedEpisode = $selectedSeason->episodes->firstWhere('episode_number', $episodeNum) ?? $selectedSeason->episodes->first();
        }

        if ($selectedEpisode && empty($selectedEpisode->stream_servers)) {
            $actualSeason = $selectedSeason ? $selectedSeason->season_number : 1;
            $actualEpisode = $selectedEpisode ? $selectedEpisode->episode_number : 1;
            $selectedEpisode->stream_servers = $tmdbService->generateServers('tv', $show->tmdb_id ?: (string) $show->id, $actualSeason, $actualEpisode);
        }

        $recommendations = [];
        if ($show->tmdb_id && $tmdbService->isConfigured()) {
            $recommendations = $tmdbService->getRecommendations('tv', $show->tmdb_id, 8);
        }

        $similar = TvShow::where('id', '!=', $show->id)
            ->where('is_anime', $show->is_anime)
            ->take(8)
            ->get();

        $show->load(['reviews.user']);
        $communityRating = $show->reviews->count() > 0
            ? round($show->reviews->avg('rating'), 1)
            : $show->vote_average;

        return Inertia::render('TvShows/Show', [
            'show' => $show,
            'selectedSeason' => $selectedSeason,
            'selectedEpisode' => $selectedEpisode,
            'similar' => $similar,
            'recommendations' => $recommendations,
            'reviews' => $show->reviews,
            'communityRating' => $communityRating,
        ]);
    }

    public function episodeDetails(Request $request): JsonResponse
    {
        $episodeId = $request->get('episode_id');
        $episode = Episode::find($episodeId);

        if (! $episode) {
            return response()->json(['error' => 'Episode not found'], 404);
        }

        return response()->json([
            'episode' => $episode,
            'players' => $episode->stream_servers ?? [],
        ]);
    }
}
