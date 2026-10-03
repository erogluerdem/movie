<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| TMDB Automated Scheduled Sync
|--------------------------------------------------------------------------
| Runs daily at 04:00 AM (or per frequency set in Admin Panel)
| Only executes when 'tmdb_auto_sync_enabled' is active in site settings.
*/
Schedule::call(function () {
    if (! filter_var(Setting::get('tmdb_auto_sync_enabled', true), FILTER_VALIDATE_BOOLEAN)) {
        return;
    }

    $pages = (int) Setting::get('tmdb_auto_sync_pages', 2);
    $minVotes = (int) Setting::get('tmdb_auto_sync_min_votes', 40);

    // Sync latest trending movies
    Artisan::call('tmdb:sync', [
        '--media' => 'movie',
        '--type' => 'trending',
        '--pages' => $pages,
        '--min-votes' => $minVotes,
    ]);

    // Sync latest upcoming movies
    Artisan::call('tmdb:sync', [
        '--media' => 'movie',
        '--type' => 'upcoming',
        '--pages' => 1,
        '--min-votes' => 20,
    ]);

    // Sync trending TV shows
    Artisan::call('tmdb:sync', [
        '--media' => 'tv',
        '--type' => 'trending',
        '--pages' => 1,
        '--min-votes' => $minVotes,
    ]);

    Setting::set('tmdb_last_sync_status', 'success', 'tmdb');
    Setting::set('tmdb_last_sync_at', now()->toDateTimeString(), 'tmdb');
    Setting::set('tmdb_last_sync_message', 'Zamanlanmış otomatik senkronizasyon tamamlandı.', 'tmdb');
})
    ->dailyAt('04:00')
    ->name('tmdb-auto-sync')
    ->withoutOverlapping();
