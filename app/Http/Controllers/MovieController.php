<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\TvShow;
use App\Services\TmdbService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MovieController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Movie::query();

        if ($request->filled('genre')) {
            $query->whereJsonContains('genres', $request->genre);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('year')) {
            $query->where('release_date', 'like', $request->year.'%');
        }

        if ($request->filled('min_rating')) {
            $query->where('vote_average', '>=', (float) $request->min_rating);
        }

        if ($request->filled('year_from')) {
            $query->where('release_date', '>=', $request->year_from.'-01-01');
        }

        if ($request->filled('year_to')) {
            $query->where('release_date', '<=', $request->year_to.'-12-31');
        }

        $sortBy = $request->get('sort', 'popular');
        if ($sortBy === 'rating') {
            $query->orderByDesc('vote_average');
        } elseif ($sortBy === 'newest') {
            $query->orderByDesc('release_date');
        } else {
            $query->orderByDesc('is_trending')->orderByDesc('vote_average');
        }

        $movies = $query->paginate(24)->withQueryString();

        $allMovies = Movie::where('is_trending', true)->orderByDesc('vote_average')->get();
        $lead = Movie::where('slug', 'like', '%zootopia%')->first() ?? $allMovies->first();
        $stack = Movie::where('id', '!=', $lead?->id)->where('is_trending', true)->take(3)->get();

        // All genres matching Movie®
        $allGenres = ['Mystery', 'Thriller', 'Animation', 'Comedy', 'Adventure', 'Family', 'Action', 'Crime', 'Drama', 'Science Fiction', 'Fantasy', 'Romance', 'History', 'Horror', 'War', 'Music', 'Action & Adventure', 'Sci-Fi & Fantasy', 'War & Politics', 'Talk', 'Soap', 'Reality'];

        return Inertia::render('Movies/Index', [
            'movies' => $movies,
            'lead' => $lead,
            'stack' => $stack,
            'filters' => $request->only(['genre', 'search', 'year', 'sort', 'min_rating', 'year_from', 'year_to']),
            'genres' => $allGenres,
        ]);
    }

    public function show(string $slug, TmdbService $tmdbService): Response|RedirectResponse
    {
        $rawSlug = preg_replace('/^watch-/', '', $slug);

        $movie = Movie::where('slug', $slug)
            ->orWhere('slug', 'watch-'.$slug)
            ->orWhere('slug', $rawSlug)
            ->first();

        // If not found locally, try on-demand import from TMDB
        if (! $movie && str_starts_with($slug, 'tmdb-') && $tmdbService->isConfigured()) {
            $tmdbId = substr($slug, 5);
            $movie = $tmdbService->saveOrUpdateMovie($tmdbId);
            if ($movie) {
                return redirect()->route('movies.show', $movie->slug);
            }
        }

        if (! $movie && $tmdbService->isConfigured()) {
            $cleanQuery = str_replace('-', ' ', $rawSlug);
            $search = $tmdbService->search($cleanQuery);
            if (! empty($search['results'][0]['id'])) {
                $movie = $tmdbService->saveOrUpdateMovie($search['results'][0]['id']);
                if ($movie && $movie->slug !== $slug) {
                    return redirect()->route('movies.show', $movie->slug);
                }
            }
        }

        if (! $movie) {
            // Check if user visited a tv show with /movie/ prefix
            $show = TvShow::where('slug', $slug)
                ->orWhere('slug', 'watch-'.$slug)
                ->orWhere('slug', $rawSlug)
                ->first();

            if ($show) {
                return redirect()->route('tv-shows.show', $show->slug);
            }

            abort(404);
        }

        if (empty($movie->stream_servers)) {
            $movie->stream_servers = $tmdbService->generateServers('movie', $movie->tmdb_id ?: (string) $movie->id);
        }

        $recommendations = [];
        if ($movie->tmdb_id && $tmdbService->isConfigured()) {
            $recommendations = $tmdbService->getRecommendations('movie', $movie->tmdb_id, 8);
        }

        $similar = Movie::where('id', '!=', $movie->id)
            ->where(function ($q) use ($movie) {
                if (! empty($movie->genres) && is_array($movie->genres)) {
                    foreach ($movie->genres as $genre) {
                        $q->orWhereJsonContains('genres', $genre);
                    }
                }
            })
            ->take(8)
            ->get();

        $movie->load(['reviews.user']);
        $communityRating = $movie->reviews->count() > 0
            ? round($movie->reviews->avg('rating'), 1)
            : $movie->vote_average;

        return Inertia::render('Movies/Show', [
            'movie' => $movie,
            'similar' => $similar,
            'recommendations' => $recommendations,
            'reviews' => $movie->reviews,
            'communityRating' => $communityRating,
        ]);
    }
}
