<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdPlacement;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminAdController extends Controller
{
    public function index(): Response
    {
        $placements = AdPlacement::orderBy('id')->get();

        $settings = [
            'ads_enabled' => filter_var(Setting::get('ads_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'adblock_detector_enabled' => filter_var(Setting::get('adblock_detector_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'adblock_strict_mode' => filter_var(Setting::get('adblock_strict_mode', false), FILTER_VALIDATE_BOOLEAN),
            'adblock_title' => Setting::get('adblock_title', 'Reklam Engelleyici Algılandı'),
            'adblock_message' => Setting::get('adblock_message', 'Movie® platformundaki tüm film, dizi ve canlı yayınları ücretsiz ve kesintisiz 4K kalitede sunabilmemiz reklam gelirleriyle mümkün olmaktadır. Lütfen bizi desteklemek için reklam engelleyicinizi devre dışı bırakın.'),
            'global_ad_client' => Setting::get('global_ad_client', ''),
        ];

        return Inertia::render('Admin/Ads/Index', [
            'placements' => $placements,
            'settings' => $settings,
        ]);
    }

    public function updatePlacement(Request $request, AdPlacement $placement): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:adsense,custom_code,banner_image'],
            'is_active' => ['boolean'],
            'ad_client' => ['nullable', 'string', 'max:100'],
            'ad_slot' => ['nullable', 'string', 'max:100'],
            'code' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'target_url' => ['nullable', 'string', 'max:500'],
            'device_target' => ['required', 'string', 'in:all,desktop,mobile'],
            'frequency_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
        ]);

        $placement->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? $placement->description,
            'type' => $validated['type'],
            'is_active' => $request->boolean('is_active'),
            'ad_client' => $validated['ad_client'] ?? null,
            'ad_slot' => $validated['ad_slot'] ?? null,
            'code' => $validated['code'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'target_url' => $validated['target_url'] ?? null,
            'device_target' => $validated['device_target'],
            'frequency_minutes' => $validated['frequency_minutes'] ?? 0,
        ]);

        AuditLog::log('update_ad_placement', "Updated ad placement: {$placement->name}");

        return back()->with('success', "'{$placement->name}' reklam alanı güncellendi.");
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ads_enabled' => ['boolean'],
            'adblock_detector_enabled' => ['boolean'],
            'adblock_strict_mode' => ['boolean'],
            'adblock_title' => ['required', 'string', 'max:100'],
            'adblock_message' => ['required', 'string', 'max:1000'],
            'global_ad_client' => ['nullable', 'string', 'max:100'],
        ]);

        Setting::set('ads_enabled', $request->boolean('ads_enabled') ? '1' : '0');
        Setting::set('adblock_detector_enabled', $request->boolean('adblock_detector_enabled') ? '1' : '0');
        Setting::set('adblock_strict_mode', $request->boolean('adblock_strict_mode') ? '1' : '0');
        Setting::set('adblock_title', $validated['adblock_title']);
        Setting::set('adblock_message', $validated['adblock_message']);
        Setting::set('global_ad_client', $validated['global_ad_client'] ?? '');

        AuditLog::log('update_ad_settings', 'Updated advertising and AdBlock detector settings');

        return back()->with('success', 'Reklam motoru ve AdBlock algılayıcı ayarları başarıyla kaydedildi.');
    }

    public function resetDefaults(): RedirectResponse
    {
        AdPlacement::seedDefaults();

        AuditLog::log('reset_ad_placements', 'Reset ad placements to defaults');

        return back()->with('success', 'Varsayılan reklam yerleşimleri yenilendi.');
    }
}
