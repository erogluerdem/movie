<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Movie;
use App\Models\TvShow;
use Illuminate\Support\Facades\File;

#[Signature('sitemap:generate')]
#[Description('Generates sitemap.xml for SEO')]
class GenerateSitemap extends Command
{
    public function handle()
    {
        $this->info('Generating sitemap...');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Home
        $xml .= '<url><loc>' . url('/') . '</loc><changefreq>daily</changefreq><priority>1.0</priority></url>';

        // Movies
        $movies = Movie::select('slug', 'updated_at')->get();
        foreach ($movies as $movie) {
            $xml .= '<url>';
            $xml .= '<loc>' . route('movies.show', $movie->slug) . '</loc>';
            $xml .= '<lastmod>' . $movie->updated_at->toAtomString() . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        // TV Shows
        $tvShows = TvShow::select('slug', 'updated_at')->get();
        foreach ($tvShows as $show) {
            $xml .= '<url>';
            $xml .= '<loc>' . route('tv-shows.show', $show->slug) . '</loc>';
            $xml .= '<lastmod>' . $show->updated_at->toAtomString() . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        File::put(public_path('sitemap.xml'), $xml);

        $this->info('Sitemap generated successfully at public/sitemap.xml!');
    }
}
