<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminSettingController extends Controller
{
    public function index(): Response
    {
        $settings = [
            'site_name' => Setting::get('site_name', 'Movie®'),
            'site_tagline' => Setting::get('site_tagline', 'Watch Free Movies & TV Shows Online in HD/4K'),
            'announcement_enabled' => filter_var(Setting::get('announcement_enabled', false), FILTER_VALIDATE_BOOLEAN),
            'announcement_text' => Setting::get('announcement_text', 'Welcome to Movie®! Enjoy 4K HDR streaming with ultra-fast playback.'),
            'tmdb_api_key' => Setting::get('tmdb_api_key', ''),
            'default_player_server' => Setting::get('default_player_server', 'VidCloud HD'),
            'maintenance_mode' => filter_var(Setting::get('maintenance_mode', false), FILTER_VALIDATE_BOOLEAN),
        ];

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'announcement_enabled' => ['boolean'],
            'announcement_text' => ['nullable', 'string', 'max:500'],
            'tmdb_api_key' => ['nullable', 'string', 'max:100'],
            'default_player_server' => ['nullable', 'string', 'max:100'],
            'maintenance_mode' => ['boolean'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, is_bool($value) ? ($value ? '1' : '0') : (string) $value);
        }

        AuditLog::log('update_settings', 'Updated system settings');

        return back()->with('success', 'Site settings saved successfully!');
    }

    public function downloadBackup(): BinaryFileResponse|RedirectResponse
    {
        $dbPath = database_path('database.sqlite');

        if (! file_exists($dbPath)) {
            return back()->with('error', 'SQLite veritabanı dosyası bulunamadı.');
        }

        AuditLog::log('download_backup', 'Database backup downloaded');

        return response()->download($dbPath, 'movie-backup-'.date('Y-m-d_His').'.sqlite');
    }
}
