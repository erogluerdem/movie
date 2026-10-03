<?php

namespace App\Http\Middleware;

use App\Models\AdPlacement;
use App\Models\ContentRequest;
use App\Models\Review;
use App\Models\Setting;
use App\Models\SportMatch;
use App\Models\StreamReport;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'locale' => fn () => app()->getLocale(),
            'locales' => [
                'tr' => ['name' => 'Türkçe', 'flag' => '🇹🇷', 'code' => 'tr'],
                'en' => ['name' => 'English', 'flag' => '🇬🇧', 'code' => 'en'],
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                ] : null,
            ],
            'admin_badges' => fn () => ($user && $user->isAdmin()) ? [
                'pending_requests' => ContentRequest::where('status', 'pending')->count(),
                'pending_reports' => StreamReport::where('status', 'pending')->count(),
                'live_sports' => SportMatch::where('is_live', true)->count(),
                'total_reviews' => Review::count(),
            ] : null,
            'site_announcement' => fn () => filter_var(Setting::get('announcement_enabled', false), FILTER_VALIDATE_BOOLEAN)
                ? Setting::get('announcement_text', '')
                : null,
            'ads' => fn () => [
                'enabled' => filter_var(Setting::get('ads_enabled', true), FILTER_VALIDATE_BOOLEAN),
                'global_ad_client' => Setting::get('global_ad_client', ''),
                'adblock' => [
                    'enabled' => filter_var(Setting::get('adblock_detector_enabled', true), FILTER_VALIDATE_BOOLEAN),
                    'strict_mode' => filter_var(Setting::get('adblock_strict_mode', false), FILTER_VALIDATE_BOOLEAN),
                    'title' => Setting::get('adblock_title', 'Reklam Engelleyici Algılandı'),
                    'message' => Setting::get('adblock_message', 'Movie® platformundaki tüm film, dizi ve canlı yayınları ücretsiz ve kesintisiz 4K kalitede sunabilmemiz reklam gelirleriyle mümkün olmaktadır. Lütfen bizi desteklemek için reklam engelleyicinizi devre dışı bırakın.'),
                ],
                'placements' => AdPlacement::where('is_active', true)->get()->keyBy('slug'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
