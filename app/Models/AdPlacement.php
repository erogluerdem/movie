<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdPlacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'type',
        'is_active',
        'ad_client',
        'ad_slot',
        'code',
        'image_url',
        'target_url',
        'device_target',
        'frequency_minutes',
        'impressions_count',
        'clicks_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'frequency_minutes' => 'integer',
        'impressions_count' => 'integer',
        'clicks_count' => 'integer',
    ];

    /**
     * Default core ad placements for the streaming platform.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function defaultPlacements(): array
    {
        return [
            [
                'slug' => 'header_banner',
                'name' => 'Sayfa Üstü Banner (Header)',
                'description' => 'Tüm sayfalarda başlık alanının altında veya üstünde yer alan 728x90 / duyarlı banner alanı.',
                'type' => 'adsense',
                'is_active' => false,
                'device_target' => 'all',
                'frequency_minutes' => 0,
            ],
            [
                'slug' => 'player_top',
                'name' => 'Video Oynatıcı Üstü (Player Top)',
                'description' => 'Film ve dizi detay sayfalarında video oynatıcının hemen üstünde yer alan en yüksek tıklanma oranına sahip alan.',
                'type' => 'adsense',
                'is_active' => true,
                'device_target' => 'all',
                'frequency_minutes' => 0,
            ],
            [
                'slug' => 'player_bottom',
                'name' => 'Video Oynatıcı Altı (Player Bottom)',
                'description' => 'Video oynatıcının hemen altında, sunucu butonları ile film bilgileri arasında yer alan duyarlı banner.',
                'type' => 'adsense',
                'is_active' => true,
                'device_target' => 'all',
                'frequency_minutes' => 0,
            ],
            [
                'slug' => 'in_feed_catalog',
                'name' => 'Katalog Akış İçi Reklam (In-Feed Grid)',
                'description' => 'Filmler, Diziler ve Arama listelerinde her 12 kartta bir doğal içerik akışını bozmayan dikey sponsorlu kart.',
                'type' => 'adsense',
                'is_active' => true,
                'device_target' => 'all',
                'frequency_minutes' => 0,
            ],
            [
                'slug' => 'sticky_footer',
                'name' => 'Yapışkan Alt Banner (Sticky Footer)',
                'description' => 'Sayfanın en altında sabit kalan, kullanıcı tarafından kapatılabilen modern floating banner.',
                'type' => 'adsense',
                'is_active' => false,
                'device_target' => 'all',
                'frequency_minutes' => 0,
            ],
            [
                'slug' => 'sidebar_banner',
                'name' => 'Detay Sayfası Yan Sütun (Sidebar)',
                'description' => 'Film ve dizi detay sayfalarında oyuncu listesi ve önerilen yapımların yanında 300x250 veya 300x600 dikey banner.',
                'type' => 'adsense',
                'is_active' => false,
                'device_target' => 'desktop',
                'frequency_minutes' => 0,
            ],
            [
                'slug' => 'interstitial_popunder',
                'name' => 'Oynatma Geçiş Reklamı (Interstitial / Popunder)',
                'description' => 'Kullanıcı "Oynat"a bastığında veya video başlarken tetiklenen, kullanıcıyı sıkmamak için saatte en fazla 1 kez çalışan akıllı geçiş reklamı.',
                'type' => 'custom_code',
                'is_active' => false,
                'device_target' => 'all',
                'frequency_minutes' => 30,
            ],
        ];
    }

    /**
     * Seeds or ensures default placements exist.
     */
    public static function seedDefaults(): void
    {
        foreach (static::defaultPlacements() as $p) {
            static::firstOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
