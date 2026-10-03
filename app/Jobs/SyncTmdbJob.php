<?php

namespace App\Jobs;

use App\Models\AppNotification;
use App\Models\Movie;
use App\Models\Setting;
use App\Models\TvShow;
use App\Models\User;
use App\Services\TmdbService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SyncTmdbJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $media = 'movie',
        public string $type = 'popular',
        public int $pages = 2,
        public int $minVotes = 30,
        public ?string $genre = null,
        public bool $curated = false,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TmdbService $tmdbService): void
    {
        try {
            Setting::set('tmdb_last_sync_status', 'running', 'tmdb');
            Setting::set('tmdb_last_sync_at', now()->toDateTimeString(), 'tmdb');

            $params = [
                '--media' => $this->media,
                '--type' => $this->type,
                '--pages' => $this->pages,
                '--min-votes' => $this->minVotes,
            ];

            if (! empty($this->genre)) {
                $params['--genre'] = $this->genre;
            }

            if ($this->curated) {
                $params['--curated'] = true;
            }

            $exitCode = Artisan::call('tmdb:sync', $params);

            if ($exitCode === 0) {
                Setting::set('tmdb_last_sync_status', 'success', 'tmdb');
                Setting::set('tmdb_last_sync_message', 'Successfully synchronized '.($this->curated ? 'curated catalog' : "{$this->media} ({$this->type})"), 'tmdb');

                // Feature 10: Dispatch in-app notification to users about newly added titles
                try {
                    $latestMovie = Movie::latest('created_at')->first();
                    $latestTv = TvShow::latest('created_at')->first();
                    $featuredTitle = $latestMovie?->title ?? $latestTv?->title ?? 'Yeni Yapımlar';
                    $featuredLink = $latestMovie ? ('/movie/'.($latestMovie->slug ?: 'watch-'.$latestMovie->id)) : ($latestTv ? ('/tv-show/'.$latestTv->slug) : '/movies');

                    $users = User::all();
                    foreach ($users as $user) {
                        AppNotification::create([
                            'user_id' => $user->id,
                            'title' => 'Yeni Yapım Eklendi!',
                            'message' => "Kütüphanemize yeni popüler içerikler eklendi: {$featuredTitle}",
                            'type' => 'content_ready',
                            'link' => $featuredLink,
                            'is_read' => false,
                        ]);
                    }
                } catch (\Throwable $notifEx) {
                    Log::warning('Failed to send sync notification to users: '.$notifEx->getMessage());
                }
            } else {
                Setting::set('tmdb_last_sync_status', 'failed', 'tmdb');
                Setting::set('tmdb_last_sync_message', 'Sync command completed with exit code: '.$exitCode, 'tmdb');
            }
        } catch (\Throwable $e) {
            Log::error('TMDB Sync Job failed: '.$e->getMessage(), ['exception' => $e]);
            Setting::set('tmdb_last_sync_status', 'failed', 'tmdb');
            Setting::set('tmdb_last_sync_message', 'Error: '.$e->getMessage(), 'tmdb');
            throw $e;
        }
    }
}
