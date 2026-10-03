<?php

namespace App\Console\Commands;

use App\Services\TmdbService;
use Illuminate\Console\Command;

class TmdbSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tmdb:sync
                            {--media=movie : Media type to fetch (movie, tv, anime)}
                            {--type=popular : List type (popular, trending, top_rated, upcoming, now_playing, anime)}
                            {--genre= : Genre filter (action, scifi, comedy, horror, drama, thriller, animation, crime, adventure, fantasy)}
                            {--pages=2 : Number of pages to fetch (20 items per page)}
                            {--min-votes=30 : Minimum vote count filter to avoid low-quality entries}
                            {--id= : Import a single movie or TV show by TMDB ID}
                            {--search= : Search and import movies by title}
                            {--curated : Sync a curated library across movies, classics, series and anime}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync movies, TV shows, anime, cast, and YouTube trailers from TMDB API';

    /**
     * Execute the console command.
     */
    public function handle(TmdbService $tmdbService): int
    {
        if (! $tmdbService->isConfigured()) {
            $this->error('TMDB API Key is not set.');
            $this->line('Please add TMDB_API_KEY or TMDB_READ_TOKEN to your .env file.');
            $this->line('Get a free API key at: https://www.themoviedb.org/settings/api');

            return self::FAILURE;
        }

        // Curated multi-category sync
        if ($this->option('curated')) {
            return $this->runCuratedSync($tmdbService);
        }

        $media = strtolower((string) $this->option('media'));

        // Single ID import
        $singleId = $this->option('id');
        if (! empty($singleId)) {
            $this->info("Fetching TMDB ID: {$singleId} ({$media})...");
            if ($media === 'tv' || $media === 'anime') {
                $show = $tmdbService->saveOrUpdateTv($singleId);
                if ($show) {
                    $this->info("Successfully imported TV Show: {$show->title} (Seasons: {$show->number_of_seasons})");

                    return self::SUCCESS;
                }
            } else {
                $movie = $tmdbService->saveOrUpdateMovie($singleId);
                if ($movie) {
                    $this->info("Successfully imported: {$movie->title} (Trailer: ".($movie->trailer_url ?? 'None').')');

                    return self::SUCCESS;
                }
            }

            $this->error("Failed to fetch or save {$media} with ID: {$singleId}");

            return self::FAILURE;
        }

        // Search and import
        $searchQuery = $this->option('search');
        if (! empty($searchQuery)) {
            $this->info("Searching TMDB for: '{$searchQuery}'...");
            $response = ($media === 'tv' || $media === 'anime')
                ? $tmdbService->searchTv($searchQuery)
                : $tmdbService->search($searchQuery);

            $results = $response['results'] ?? [];

            if (empty($results)) {
                $this->warn('No titles found matching the query.');

                return self::SUCCESS;
            }

            $this->info('Found '.count($results).' items. Importing...');
            $importedCount = 0;
            foreach ($results as $item) {
                if ($media === 'tv' || $media === 'anime') {
                    $saved = $tmdbService->saveOrUpdateTv($item);
                } else {
                    $saved = $tmdbService->saveOrUpdateMovie($item);
                }

                if ($saved) {
                    $importedCount++;
                    $this->line("  [✓] Imported: {$saved->title} (Trailer: ".($saved->trailer_url ?? 'None').')');
                }
                usleep(150000); // 150ms pause
            }

            $this->info("Completed! Imported/Updated {$importedCount} titles.");

            return self::SUCCESS;
        }

        // Batch import
        $type = strtolower((string) $this->option('type'));
        $genre = strtolower((string) $this->option('genre'));
        $pages = max(1, (int) $this->option('pages'));
        $minVotes = (int) $this->option('min-votes');

        $this->info("Starting TMDB sync [Media: {$media}, Type: {$type}, Genre: ".($genre ?: 'All').", Pages: {$pages}, Min Votes: {$minVotes}]...");

        $totalProcessed = 0;
        $totalImported = 0;

        $movieGenreMap = [
            'action' => 28,
            'adventure' => 12,
            'animation' => 16,
            'comedy' => 35,
            'crime' => 80,
            'documentary' => 99,
            'drama' => 18,
            'family' => 10751,
            'fantasy' => 14,
            'horror' => 27,
            'romance' => 10749,
            'scifi' => 878,
            'thriller' => 53,
            'western' => 37,
        ];

        $tvGenreMap = [
            'action' => 10759,
            'adventure' => 10759,
            'animation' => 16,
            'comedy' => 35,
            'crime' => 80,
            'documentary' => 99,
            'drama' => 18,
            'family' => 10751,
            'mystery' => 9648,
            'scifi' => 10765,
            'fantasy' => 10765,
            'western' => 37,
        ];

        $isTv = ($media === 'tv' || $media === 'anime' || $type === 'anime');

        for ($page = 1; $page <= $pages; $page++) {
            $this->line("Fetching page {$page}/{$pages}...");

            if (! empty($genre)) {
                if ($isTv) {
                    $genreId = $tvGenreMap[$genre] ?? (is_numeric($genre) ? (int) $genre : null);
                    $response = $tmdbService->discoverTv([
                        'with_genres' => $genreId,
                        'sort_by' => 'vote_count.desc',
                        'page' => $page,
                    ]);
                } else {
                    $genreId = $movieGenreMap[$genre] ?? (is_numeric($genre) ? (int) $genre : null);
                    $response = $tmdbService->discoverMovies([
                        'with_genres' => $genreId,
                        'sort_by' => 'vote_count.desc',
                        'page' => $page,
                    ]);
                }
            } else {
                $response = match (true) {
                    $media === 'anime' || $type === 'anime' => $tmdbService->getAnime($page),
                    $isTv && $type === 'trending' => $tmdbService->getTrendingTv('week', $page),
                    $isTv && $type === 'top_rated' => $tmdbService->getTopRatedTv($page),
                    $isTv => $tmdbService->getPopularTv($page),
                    $type === 'trending' => $tmdbService->getTrending('week', $page),
                    $type === 'top_rated' => $tmdbService->getTopRated($page),
                    $type === 'upcoming' => $tmdbService->getUpcoming($page),
                    $type === 'now_playing' => $tmdbService->getNowPlaying($page),
                    default => $tmdbService->getPopular($page),
                };
            }

            if (! $response || empty($response['results'])) {
                $this->warn("No results returned for page {$page}.");
                break;
            }

            $items = $response['results'];
            foreach ($items as $itemData) {
                $totalProcessed++;
                $voteCount = $itemData['vote_count'] ?? 0;
                $hasPoster = ! empty($itemData['poster_path']);

                // Filter out low quality / obscure entries
                if ($voteCount < $minVotes || ! $hasPoster) {
                    continue;
                }

                $isTrending = ($type === 'trending');

                if ($isTv) {
                    $saved = $tmdbService->saveOrUpdateTv($itemData, $isTrending);
                    if ($saved) {
                        $totalImported++;
                        $this->line("  [✓] TV: {$saved->title} ({$saved->first_air_date}) - Seasons: {$saved->number_of_seasons} - Trailer: ".($saved->trailer_url ? 'Yes' : 'No'));
                    }
                } else {
                    $saved = $tmdbService->saveOrUpdateMovie($itemData, $isTrending);
                    if ($saved) {
                        $totalImported++;
                        $this->line("  [✓] Movie: {$saved->title} ({$saved->release_date}) - Trailer: ".($saved->trailer_url ? 'Yes' : 'No'));
                    }
                }

                usleep(120000); // 120ms sleep
            }
        }

        $this->newLine();
        $this->info("Sync completed! Processed: {$totalProcessed} | Successfully saved/updated: {$totalImported} items.");

        return self::SUCCESS;
    }

    /**
     * Run an extensive curated synchronization across top movies, series, and anime.
     */
    protected function runCuratedSync(TmdbService $tmdbService): int
    {
        $this->info('Starting Curated Full Catalog Sync...');

        $plan = [
            ['media' => 'movie', 'type' => 'popular', 'pages' => 3, 'min_votes' => 50],
            ['media' => 'movie', 'type' => 'top_rated', 'pages' => 3, 'min_votes' => 100],
            ['media' => 'movie', 'type' => 'trending', 'pages' => 2, 'min_votes' => 50],
            ['media' => 'movie', 'type' => 'upcoming', 'pages' => 2, 'min_votes' => 20],
            ['media' => 'tv', 'type' => 'popular', 'pages' => 2, 'min_votes' => 40],
            ['media' => 'tv', 'type' => 'top_rated', 'pages' => 2, 'min_votes' => 50],
            ['media' => 'anime', 'type' => 'anime', 'pages' => 2, 'min_votes' => 30],
        ];

        foreach ($plan as $step) {
            $this->newLine();
            $this->call('tmdb:sync', [
                '--media' => $step['media'],
                '--type' => $step['type'],
                '--pages' => $step['pages'],
                '--min-votes' => $step['min_votes'],
            ]);
        }

        $this->newLine();
        $this->info('Curated Full Catalog Sync Completed Successfully!');

        return self::SUCCESS;
    }
}
