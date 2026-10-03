<?php

use App\Http\Controllers\Admin\AdminAdController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminAnnouncementController;
use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminCollectionController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminMovieController;
use App\Http\Controllers\Admin\AdminRequestController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminSportController;
use App\Http\Controllers\Admin\AdminStreamReportController;
use App\Http\Controllers\Admin\AdminTmdbBulkController;
use App\Http\Controllers\Admin\AdminTmdbController;
use App\Http\Controllers\Admin\AdminTvShowController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomListController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\StreamReportController;
use App\Http\Controllers\TvShowController;
use App\Http\Controllers\WatchlistController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Localization
Route::post('/locale', [LocaleController::class, 'switch'])->name('locale.switch');
Route::get('/locale/{locale}', [LocaleController::class, 'switchGet'])->name('locale.switch.get');

// Home & Trending
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/trending', [HomeController::class, 'trending'])->name('trending');
Route::get('/api/random-pick', [HomeController::class, 'randomPick'])->name('api.random-pick');

// Movies
Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
Route::get('/movie/{slug}', [MovieController::class, 'show'])->name('movies.show');

// TV Shows & Anime
Route::get('/tv-shows', [TvShowController::class, 'index'])->name('tv-shows.index');
Route::get('/tv-show/{slug}', [TvShowController::class, 'show'])->name('tv-shows.show');
Route::get('/anime', [TvShowController::class, 'anime'])->name('anime.index');
Route::get('/episode-details', [TvShowController::class, 'episodeDetails'])->name('episode.details');

// Sports
Route::get('/sports', [SportController::class, 'index'])->name('sports.index');
Route::get('/sports/event/{slug}', [SportController::class, 'event'])->name('sports.event');

// Search
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/api/search/live', [SearchController::class, 'live'])->name('api.search.live');
Route::get('/api/search/mega', [SearchController::class, 'mega'])->name('api.search.mega');

// Watchlist
Route::get('/watchlist', [WatchlistController::class, 'index'])->name('watchlist.index');
Route::get('/my-watchlist', [WatchlistController::class, 'index'])->name('watchlist.my');
Route::post('/api/watchlist/toggle', [WatchlistController::class, 'toggle'])->name('watchlist.toggle');
Route::get('/api/watchlist/check', [WatchlistController::class, 'check'])->name('watchlist.check');

// Custom Lists (Letterboxd-style Community & User Lists)
Route::get('/lists', [CustomListController::class, 'index'])->name('lists.index');
Route::get('/lists/{slug}', [CustomListController::class, 'show'])->name('lists.show');
Route::post('/lists', [CustomListController::class, 'store'])->name('lists.store');
Route::put('/lists/{customList}', [CustomListController::class, 'update'])->name('lists.update');
Route::delete('/lists/{customList}', [CustomListController::class, 'destroy'])->name('lists.destroy');
Route::post('/lists/{customList}/items', [CustomListController::class, 'addItem'])->name('lists.items.add');
Route::post('/lists/{customList}/items/remove', [CustomListController::class, 'removeItem'])->name('lists.items.remove');
Route::get('/api/custom-lists/status', [CustomListController::class, 'itemStatus'])->name('api.custom-lists.status');

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
});

Route::get('/terms', function () {
    return Inertia::render('Static/Terms');
})->name('terms');

Route::get('/privacy', function () {
    return Inertia::render('Static/Privacy');
})->name('privacy');

Route::get('/contact', function () {
    return Inertia::render('Static/Contact');
})->name('contact');

Route::get('/my-requests', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route('dashboard.requests');
})->name('my-requests');

// User Dashboard & Panels (Protected by auth)
Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/watchlist', [DashboardController::class, 'watchlist'])->name('dashboard.watchlist');
    Route::get('/history', [DashboardController::class, 'history'])->name('dashboard.history');
    Route::get('/requests', [DashboardController::class, 'requests'])->name('dashboard.requests');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('dashboard.settings');

    // Actions
    Route::post('/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
    Route::post('/password', [DashboardController::class, 'updatePassword'])->name('dashboard.password.update');
    Route::post('/preferences', [DashboardController::class, 'updatePreferences'])->name('dashboard.preferences.update');
    Route::post('/requests', [DashboardController::class, 'submitRequest'])->name('dashboard.requests.submit');
    Route::delete('/requests/{id}', [DashboardController::class, 'deleteRequest'])->name('dashboard.requests.delete');
    Route::delete('/history', [DashboardController::class, 'clearHistory'])->name('dashboard.history.clear');
    Route::delete('/history/{id}', [DashboardController::class, 'removeHistoryItem'])->name('dashboard.history.remove');
});

// Watch tracking API
Route::post('/api/watch/progress', [DashboardController::class, 'trackProgress'])->name('api.watch.progress');

// Reviews & Ratings API
Route::post('/api/reviews', [ReviewController::class, 'store'])->middleware('auth')->name('reviews.store');
Route::post('/api/reviews/{review}/like', [ReviewController::class, 'toggleLike'])->middleware('auth')->name('reviews.like');
Route::delete('/api/reviews/{review}', [ReviewController::class, 'destroy'])->middleware('auth')->name('reviews.destroy');

// In-App Notification Center API
Route::get('/api/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/api/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::post('/api/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

// Dead Link & Stream Issue Reporting API
Route::post('/api/reports', [StreamReportController::class, 'store'])->name('reports.store');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Panel (Protected by auth and admin middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::post('/clear-cache', [AdminController::class, 'clearCache'])->name('clear-cache');

    // Streaming Analytics & Insights
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');

    // TMDB Integration & Bulk Importer
    Route::post('/tmdb/fetch', [AdminController::class, 'fetchTmdb'])->name('tmdb.fetch');
    Route::get('/tmdb', [AdminTmdbController::class, 'index'])->name('tmdb.index');
    Route::get('/tmdb/bulk', [AdminTmdbBulkController::class, 'index'])->name('tmdb.bulk');
    Route::post('/tmdb/bulk-import', [AdminTmdbBulkController::class, 'importBatch'])->name('tmdb.bulk-import');
    Route::post('/tmdb/sync', [AdminTmdbController::class, 'sync'])->name('tmdb.sync');
    Route::post('/tmdb/search', [AdminTmdbController::class, 'search'])->name('tmdb.search');
    Route::post('/tmdb/import-single', [AdminTmdbController::class, 'importSingle'])->name('tmdb.import-single');
    Route::post('/tmdb/settings', [AdminTmdbController::class, 'updateSettings'])->name('tmdb.settings');

    // Announcements & Broadcast Notifications
    Route::get('/announcements', [AdminAnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements/banner', [AdminAnnouncementController::class, 'updateBanner'])->name('announcements.banner');
    Route::post('/announcements/broadcast', [AdminAnnouncementController::class, 'sendBroadcast'])->name('announcements.broadcast');

    // Curated Collections & Home Showcases
    Route::get('/collections', [AdminCollectionController::class, 'index'])->name('collections.index');
    Route::post('/collections', [AdminCollectionController::class, 'store'])->name('collections.store');
    Route::put('/collections/{collection}', [AdminCollectionController::class, 'update'])->name('collections.update');
    Route::delete('/collections/{collection}', [AdminCollectionController::class, 'destroy'])->name('collections.destroy');
    Route::post('/collections/{collection}/items', [AdminCollectionController::class, 'addItem'])->name('collections.add-item');
    Route::delete('/collections/{collection}/items/{item}', [AdminCollectionController::class, 'removeItem'])->name('collections.remove-item');

    // Stream Issue & Dead Link Reports Moderation
    Route::get('/reports', [AdminStreamReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/{streamReport}/status', [AdminStreamReportController::class, 'updateStatus'])->name('reports.update-status');
    Route::delete('/reports/{streamReport}', [AdminStreamReportController::class, 'destroy'])->name('reports.destroy');

    // Security & Audit Logs
    Route::get('/logs', [AdminAuditLogController::class, 'index'])->name('logs.index');

    // Movies Management
    Route::get('/movies', [AdminMovieController::class, 'index'])->name('movies.index');
    Route::post('/movies', [AdminMovieController::class, 'store'])->name('movies.store');
    Route::post('/movies/import-tmdb', [AdminMovieController::class, 'importFromTmdb'])->name('movies.import-tmdb');
    Route::put('/movies/{movie}', [AdminMovieController::class, 'update'])->name('movies.update');
    Route::delete('/movies/{movie}', [AdminMovieController::class, 'destroy'])->name('movies.destroy');
    Route::post('/movies/{movie}/toggle-trending', [AdminMovieController::class, 'toggleTrending'])->name('movies.toggle-trending');
    Route::post('/movies/{movie}/toggle-featured', [AdminMovieController::class, 'toggleFeatured'])->name('movies.toggle-featured');

    // TV Shows & Anime Management
    Route::get('/tv-shows', [AdminTvShowController::class, 'index'])->name('tv-shows.index');
    Route::get('/tv-shows/{tvShow}', [AdminTvShowController::class, 'show'])->name('tv-shows.show');
    Route::post('/tv-shows', [AdminTvShowController::class, 'store'])->name('tv-shows.store');
    Route::post('/tv-shows/import-tmdb', [AdminTvShowController::class, 'importFromTmdb'])->name('tv-shows.import-tmdb');
    Route::put('/tv-shows/{tvShow}', [AdminTvShowController::class, 'update'])->name('tv-shows.update');
    Route::delete('/tv-shows/{tvShow}', [AdminTvShowController::class, 'destroy'])->name('tv-shows.destroy');
    Route::post('/tv-shows/{tvShow}/toggle-trending', [AdminTvShowController::class, 'toggleTrending'])->name('tv-shows.toggle-trending');
    Route::post('/tv-shows/{tvShow}/toggle-anime', [AdminTvShowController::class, 'toggleAnime'])->name('tv-shows.toggle-anime');
    Route::post('/tv-shows/{tvShow}/seasons', [AdminTvShowController::class, 'storeSeason'])->name('tv-shows.seasons.store');
    Route::delete('/seasons/{seasonId}', [AdminTvShowController::class, 'destroySeason'])->name('tv-shows.seasons.destroy');
    Route::post('/seasons/{seasonId}/episodes', [AdminTvShowController::class, 'storeEpisode'])->name('tv-shows.episodes.store');
    Route::put('/episodes/{episodeId}', [AdminTvShowController::class, 'updateEpisode'])->name('tv-shows.episodes.update');
    Route::delete('/episodes/{episodeId}', [AdminTvShowController::class, 'destroyEpisode'])->name('tv-shows.episodes.destroy');

    // Live Sports Management
    Route::get('/sports', [AdminSportController::class, 'index'])->name('sports.index');
    Route::post('/sports', [AdminSportController::class, 'store'])->name('sports.store');
    Route::put('/sports/{sport}', [AdminSportController::class, 'update'])->name('sports.update');
    Route::delete('/sports/{sport}', [AdminSportController::class, 'destroy'])->name('sports.destroy');
    Route::post('/sports/{sport}/toggle-live', [AdminSportController::class, 'toggleLive'])->name('sports.toggle-live');

    // Content Requests Management
    Route::get('/requests', [AdminRequestController::class, 'index'])->name('requests.index');
    Route::put('/requests/{contentRequest}', [AdminRequestController::class, 'update'])->name('requests.update');
    Route::delete('/requests/{contentRequest}', [AdminRequestController::class, 'destroy'])->name('requests.destroy');

    // Review Moderation Management
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    // Users Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle-role', [AdminUserController::class, 'toggleRole'])->name('users.toggle-role');
    Route::post('/users/{user}/toggle-ban', [AdminUserController::class, 'toggleBan'])->name('users.toggle-ban');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Site Settings & Backup
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
    Route::get('/backup/download', [AdminSettingController::class, 'downloadBackup'])->name('backup.download');

    // Advertising & Monetization Engine
    Route::get('/ads', [AdminAdController::class, 'index'])->name('ads.index');
    Route::post('/ads/placements/{placement}', [AdminAdController::class, 'updatePlacement'])->name('ads.placement.update');
    Route::post('/ads/settings', [AdminAdController::class, 'updateSettings'])->name('ads.settings.update');
    Route::post('/ads/reset', [AdminAdController::class, 'resetDefaults'])->name('ads.reset');
});
