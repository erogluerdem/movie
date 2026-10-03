<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncTmdbJob;
use App\Models\Movie;
use App\Models\Setting;
use App\Models\TvShow;
use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Inertia\Response;

class AdminTmdbController extends Controller
{
    public function index(TmdbService $tmdbService): Response
    {
        $apiKey = config('services.tmdb.api_key') ?: Setting::get('tmdb_api_key', '');
        $maskedKey = $apiKey ? substr($apiKey, 0, 4).'...'.substr($apiKey, -4) : null;

        $stats = [
            'total_movies' => Movie::count(),
            'total_tv' => TvShow::where('is_anime', false)->count(),
            'total_anime' => TvShow::where('is_anime', true)->count(),
            'movies_with_trailers' => Movie::whereNotNull('trailer_url')->where('trailer_url', '!=', '')->count(),
            'tv_with_trailers' => TvShow::whereNotNull('trailer_url')->where('trailer_url', '!=', '')->count(),
        ];

        $automation = [
            'enabled' => filter_var(Setting::get('tmdb_auto_sync_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'frequency' => Setting::get('tmdb_auto_sync_frequency', 'daily'),
            'pages' => (int) Setting::get('tmdb_auto_sync_pages', 2),
            'min_votes' => (int) Setting::get('tmdb_auto_sync_min_votes', 40),
            'last_sync_at' => Setting::get('tmdb_last_sync_at'),
            'last_sync_status' => Setting::get('tmdb_last_sync_status', 'idle'),
            'last_sync_message' => Setting::get('tmdb_last_sync_message', 'No sync run yet.'),
        ];

        $recentMovies = Movie::latest()
            ->take(6)
            ->select(['id', 'title', 'slug', 'poster_path', 'vote_average', 'release_date', 'trailer_url', 'created_at'])
            ->get();

        $recentTvShows = TvShow::latest()
            ->take(6)
            ->select(['id', 'title', 'slug', 'poster_path', 'vote_average', 'first_air_date', 'is_anime', 'trailer_url', 'created_at'])
            ->get();

        return Inertia::render('Admin/Tmdb/Index', [
            'stats' => $stats,
            'tmdb_config' => [
                'is_configured' => $tmdbService->isConfigured(),
                'api_key_masked' => $maskedKey,
                'has_read_token' => ! empty(config('services.tmdb.read_token')),
            ],
            'automation' => $automation,
            'recent_movies' => $recentMovies,
            'recent_tv_shows' => $recentTvShows,
        ]);
    }

    public function sync(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:curated,popular_movies,trending_movies,top_rated_movies,upcoming_movies,popular_tv,top_rated_tv,anime,genre'],
            'genre' => ['nullable', 'string'],
            'pages' => ['nullable', 'integer', 'min:1', 'max:10'],
            'min_votes' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'background' => ['nullable', 'boolean'],
        ]);

        $mode = $validated['mode'];
        $pages = $validated['pages'] ?? 2;
        $minVotes = $validated['min_votes'] ?? 30;
        $genre = $validated['genre'] ?? null;
        $isBackground = $request->boolean('background', false);

        $params = match ($mode) {
            'curated' => ['--curated' => true],
            'popular_movies' => ['--media' => 'movie', '--type' => 'popular', '--pages' => $pages, '--min-votes' => $minVotes],
            'trending_movies' => ['--media' => 'movie', '--type' => 'trending', '--pages' => $pages, '--min-votes' => $minVotes],
            'top_rated_movies' => ['--media' => 'movie', '--type' => 'top_rated', '--pages' => $pages, '--min-votes' => $minVotes],
            'upcoming_movies' => ['--media' => 'movie', '--type' => 'upcoming', '--pages' => $pages, '--min-votes' => 20],
            'popular_tv' => ['--media' => 'tv', '--type' => 'popular', '--pages' => $pages, '--min-votes' => $minVotes],
            'top_rated_tv' => ['--media' => 'tv', '--type' => 'top_rated', '--pages' => $pages, '--min-votes' => $minVotes],
            'anime' => ['--media' => 'anime', '--type' => 'anime', '--pages' => $pages, '--min-votes' => 30],
            'genre' => ['--media' => 'movie', '--genre' => $genre ?: 'action', '--pages' => $pages, '--min-votes' => $minVotes],
            default => ['--media' => 'movie', '--type' => 'popular', '--pages' => 2],
        };

        if ($isBackground) {
            SyncTmdbJob::dispatch(
                media: $params['--media'] ?? 'movie',
                type: $params['--type'] ?? 'popular',
                pages: $params['--pages'] ?? $pages,
                minVotes: $params['--min-votes'] ?? $minVotes,
                genre: $genre,
                curated: ($mode === 'curated')
            );

            return back()->with('success', 'TMDB Senkronizasyon görevi arka plan kuyruğuna alındı!');
        }

        // Direct Execution
        Setting::set('tmdb_last_sync_status', 'running', 'tmdb');
        Setting::set('tmdb_last_sync_at', now()->toDateTimeString(), 'tmdb');

        $exitCode = Artisan::call('tmdb:sync', $params);

        if ($exitCode === 0) {
            Setting::set('tmdb_last_sync_status', 'success', 'tmdb');
            Setting::set('tmdb_last_sync_message', "Senkronizasyon başarıyla tamamlandı ({$mode}).", 'tmdb');

            return back()->with('success', 'TMDB verileri başarıyla senkronize edildi!');
        }

        Setting::set('tmdb_last_sync_status', 'failed', 'tmdb');
        Setting::set('tmdb_last_sync_message', 'Hata: Kod '.$exitCode, 'tmdb');

        return back()->with('error', 'Senkronizasyon sırasında hata oluştu.');
    }

    public function search(Request $request, TmdbService $tmdbService): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2'],
            'media' => ['nullable', 'string', 'in:movie,tv'],
        ]);

        $media = $validated['media'] ?? 'movie';
        $query = $validated['query'];

        if ($media === 'tv') {
            $response = $tmdbService->searchTv($query);
            $results = collect($response['results'] ?? [])->take(8)->map(function ($item) {
                return [
                    'id' => $item['id'],
                    'title' => $item['name'] ?? '',
                    'media' => 'tv',
                    'year' => substr($item['first_air_date'] ?? '', 0, 4),
                    'poster' => ! empty($item['poster_path']) ? "https://image.tmdb.org/t/p/w342{$item['poster_path']}" : null,
                    'rating' => round((float) ($item['vote_average'] ?? 0), 1),
                    'overview' => $item['overview'] ?? '',
                ];
            });
        } else {
            $response = $tmdbService->search($query);
            $results = collect($response['results'] ?? [])->take(8)->map(function ($item) {
                return [
                    'id' => $item['id'],
                    'title' => $item['title'] ?? '',
                    'media' => 'movie',
                    'year' => substr($item['release_date'] ?? '', 0, 4),
                    'poster' => ! empty($item['poster_path']) ? "https://image.tmdb.org/t/p/w342{$item['poster_path']}" : null,
                    'rating' => round((float) ($item['vote_average'] ?? 0), 1),
                    'overview' => $item['overview'] ?? '',
                ];
            });
        }

        return response()->json(['results' => $results]);
    }

    public function importSingle(Request $request, TmdbService $tmdbService): RedirectResponse
    {
        $validated = $request->validate([
            'tmdb_id' => ['required'],
            'media' => ['required', 'string', 'in:movie,tv,anime'],
        ]);

        $tmdbId = $validated['tmdb_id'];
        $media = $validated['media'];

        if ($media === 'tv' || $media === 'anime') {
            $saved = $tmdbService->saveOrUpdateTv($tmdbId);
        } else {
            $saved = $tmdbService->saveOrUpdateMovie($tmdbId);
        }

        if ($saved) {
            return back()->with('success', "'{$saved->title}' başarıyla platforma aktarıldı!");
        }

        return back()->with('error', 'TMDB ID aktarılırken hata oluştu.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tmdb_auto_sync_enabled' => ['boolean'],
            'tmdb_auto_sync_frequency' => ['required', 'string', 'in:hourly,every_12_hours,daily,weekly'],
            'tmdb_auto_sync_pages' => ['required', 'integer', 'min:1', 'max:10'],
            'tmdb_auto_sync_min_votes' => ['required', 'integer', 'min:0', 'max:500'],
            'tmdb_api_key' => ['nullable', 'string', 'max:100'],
        ]);

        Setting::set('tmdb_auto_sync_enabled', $request->boolean('tmdb_auto_sync_enabled') ? '1' : '0', 'tmdb');
        Setting::set('tmdb_auto_sync_frequency', $validated['tmdb_auto_sync_frequency'], 'tmdb');
        Setting::set('tmdb_auto_sync_pages', (string) $validated['tmdb_auto_sync_pages'], 'tmdb');
        Setting::set('tmdb_auto_sync_min_votes', (string) $validated['tmdb_auto_sync_min_votes'], 'tmdb');

        if (! empty($validated['tmdb_api_key'])) {
            Setting::set('tmdb_api_key', $validated['tmdb_api_key'], 'tmdb');
        }

        return back()->with('success', 'TMDB Otomasyon ayarları başarıyla kaydedildi!');
    }
}
