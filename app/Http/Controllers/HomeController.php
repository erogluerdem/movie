<?php

namespace App\Http\Controllers;

use App\Models\CuratedCollection;
use App\Models\Movie;
use App\Models\SportMatch;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $heroSlugs = [
            ['type' => 'movie', 'slug' => 'a-useful-ghost-2025'],
            ['type' => 'movie', 'slug' => 'jurassic-world-rebirth-2025'],
            ['type' => 'tv', 'slug' => 'dexter-original-sin-2024'],
            ['type' => 'tv', 'slug' => 'matlock-2024'],
            ['type' => 'movie', 'slug' => 'it-was-just-an-accident-2025'],
        ];

        $heroMovies = collect($heroSlugs)->map(function ($item) {
            if ($item['type'] === 'movie') {
                $m = Movie::where('slug', $item['slug'])->orWhere('slug', 'watch-'.$item['slug'])->first();
                if ($m) {
                    $m->media_type = 'movie';

                    return $m;
                }
            } else {
                $t = TvShow::where('slug', $item['slug'])->orWhere('slug', 'watch-'.$item['slug'])->first();
                if ($t) {
                    $t->media_type = 'tv';

                    return $t;
                }
            }

            return null;
        })->filter()->values();

        if ($heroMovies->isEmpty()) {
            $heroMovies = Movie::where('is_featured', true)->orWhere('is_trending', true)->take(5)->get();
        }

        $trending = Movie::where('is_trending', true)->take(10)->get();
        $popularMovies = Movie::orderByDesc('vote_average')->take(12)->get();
        $popularShows = TvShow::where('is_anime', false)->orderByDesc('vote_average')->take(12)->get();
        $latestMovies = Movie::orderByDesc('release_date')->take(12)->get();
        $latestShows = TvShow::orderByDesc('first_air_date')->take(12)->get();
        $animeList = TvShow::where('is_anime', true)->take(12)->get();
        $liveSports = SportMatch::take(6)->get();

        $continueWatching = [];
        if (auth()->check()) {
            $histories = auth()->user()->watchHistories()
                ->where('completed', false)
                ->where('progress_percent', '>=', 3)
                ->where('progress_percent', '<=', 95)
                ->orderByDesc('updated_at')
                ->take(6)
                ->get();

            $continueWatching = $histories->map(function ($h) {
                $item = $h->media_type === 'movie' ? Movie::find($h->media_id) : TvShow::find($h->media_id);
                if (! $item) {
                    return null;
                }

                return [
                    'id' => $h->id,
                    'media_type' => $h->media_type,
                    'media_id' => $h->media_id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'poster_path' => $item->poster_url ?? $item->poster_path,
                    'backdrop_path' => $item->backdrop_url ?? $item->backdrop_path,
                    'progress_percent' => $h->progress_percent,
                    'season_number' => $h->season_number,
                    'episode_number' => $h->episode_number,
                    'url' => $h->media_type === 'movie'
                        ? route('movies.show', $item->slug)
                        : route('tv-shows.show', $item->slug).($h->season_number ? "?season={$h->season_number}&episode={$h->episode_number}" : ''),
                ];
            })->filter()->values();
        }

        $curatedCollections = CuratedCollection::where('is_featured', true)
            ->with(['items.collectible'])
            ->orderBy('order')
            ->take(5)
            ->get();

        $spotlightMovie = Movie::where('is_featured', true)
            ->whereNotNull('backdrop_path')
            ->orderByDesc('vote_average')
            ->first()
            ?? Movie::whereNotNull('backdrop_path')->orderByDesc('vote_average')->first()
            ?? Movie::first();

        return Inertia::render('Home', [
            'heroMovies' => $heroMovies,
            'continueWatching' => $continueWatching,
            'trending' => $trending,
            'popularMovies' => $popularMovies,
            'popularShows' => $popularShows,
            'latestMovies' => $latestMovies,
            'latestShows' => $latestShows,
            'animeList' => $animeList,
            'liveSports' => $liveSports,
            'curatedCollections' => $curatedCollections,
            'spotlightMovie' => $spotlightMovie,
        ]);
    }

    public function randomPick(Request $request): JsonResponse
    {
        $type = $request->get('type', 'any');

        $pool = collect();

        if ($type === 'any' || $type === 'movie') {
            $movies = Movie::where(function ($q) {
                $q->where('vote_average', '>=', 6.5)
                    ->orWhere('is_featured', true)
                    ->orWhere('is_trending', true);
            })
                ->whereNotNull('poster_path')
                ->inRandomOrder()
                ->take(5)
                ->get()
                ->map(function ($m) {
                    $genres = is_array($m->genres) ? $m->genres : (is_string($m->genres) ? json_decode($m->genres, true) : []);

                    return [
                        'id' => $m->id,
                        'title' => $m->title,
                        'slug' => $m->slug,
                        'type' => 'movie',
                        'overview' => $m->overview,
                        'vote_average' => (float) $m->vote_average,
                        'release_year' => $m->release_date ? substr($m->release_date, 0, 4) : '2025',
                        'poster_url' => $m->poster_url ?? $m->poster_path,
                        'backdrop_url' => $m->backdrop_url ?? $m->backdrop_path,
                        'genres' => $genres ?: ['Sinema', 'Popüler'],
                        'watch_url' => route('movies.show', $m->slug),
                        'trailer_url' => $m->trailer_url,
                    ];
                });
            $pool = $pool->concat($movies);
        }

        if ($type === 'any' || $type === 'tv') {
            $shows = TvShow::where(function ($q) {
                $q->where('vote_average', '>=', 6.5)
                    ->orWhere('is_featured', true)
                    ->orWhere('is_trending', true);
            })
                ->whereNotNull('poster_path')
                ->inRandomOrder()
                ->take(5)
                ->get()
                ->map(function ($t) {
                    $genres = is_array($t->genres) ? $t->genres : (is_string($t->genres) ? json_decode($t->genres, true) : []);

                    return [
                        'id' => $t->id,
                        'title' => $t->title,
                        'slug' => $t->slug,
                        'type' => 'tv',
                        'overview' => $t->overview,
                        'vote_average' => (float) $t->vote_average,
                        'release_year' => $t->first_air_date ? substr($t->first_air_date, 0, 4) : '2024',
                        'poster_url' => $t->poster_url ?? $t->poster_path,
                        'backdrop_url' => $t->backdrop_url ?? $t->backdrop_path,
                        'genres' => $genres ?: ['Dizi', 'Popüler'],
                        'watch_url' => route('tv-shows.show', $t->slug),
                        'trailer_url' => $t->trailer_url,
                    ];
                });
            $pool = $pool->concat($shows);
        }

        $pick = $pool->shuffle()->first();

        if (! $pick) {
            $fallback = Movie::first();
            if ($fallback) {
                $pick = [
                    'id' => $fallback->id,
                    'title' => $fallback->title,
                    'slug' => $fallback->slug,
                    'type' => 'movie',
                    'overview' => $fallback->overview,
                    'vote_average' => (float) $fallback->vote_average,
                    'release_year' => $fallback->release_date ? substr($fallback->release_date, 0, 4) : '2025',
                    'poster_url' => $fallback->poster_url ?? $fallback->poster_path,
                    'backdrop_url' => $fallback->backdrop_url ?? $fallback->backdrop_path,
                    'genres' => ['Sinema'],
                    'watch_url' => route('movies.show', $fallback->slug),
                    'trailer_url' => $fallback->trailer_url,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $pick,
        ]);
    }

    public function trending(Request $request): Response
    {
        $type = $request->get('type', 'movies');
        $search = $request->get('search');
        $genre = $request->get('genre');

        $movieQuery = Movie::query()->where('is_trending', true);
        $showQuery = TvShow::query()->where('is_trending', true);

        if ($search) {
            $movieQuery->where('title', 'like', "%{$search}%");
            $showQuery->where('title', 'like', "%{$search}%");
        }

        if ($genre) {
            $movieQuery->whereJsonContains('genres', $genre);
            $showQuery->whereJsonContains('genres', $genre);
        }

        $allMovies = $movieQuery->orderByDesc('vote_average')->get();
        $allShows = $showQuery->orderByDesc('vote_average')->get();

        $leadMovie = Movie::where('slug', 'like', '%zootopia%')->first()
            ?? $allMovies->first()
            ?? Movie::first();

        $stackMovies = Movie::where('id', '!=', $leadMovie->id)
            ->where('is_trending', true)
            ->take(3)
            ->get();

        $items = $type === 'shows' ? $allShows : $allMovies;

        $genres = ['Mystery', 'Thriller', 'Animation', 'Comedy', 'Adventure', 'Family', 'Action', 'Crime', 'Drama', 'Science Fiction', 'Fantasy', 'Romance', 'History', 'Horror', 'War', 'Music', 'Action & Adventure', 'Sci-Fi & Fantasy', 'War & Politics', 'Talk', 'Soap', 'Reality'];

        return Inertia::render('Trending', [
            'lead' => $leadMovie,
            'stack' => $stackMovies,
            'items' => $items,
            'moviesCount' => Movie::count(),
            'showsCount' => TvShow::count(),
            'activeType' => $type,
            'filters' => [
                'type' => $type,
                'search' => $search,
                'genre' => $genre,
            ],
            'genres' => $genres,
        ]);
    }
}
