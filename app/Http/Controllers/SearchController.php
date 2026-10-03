<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\SportMatch;
use App\Models\TvShow;
use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request, TmdbService $tmdbService): Response
    {
        $q = trim($request->get('q', ''));
        $type = $request->get('type', 'all');

        $movies = collect();
        $shows = collect();

        if ($q !== '') {
            if ($type === 'all' || $type === 'movie') {
                $movies = Movie::where('title', 'like', "%{$q}%")
                    ->orWhere('overview', 'like', "%{$q}%")
                    ->take(24)
                    ->get();

                // If no local movies found, search TMDB and on-demand import the top results
                if ($movies->isEmpty() && $tmdbService->isConfigured()) {
                    $tmdbResponse = $tmdbService->search($q);
                    $tmdbResults = $tmdbResponse['results'] ?? [];
                    $importedCount = 0;

                    foreach (array_slice($tmdbResults, 0, 6) as $item) {
                        if (! empty($item['poster_path'])) {
                            $saved = $tmdbService->saveOrUpdateMovie($item);
                            if ($saved) {
                                $importedCount++;
                            }
                        }
                    }

                    if ($importedCount > 0) {
                        $movies = Movie::where('title', 'like', "%{$q}%")
                            ->orWhere('overview', 'like', "%{$q}%")
                            ->take(24)
                            ->get();
                    }
                }
            }

            if ($type === 'all' || $type === 'tv') {
                $shows = TvShow::where('title', 'like', "%{$q}%")
                    ->orWhere('overview', 'like', "%{$q}%")
                    ->take(24)
                    ->get();
            }
        }

        return Inertia::render('Search', [
            'query' => $q,
            'type' => $type,
            'movies' => $movies,
            'shows' => $shows,
        ]);
    }

    public function live(Request $request, TmdbService $tmdbService): JsonResponse
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $movies = Movie::where('title', 'like', "%{$q}%")
            ->take(6)
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'title' => $m->title,
                    'type' => 'movie',
                    'slug' => $m->slug,
                    'poster' => $m->poster_url,
                    'year' => substr($m->release_date ?? '', 0, 4),
                    'rating' => $m->vote_average,
                    'url' => route('movies.show', $m->slug),
                ];
            });

        $shows = TvShow::where('title', 'like', "%{$q}%")
            ->take(6)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'title' => $s->title,
                    'type' => 'tv',
                    'slug' => $s->slug,
                    'poster' => $s->poster_url,
                    'year' => substr($s->first_air_date ?? '', 0, 4),
                    'rating' => $s->vote_average,
                    'url' => route('tv-shows.show', $s->slug),
                ];
            });

        $combined = $movies->concat($shows);

        // If local results are few and TMDB is configured, supplement with TMDB candidates
        if ($combined->count() < 6 && $tmdbService->isConfigured()) {
            $tmdbCandidates = $tmdbService->searchCandidates($q, 6 - $combined->count());

            // Exclude anything that already matches by title
            $existingTitles = $combined->pluck('title')->map(fn ($t) => strtolower($t))->all();
            $filteredCandidates = $tmdbCandidates->reject(function ($cand) use ($existingTitles) {
                return in_array(strtolower($cand['title']), $existingTitles, true);
            });

            $combined = $combined->concat($filteredCandidates);
        }

        return response()->json([
            'results' => $combined->take(10)->values(),
        ]);
    }

    public function mega(Request $request, TmdbService $tmdbService): JsonResponse
    {
        $q = trim($request->get('q', ''));
        $type = $request->get('type', 'all'); // 'all', 'movie', 'tv', 'anime', 'sports'
        $minRating = (float) $request->get('min_rating', 0);
        $yearRange = $request->get('year_range', 'all');
        $genre = trim($request->get('genre', ''));

        // If query is empty, return instant discovery items & trending searches
        if ($q === '') {
            $topMovies = Movie::whereNotNull('poster_path')
                ->where('vote_average', '>=', 7.0)
                ->orderByDesc('vote_average')
                ->take(4)
                ->get()
                ->map(fn ($m) => [
                    'id' => $m->id,
                    'title' => $m->title,
                    'type' => 'movie',
                    'slug' => $m->slug,
                    'poster' => $m->poster_url,
                    'backdrop' => $m->backdrop_url,
                    'year' => substr($m->release_date ?? '', 0, 4),
                    'rating' => (float) $m->vote_average,
                    'genres' => is_array($m->genres) ? $m->genres : (is_string($m->genres) ? json_decode($m->genres, true) : []),
                    'url' => route('movies.show', $m->slug),
                ]);

            $topShows = TvShow::whereNotNull('poster_path')
                ->where('vote_average', '>=', 7.0)
                ->orderByDesc('vote_average')
                ->take(4)
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'type' => $s->is_anime ? 'anime' : 'tv',
                    'slug' => $s->slug,
                    'poster' => $s->poster_url,
                    'backdrop' => $s->backdrop_url,
                    'year' => substr($s->first_air_date ?? '', 0, 4),
                    'rating' => (float) $s->vote_average,
                    'genres' => is_array($s->genres) ? $s->genres : (is_string($s->genres) ? json_decode($s->genres, true) : []),
                    'url' => route('tv-shows.show', $s->slug),
                ]);

            $topPicks = $topMovies->concat($topShows);

            $liveSports = SportMatch::take(3)->get()->map(fn ($sp) => [
                'id' => $sp->id,
                'title' => $sp->title,
                'slug' => $sp->slug,
                'type' => 'sport',
                'league' => $sp->league,
                'team_home' => $sp->team_home,
                'team_away' => $sp->team_away,
                'match_time' => $sp->match_time,
                'is_live' => (bool) $sp->is_live,
                'status' => $sp->status,
                'url' => url('/sports?watch='.$sp->slug),
            ]);

            return response()->json([
                'status' => 'success',
                'is_empty_query' => true,
                'top_picks' => $topPicks->values(),
                'live_sports' => $liveSports->values(),
                'trending_keywords' => ['Gladiator', 'Inception', 'Arcane', 'Attack on Titan', 'Real Madrid', 'Interstellar', 'Dune'],
                'genres' => ['Aksiyon', 'Bilim Kurgu', 'Komedi', 'Korku', 'Dram', 'Animasyon', 'Macera', 'Gerilim'],
            ]);
        }

        // Active query search
        $movies = collect();
        $shows = collect();
        $sports = collect();

        // 1. Search Movies
        if ($type === 'all' || $type === 'movie') {
            $mQuery = Movie::where(function ($qBuilder) use ($q) {
                $qBuilder->where('title', 'like', "%{$q}%")
                    ->orWhere('overview', 'like', "%{$q}%");
            });

            if ($minRating > 0) {
                $mQuery->where('vote_average', '>=', $minRating);
            }

            if ($yearRange === '2024-2025') {
                $mQuery->whereBetween('release_date', ['2024-01-01', '2025-12-31']);
            } elseif ($yearRange === '2020-2023') {
                $mQuery->whereBetween('release_date', ['2020-01-01', '2023-12-31']);
            } elseif ($yearRange === '2010-2019') {
                $mQuery->whereBetween('release_date', ['2010-01-01', '2019-12-31']);
            } elseif ($yearRange === 'classic') {
                $mQuery->where('release_date', '<', '2010-01-01');
            }

            if ($genre !== '') {
                $mQuery->where('genres', 'like', "%{$genre}%");
            }

            $movies = $mQuery->take(12)->get()->map(fn ($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'type' => 'movie',
                'slug' => $m->slug,
                'poster' => $m->poster_url,
                'backdrop' => $m->backdrop_url,
                'year' => substr($m->release_date ?? '', 0, 4),
                'rating' => (float) $m->vote_average,
                'genres' => is_array($m->genres) ? $m->genres : (is_string($m->genres) ? json_decode($m->genres, true) : []),
                'url' => route('movies.show', $m->slug),
            ]);
        }

        // 2. Search Shows & Anime
        if ($type === 'all' || $type === 'tv' || $type === 'anime') {
            $sQuery = TvShow::where(function ($qBuilder) use ($q) {
                $qBuilder->where('title', 'like', "%{$q}%")
                    ->orWhere('overview', 'like', "%{$q}%");
            });

            if ($type === 'anime') {
                $sQuery->where('is_anime', true);
            }

            if ($minRating > 0) {
                $sQuery->where('vote_average', '>=', $minRating);
            }

            if ($yearRange === '2024-2025') {
                $sQuery->whereBetween('first_air_date', ['2024-01-01', '2025-12-31']);
            } elseif ($yearRange === '2020-2023') {
                $sQuery->whereBetween('first_air_date', ['2020-01-01', '2023-12-31']);
            } elseif ($yearRange === '2010-2019') {
                $sQuery->whereBetween('first_air_date', ['2010-01-01', '2019-12-31']);
            } elseif ($yearRange === 'classic') {
                $sQuery->where('first_air_date', '<', '2010-01-01');
            }

            if ($genre !== '') {
                $sQuery->where('genres', 'like', "%{$genre}%");
            }

            $shows = $sQuery->take(12)->get()->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'type' => $s->is_anime ? 'anime' : 'tv',
                'is_anime' => (bool) $s->is_anime,
                'slug' => $s->slug,
                'poster' => $s->poster_url,
                'backdrop' => $s->backdrop_url,
                'year' => substr($s->first_air_date ?? '', 0, 4),
                'rating' => (float) $s->vote_average,
                'genres' => is_array($s->genres) ? $s->genres : (is_string($s->genres) ? json_decode($s->genres, true) : []),
                'url' => route('tv-shows.show', $s->slug),
            ]);
        }

        // 3. Search Sports
        if ($type === 'all' || $type === 'sports') {
            $sports = SportMatch::where(function ($sp) use ($q) {
                $sp->where('title', 'like', "%{$q}%")
                    ->orWhere('team_home', 'like', "%{$q}%")
                    ->orWhere('team_away', 'like', "%{$q}%")
                    ->orWhere('league', 'like', "%{$q}%");
            })
                ->take(4)
                ->get()
                ->map(fn ($sp) => [
                    'id' => $sp->id,
                    'title' => $sp->title,
                    'slug' => $sp->slug,
                    'type' => 'sport',
                    'league' => $sp->league,
                    'team_home' => $sp->team_home,
                    'team_away' => $sp->team_away,
                    'match_time' => $sp->match_time,
                    'is_live' => (bool) $sp->is_live,
                    'status' => $sp->status,
                    'url' => url('/sports?watch='.$sp->slug),
                ]);
        }

        // 4. TMDB Supplement if local results are few
        $totalLocal = $movies->count() + $shows->count();
        if ($totalLocal < 4 && $tmdbService->isConfigured() && ($type === 'all' || $type === 'movie')) {
            $tmdbCandidates = $tmdbService->searchCandidates($q, 4 - $totalLocal);
            $existingTitles = $movies->pluck('title')->concat($shows->pluck('title'))->map(fn ($t) => strtolower($t))->all();
            $filteredCandidates = $tmdbCandidates->reject(fn ($c) => in_array(strtolower($c['title']), $existingTitles, true))
                ->map(fn ($c) => [
                    'id' => $c['id'],
                    'title' => $c['title'],
                    'type' => 'movie',
                    'slug' => $c['slug'],
                    'poster' => $c['poster'],
                    'backdrop' => null,
                    'year' => $c['year'],
                    'rating' => (float) ($c['rating'] ?? 7.0),
                    'genres' => ['TMDB Keşif'],
                    'url' => $c['url'],
                ]);

            if ($minRating > 0) {
                $filteredCandidates = $filteredCandidates->filter(fn ($c) => $c['rating'] >= $minRating);
            }

            $movies = $movies->concat($filteredCandidates);
        }

        $totalCount = $movies->count() + $shows->count() + $sports->count();

        return response()->json([
            'status' => 'success',
            'is_empty_query' => false,
            'query' => $q,
            'total_count' => $totalCount,
            'movies' => $movies->values(),
            'shows' => $shows->values(),
            'sports' => $sports->values(),
        ]);
    }
}
