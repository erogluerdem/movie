<?php

namespace App\Services;

use App\Models\Movie;
use App\Models\Setting;
use App\Models\TvShow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TmdbService
{
    protected ?string $apiKey;

    protected ?string $readToken;

    protected string $baseUrl;

    protected string $imageBaseUrl;

    public function __construct()
    {
        $this->apiKey = Setting::get('tmdb_api_key', '') ?: config('services.tmdb.api_key', '');
        $this->readToken = config('services.tmdb.read_token', '');
        $this->baseUrl = config('services.tmdb.base_url', 'https://api.themoviedb.org/3');
        $this->imageBaseUrl = config('services.tmdb.image_base_url', 'https://image.tmdb.org/t/p');
    }

    public function isConfigured(): bool
    {
        $key = Setting::get('tmdb_api_key', '') ?: config('services.tmdb.api_key', '');
        $token = config('services.tmdb.read_token', '');

        return ! empty($key) || ! empty($token);
    }

    protected function client()
    {
        $req = Http::timeout(15)->acceptJson()->withoutVerifying();
        if (! empty($this->readToken)) {
            $req = $req->withToken($this->readToken);
        }

        return $req;
    }

    protected function buildParams(array $params = []): array
    {
        if (empty($this->readToken) && ! empty($this->apiKey)) {
            $params['api_key'] = $this->apiKey;
        }

        return $params;
    }

    public function extractTrailerUrl(array $videos): ?string
    {
        $ytTrailers = array_filter($videos, function ($v) {
            $site = $v['site'] ?? '';
            $type = $v['type'] ?? '';

            return strtolower($site) === 'youtube' && in_array(strtolower($type), ['trailer', 'teaser']);
        });

        // 1. First look for official trailer
        foreach ($ytTrailers as $v) {
            if (! empty($v['official']) && strtolower($v['type'] ?? '') === 'trailer' && ! empty($v['key'])) {
                return 'https://www.youtube.com/watch?v='.$v['key'];
            }
        }

        // 2. Any official video
        foreach ($ytTrailers as $v) {
            if (! empty($v['official']) && ! empty($v['key'])) {
                return 'https://www.youtube.com/watch?v='.$v['key'];
            }
        }

        // 3. Fallback to any YouTube trailer
        foreach ($ytTrailers as $v) {
            if (! empty($v['key'])) {
                return 'https://www.youtube.com/watch?v='.$v['key'];
            }
        }

        return null;
    }

    public function generateServers(string $type, string $id, int $season = 1, int $episode = 1): array
    {
        if ($type === 'movie') {
            return [
                [
                    'server_name' => 'VidCloud 4K',
                    'embed_url' => "https://vidsrc.to/embed/movie/{$id}",
                    'quality' => '4K',
                ],
                [
                    'server_name' => 'UpCloud 1080p',
                    'embed_url' => "https://vidsrc.me/embed/movie?tmdb={$id}",
                    'quality' => '1080p',
                ],
                [
                    'server_name' => 'SuperEmbed HD',
                    'embed_url' => "https://multiembed.mov/?video_id={$id}&tmdb=1",
                    'quality' => 'HD',
                ],
                [
                    'server_name' => 'AutoEmbed 1080p',
                    'embed_url' => "https://player.autoembed.cc/embed/movie/{$id}",
                    'quality' => '1080p',
                ],
            ];
        }

        return [
            [
                'server_name' => 'VidCloud 4K',
                'embed_url' => "https://vidsrc.to/embed/tv/{$id}/{$season}/{$episode}",
                'quality' => '4K',
            ],
            [
                'server_name' => 'UpCloud 1080p',
                'embed_url' => "https://vidsrc.me/embed/tv?tmdb={$id}&season={$season}&episode={$episode}",
                'quality' => '1080p',
            ],
            [
                'server_name' => 'SuperEmbed HD',
                'embed_url' => "https://multiembed.mov/?video_id={$id}&tmdb=1&s={$season}&e={$episode}",
                'quality' => 'HD',
            ],
            [
                'server_name' => 'AutoEmbed 1080p',
                'embed_url' => "https://player.autoembed.cc/embed/tv/{$id}/{$season}/{$episode}",
                'quality' => '1080p',
            ],
        ];
    }

    public function getRecommendations(string $type, string $id, int $limit = 8): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/{$type}/{$id}/recommendations", $this->buildParams([
                'page' => 1,
            ]));

            $results = $response->json()['results'] ?? [];

            if (empty($results)) {
                $response = $this->client()->get("{$this->baseUrl}/{$type}/{$id}/similar", $this->buildParams([
                    'page' => 1,
                ]));
                $results = $response->json()['results'] ?? [];
            }

            return collect(array_slice($results, 0, $limit))->map(function ($item) use ($type) {
                return [
                    'id' => $item['id'],
                    'title' => $item['title'] ?? $item['name'] ?? '',
                    'media_type' => $type,
                    'slug' => Str::slug($item['title'] ?? $item['name'] ?? 'title-'.$item['id']),
                    'poster_url' => ! empty($item['poster_path']) ? "{$this->imageBaseUrl}/w500{$item['poster_path']}" : null,
                    'backdrop_url' => ! empty($item['backdrop_path']) ? "{$this->imageBaseUrl}/w1280{$item['backdrop_path']}" : null,
                    'release_date' => $item['release_date'] ?? $item['first_air_date'] ?? '',
                    'vote_average' => round((float) ($item['vote_average'] ?? 0), 1),
                ];
            })->all();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function formatMovieData(array $data): array
    {
        $director = null;
        if (isset($data['credits']['crew'])) {
            foreach ($data['credits']['crew'] as $crew) {
                if (($crew['job'] ?? '') === 'Director') {
                    $director = $crew['name'];
                    break;
                }
            }
        }

        $videos = $data['videos']['results'] ?? ($data['videos'] ?? []);
        $trailerUrl = $this->extractTrailerUrl($videos);

        $genres = [];
        if (isset($data['genres']) && is_array($data['genres'])) {
            foreach ($data['genres'] as $g) {
                $genres[] = is_array($g) ? ($g['name'] ?? '') : (string) $g;
            }
            $genres = array_values(array_filter($genres));
        }

        $cast = [];
        if (isset($data['credits']['cast']) && is_array($data['credits']['cast'])) {
            foreach (array_slice($data['credits']['cast'], 0, 8) as $member) {
                $cast[] = [
                    'name' => $member['name'] ?? '',
                    'character' => $member['character'] ?? '',
                    'profile_path' => ! empty($member['profile_path'])
                        ? (str_starts_with($member['profile_path'], 'http') ? $member['profile_path'] : "{$this->imageBaseUrl}/w185{$member['profile_path']}")
                        : null,
                ];
            }
        }

        $poster = ! empty($data['poster_path'])
            ? (str_starts_with($data['poster_path'], 'http') ? $data['poster_path'] : "{$this->imageBaseUrl}/w500{$data['poster_path']}")
            : '';

        $backdrop = ! empty($data['backdrop_path'])
            ? (str_starts_with($data['backdrop_path'], 'http') ? $data['backdrop_path'] : "{$this->imageBaseUrl}/w1280{$data['backdrop_path']}")
            : '';

        $runtime = '';
        if (isset($data['runtime']) && ! empty($data['runtime'])) {
            $runtime = is_numeric($data['runtime']) ? "{$data['runtime']} min" : (string) $data['runtime'];
        }

        $tmdbId = (string) ($data['id'] ?? $data['tmdb_id'] ?? '');

        return [
            'tmdb_id' => $tmdbId,
            'title' => $data['title'] ?? '',
            'slug' => Str::slug($data['title'] ?? 'movie-'.$tmdbId),
            'tagline' => $data['tagline'] ?? '',
            'overview' => $data['overview'] ?? '',
            'poster_path' => $poster,
            'backdrop_path' => $backdrop,
            'release_date' => $data['release_date'] ?? '',
            'vote_average' => round((float) ($data['vote_average'] ?? 0), 1),
            'runtime' => $runtime,
            'director' => $director,
            'trailer_url' => $trailerUrl,
            'genres' => $genres,
            'cast' => $cast,
            'stream_servers' => $this->generateServers('movie', $tmdbId),
        ];
    }

    public function fetchMovie(string $tmdbId): array
    {
        if (! $this->isConfigured()) {
            return [
                'status' => 'success',
                'data' => [
                    'tmdb_id' => $tmdbId,
                    'title' => 'Sample Movie '.$tmdbId,
                    'slug' => 'sample-movie-'.$tmdbId,
                    'tagline' => 'An epic cinematic journey.',
                    'overview' => 'Detailed synopsis fetched from TMDB automatically with high-definition streaming sources.',
                    'poster_path' => 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=500&q=80',
                    'backdrop_path' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=1280&q=80',
                    'release_date' => date('Y-m-d'),
                    'vote_average' => 8.4,
                    'runtime' => '124 min',
                    'director' => 'Christopher Nolan',
                    'trailer_url' => 'https://www.youtube.com/watch?v=YoHD9XEInc0',
                    'genres' => ['Action', 'Sci-Fi', 'Adventure'],
                    'stream_servers' => $this->generateServers('movie', $tmdbId),
                ],
            ];
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/movie/{$tmdbId}", $this->buildParams([
                'append_to_response' => 'credits,videos',
            ]));

            if (! $response->successful()) {
                return ['status' => 'error', 'message' => 'TMDB title not found. Status: '.$response->status()];
            }

            $formatted = $this->formatMovieData($response->json());

            return ['status' => 'success', 'data' => $formatted];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Connection to TMDB failed: '.$e->getMessage()];
        }
    }

    public function fetchTv(string $tmdbId): array
    {
        if (! $this->isConfigured()) {
            return [
                'status' => 'success',
                'data' => [
                    'tmdb_id' => $tmdbId,
                    'title' => 'Sample Series '.$tmdbId,
                    'slug' => 'sample-series-'.$tmdbId,
                    'tagline' => 'A groundbreaking television event.',
                    'overview' => 'Detailed synopsis fetched from TMDB with complete seasons and episodes.',
                    'poster_path' => 'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?w=500&q=80',
                    'backdrop_path' => 'https://images.unsplash.com/photo-1518676590629-3dcbd9c5a5c9?w=1280&q=80',
                    'first_air_date' => date('Y-m-d'),
                    'vote_average' => 8.7,
                    'is_anime' => false,
                    'genres' => ['Drama', 'Mystery', 'Sci-Fi'],
                    'number_of_seasons' => 1,
                    'number_of_episodes' => 10,
                ],
            ];
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/tv/{$tmdbId}", $this->buildParams([
                'append_to_response' => 'videos,credits',
            ]));

            if (! $response->successful()) {
                return ['status' => 'error', 'message' => 'TMDB series not found. Status: '.$response->status()];
            }

            $data = $response->json();
            $genres = isset($data['genres']) ? array_column($data['genres'], 'name') : [];
            $isAnime = in_array('Animation', $genres) && (
                in_array('JP', $data['origin_country'] ?? []) ||
                ($data['original_language'] ?? '') === 'ja'
            );

            $cast = [];
            if (isset($data['credits']['cast']) && is_array($data['credits']['cast'])) {
                foreach (array_slice($data['credits']['cast'], 0, 8) as $member) {
                    $cast[] = [
                        'name' => $member['name'] ?? '',
                        'character' => $member['character'] ?? '',
                        'profile_path' => ! empty($member['profile_path'])
                            ? (str_starts_with($member['profile_path'], 'http') ? $member['profile_path'] : "{$this->imageBaseUrl}/w185{$member['profile_path']}")
                            : null,
                    ];
                }
            }

            $videos = $data['videos']['results'] ?? [];
            $trailerUrl = $this->extractTrailerUrl($videos);

            return [
                'status' => 'success',
                'data' => [
                    'tmdb_id' => (string) $data['id'],
                    'title' => $data['name'] ?? '',
                    'slug' => Str::slug($data['name'] ?? 'tv-'.$tmdbId),
                    'tagline' => $data['tagline'] ?? '',
                    'overview' => $data['overview'] ?? '',
                    'poster_path' => ! empty($data['poster_path']) ? "{$this->imageBaseUrl}/w500{$data['poster_path']}" : '',
                    'backdrop_path' => ! empty($data['backdrop_path']) ? "{$this->imageBaseUrl}/w1280{$data['backdrop_path']}" : '',
                    'first_air_date' => $data['first_air_date'] ?? '',
                    'vote_average' => round((float) ($data['vote_average'] ?? 0), 1),
                    'is_anime' => $isAnime,
                    'genres' => $genres,
                    'cast' => $cast,
                    'number_of_seasons' => $data['number_of_seasons'] ?? 1,
                    'number_of_episodes' => $data['number_of_episodes'] ?? 10,
                    'trailer_url' => $trailerUrl,
                ],
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Connection to TMDB failed: '.$e->getMessage()];
        }
    }

    public function search(string $query, int $page = 1): array
    {
        if (! $this->isConfigured()) {
            return ['results' => []];
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/search/movie", $this->buildParams([
                'query' => $query,
                'page' => $page,
                'include_adult' => false,
            ]));

            return $response->json() ?? ['results' => []];
        } catch (\Exception $e) {
            return ['results' => []];
        }
    }

    public function getPopular(int $page = 1): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/movie/popular", $this->buildParams([
                'page' => $page,
            ]));

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getTrending(string $timeWindow = 'week', int $page = 1): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/trending/movie/{$timeWindow}", $this->buildParams([
                'page' => $page,
            ]));

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getTopRated(int $page = 1): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/movie/top_rated", $this->buildParams([
                'page' => $page,
            ]));

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getUpcoming(int $page = 1): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/movie/upcoming", $this->buildParams([
                'page' => $page,
            ]));

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getPopularTv(int $page = 1): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/tv/popular", $this->buildParams([
                'page' => $page,
            ]));

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getTopRatedTv(int $page = 1): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/tv/top_rated", $this->buildParams([
                'page' => $page,
            ]));

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getTrendingTv(string $timeWindow = 'week', int $page = 1): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/trending/tv/{$timeWindow}", $this->buildParams([
                'page' => $page,
            ]));

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getNowPlaying(int $page = 1): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/movie/now_playing", $this->buildParams([
                'page' => $page,
            ]));

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getAnime(int $page = 1): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/discover/tv", $this->buildParams([
                'page' => $page,
                'with_genres' => '16',
                'with_original_language' => 'ja',
                'sort_by' => 'vote_count.desc',
                'vote_count.gte' => 30,
            ]));

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function discoverMovies(array $params = []): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/discover/movie", $this->buildParams($params));

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function discoverTv(array $params = []): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/discover/tv", $this->buildParams($params));

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function searchTv(string $query, int $page = 1): array
    {
        if (! $this->isConfigured()) {
            return ['results' => []];
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/search/tv", $this->buildParams([
                'query' => $query,
                'page' => $page,
                'include_adult' => false,
            ]));

            return $response->json() ?? ['results' => []];
        } catch (\Exception $e) {
            return ['results' => []];
        }
    }

    public function searchCandidates(string $query, int $limit = 5): Collection
    {
        $res = $this->search($query);
        $results = $res['results'] ?? [];

        return collect(array_slice($results, 0, $limit))->map(function ($item) {
            return [
                'id' => 'tmdb-'.$item['id'],
                'title' => $item['title'] ?? '',
                'type' => 'movie',
                'slug' => 'tmdb-'.$item['id'],
                'poster' => ! empty($item['poster_path']) ? "{$this->imageBaseUrl}/w500{$item['poster_path']}" : null,
                'year' => substr($item['release_date'] ?? '', 0, 4),
                'rating' => round((float) ($item['vote_average'] ?? 0), 1),
                'url' => route('movies.show', 'tmdb-'.$item['id']),
            ];
        });
    }

    public function saveOrUpdateMovie(array|string|int $tmdbDataOrId, bool $isTrending = false): ?Movie
    {
        if (is_numeric($tmdbDataOrId) || is_string($tmdbDataOrId)) {
            $res = $this->fetchMovie((string) $tmdbDataOrId);
            if (($res['status'] ?? '') !== 'success' || empty($res['data'])) {
                return null;
            }
            $formatted = $res['data'];
        } else {
            if (! isset($tmdbDataOrId['credits']) && ! empty($tmdbDataOrId['id'])) {
                return $this->saveOrUpdateMovie((string) $tmdbDataOrId['id'], $isTrending);
            }
            $formatted = $this->formatMovieData($tmdbDataOrId);
        }

        if (empty($formatted['title'])) {
            return null;
        }

        $movie = Movie::where('tmdb_id', $formatted['tmdb_id'])->first();

        if (! $movie) {
            $baseSlug = $formatted['slug'] ?: Str::slug($formatted['title']);
            $slug = $baseSlug;
            $counter = 1;
            while (Movie::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $movie = new Movie;
            $movie->slug = $slug;
        }

        $movie->tmdb_id = $formatted['tmdb_id'];
        $movie->title = $formatted['title'];
        $movie->tagline = $formatted['tagline'] ?? $movie->tagline;
        $movie->overview = $formatted['overview'] ?? $movie->overview;
        $movie->poster_path = $formatted['poster_path'] ?? $movie->poster_path;
        $movie->backdrop_path = $formatted['backdrop_path'] ?? $movie->backdrop_path;
        $movie->release_date = $formatted['release_date'] ?? $movie->release_date;
        $movie->vote_average = $formatted['vote_average'] ?? $movie->vote_average;
        $movie->runtime = $formatted['runtime'] ?? $movie->runtime;
        $movie->director = $formatted['director'] ?? $movie->director;
        $movie->trailer_url = $formatted['trailer_url'] ?? $movie->trailer_url;
        $movie->genres = $formatted['genres'] ?? $movie->genres;
        if (! empty($formatted['cast'])) {
            $movie->cast = $formatted['cast'];
        }
        if (! empty($formatted['stream_servers'])) {
            $movie->stream_servers = $formatted['stream_servers'];
        }

        if ($isTrending) {
            $movie->is_trending = true;
        }

        $movie->save();

        return $movie;
    }

    public function saveOrUpdateTv(array|string|int $tmdbDataOrId, bool $isTrending = false): ?TvShow
    {
        if (is_numeric($tmdbDataOrId) || is_string($tmdbDataOrId)) {
            $res = $this->fetchTv((string) $tmdbDataOrId);
            if (($res['status'] ?? '') !== 'success' || empty($res['data'])) {
                return null;
            }
            $formatted = $res['data'];
        } else {
            if (! empty($tmdbDataOrId['id'])) {
                return $this->saveOrUpdateTv((string) $tmdbDataOrId['id'], $isTrending);
            }
            $formatted = $tmdbDataOrId;
        }

        if (empty($formatted['title'])) {
            return null;
        }

        $show = TvShow::where('tmdb_id', $formatted['tmdb_id'])->first();

        if (! $show) {
            $baseSlug = $formatted['slug'] ?: Str::slug($formatted['title']);
            $slug = $baseSlug;
            $counter = 1;
            while (TvShow::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $show = new TvShow;
            $show->slug = $slug;
        }

        $show->tmdb_id = $formatted['tmdb_id'];
        $show->title = $formatted['title'];
        $show->tagline = $formatted['tagline'] ?? $show->tagline;
        $show->overview = $formatted['overview'] ?? $show->overview;
        $show->poster_path = $formatted['poster_path'] ?? $show->poster_path;
        $show->backdrop_path = $formatted['backdrop_path'] ?? $show->backdrop_path;
        $show->first_air_date = $formatted['first_air_date'] ?? $show->first_air_date;
        $show->vote_average = $formatted['vote_average'] ?? $show->vote_average;
        $show->is_anime = $formatted['is_anime'] ?? $show->is_anime;
        $show->genres = $formatted['genres'] ?? $show->genres;
        if (! empty($formatted['cast'])) {
            $show->cast = $formatted['cast'];
        }
        $show->number_of_seasons = $formatted['number_of_seasons'] ?? 1;
        $show->number_of_episodes = $formatted['number_of_episodes'] ?? 10;
        $show->trailer_url = $formatted['trailer_url'] ?? $show->trailer_url;

        if ($isTrending) {
            $show->is_trending = true;
        }

        $show->save();

        // If no seasons exist, create Season 1 and Episode 1 with working embed servers
        if ($show->seasons()->count() === 0) {
            $season = $show->seasons()->create([
                'season_number' => 1,
                'name' => 'Season 1',
                'overview' => 'Season 1 of '.$show->title,
            ]);

            $season->episodes()->create([
                'tv_show_id' => $show->id,
                'episode_number' => 1,
                'name' => 'Episode 1',
                'overview' => 'Premiere episode.',
                'stream_servers' => $this->generateServers('tv', $show->tmdb_id),
            ]);
        }

        return $show;
    }

    public function fetchFeed(string $feed = 'popular', string $type = 'movie', int $page = 1): array
    {
        if (! $this->isConfigured()) {
            return ['status' => 'error', 'message' => 'TMDB API is not configured.'];
        }

        $endpoint = match ($feed) {
            'trending' => "trending/{$type}/day",
            'top_rated' => "{$type}/top_rated",
            default => "{$type}/popular",
        };

        try {
            $response = $this->client()->get("{$this->baseUrl}/{$endpoint}", $this->buildParams([
                'page' => $page,
                'language' => 'en-US',
            ]));

            if ($response->failed()) {
                return ['status' => 'error', 'message' => 'Failed to fetch from TMDB API: '.$response->status()];
            }

            $results = $response->json('results') ?? [];
            $formatted = array_map(function ($item) use ($type) {
                return [
                    'tmdb_id' => $item['id'],
                    'title' => $type === 'tv' ? ($item['name'] ?? 'Untitled') : ($item['title'] ?? 'Untitled'),
                    'overview' => $item['overview'] ?? '',
                    'poster_path' => ! empty($item['poster_path']) ? $this->imageBaseUrl.'/w500'.$item['poster_path'] : null,
                    'backdrop_path' => ! empty($item['backdrop_path']) ? $this->imageBaseUrl.'/w1280'.$item['backdrop_path'] : null,
                    'release_date' => $type === 'tv' ? ($item['first_air_date'] ?? null) : ($item['release_date'] ?? null),
                    'vote_average' => round((float) ($item['vote_average'] ?? 0), 1),
                    'type' => $type,
                ];
            }, $results);

            return [
                'status' => 'success',
                'page' => $response->json('page') ?? 1,
                'total_pages' => min($response->json('total_pages') ?? 1, 50),
                'results' => $formatted,
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'TMDB Feed exception: '.$e->getMessage()];
        }
    }

    public function bulkImport(string $type, array $tmdbIds): array
    {
        $imported = [];
        $failed = [];

        foreach ($tmdbIds as $id) {
            try {
                if ($type === 'tv') {
                    $fetched = $this->fetchTv((string) $id);
                    if ($fetched['status'] === 'success') {
                        $saved = $this->saveOrUpdateTv($fetched['data']);
                        $imported[] = $saved->title;
                    } else {
                        $failed[] = "ID {$id}: ".($fetched['message'] ?? 'Failed');
                    }
                } else {
                    $fetched = $this->fetchMovie((string) $id);
                    if ($fetched['status'] === 'success') {
                        $saved = $this->saveOrUpdateMovie($fetched['data']);
                        $imported[] = $saved->title;
                    } else {
                        $failed[] = "ID {$id}: ".($fetched['message'] ?? 'Failed');
                    }
                }
            } catch (\Exception $e) {
                $failed[] = "ID {$id}: ".$e->getMessage();
            }
        }

        return [
            'status' => 'success',
            'imported_count' => count($imported),
            'failed_count' => count($failed),
            'imported' => $imported,
            'failed' => $failed,
        ];
    }
}
