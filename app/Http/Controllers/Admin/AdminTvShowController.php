<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Season;
use App\Models\TvShow;
use App\Services\TmdbService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminTvShowController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TvShow::withCount(['seasons']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('tmdb_id', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_anime') && $request->input('is_anime') !== '') {
            $query->where('is_anime', filter_var($request->input('is_anime'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('trending') && $request->input('trending') !== '') {
            $query->where('is_trending', filter_var($request->input('trending'), FILTER_VALIDATE_BOOLEAN));
        }

        $shows = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return Inertia::render('Admin/TvShows/Index', [
            'shows' => $shows,
            'filters' => $request->only(['search', 'is_anime', 'trending']),
        ]);
    }

    public function show(TvShow $tvShow): Response
    {
        $tvShow->load(['seasons.episodes']);

        return Inertia::render('Admin/TvShows/Episodes', [
            'show' => $tvShow,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tv_shows,slug'],
            'tmdb_id' => ['nullable', 'string', 'max:50'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'poster_path' => ['nullable', 'string', 'max:500'],
            'backdrop_path' => ['nullable', 'string', 'max:500'],
            'first_air_date' => ['nullable', 'string', 'max:20'],
            'vote_average' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'status' => ['nullable', 'string', 'max:50'],
            'number_of_seasons' => ['nullable', 'integer', 'min:1'],
            'number_of_episodes' => ['nullable', 'integer', 'min:1'],
            'is_trending' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_anime' => ['boolean'],
            'trailer_url' => ['nullable', 'string', 'max:500'],
            'genres' => ['nullable', 'array'],
        ]);

        if (empty($validated['slug'])) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $count = 1;
            while (TvShow::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$count++;
            }
            $validated['slug'] = $slug;
        }

        $show = TvShow::create($validated);

        // Automatically create Season 1 if none exists
        Season::create([
            'tv_show_id' => $show->id,
            'season_number' => 1,
            'name' => 'Season 1',
        ]);

        return back()->with('success', 'TV Show created successfully with Season 1 initialized!');
    }

    public function update(Request $request, TvShow $tvShow): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:tv_shows,slug,'.$tvShow->id],
            'tmdb_id' => ['nullable', 'string', 'max:50'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'poster_path' => ['nullable', 'string', 'max:500'],
            'backdrop_path' => ['nullable', 'string', 'max:500'],
            'first_air_date' => ['nullable', 'string', 'max:20'],
            'vote_average' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'status' => ['nullable', 'string', 'max:50'],
            'number_of_seasons' => ['nullable', 'integer', 'min:1'],
            'number_of_episodes' => ['nullable', 'integer', 'min:1'],
            'is_trending' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_anime' => ['boolean'],
            'trailer_url' => ['nullable', 'string', 'max:500'],
            'genres' => ['nullable', 'array'],
        ]);

        $tvShow->update($validated);

        return back()->with('success', 'TV Show updated successfully!');
    }

    public function destroy(TvShow $tvShow): RedirectResponse
    {
        $tvShow->delete();

        return back()->with('success', 'TV Show deleted successfully.');
    }

    public function toggleTrending(TvShow $tvShow): RedirectResponse
    {
        $tvShow->update(['is_trending' => ! $tvShow->is_trending]);

        return back()->with('success', 'Trending status updated!');
    }

    public function toggleAnime(TvShow $tvShow): RedirectResponse
    {
        $tvShow->update(['is_anime' => ! $tvShow->is_anime]);

        return back()->with('success', 'Anime category updated!');
    }

    public function storeSeason(Request $request, TvShow $tvShow): RedirectResponse
    {
        $validated = $request->validate([
            'season_number' => ['required', 'integer', 'min:1'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        Season::firstOrCreate([
            'tv_show_id' => $tvShow->id,
            'season_number' => $validated['season_number'],
        ], [
            'name' => $validated['name'] ?? ('Season '.$validated['season_number']),
        ]);

        $tvShow->update(['number_of_seasons' => Season::where('tv_show_id', $tvShow->id)->count()]);

        return back()->with('success', 'Season added successfully!');
    }

    public function destroySeason(int $seasonId): RedirectResponse
    {
        $season = Season::findOrFail($seasonId);
        $showId = $season->tv_show_id;
        $season->delete();

        $tvShow = TvShow::find($showId);
        if ($tvShow) {
            $tvShow->update(['number_of_seasons' => Season::where('tv_show_id', $tvShow->id)->count()]);
        }

        return back()->with('success', 'Season deleted.');
    }

    public function storeEpisode(Request $request, int $seasonId): RedirectResponse
    {
        $validated = $request->validate([
            'episode_number' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'still_path' => ['nullable', 'string', 'max:500'],
            'duration' => ['nullable', 'string', 'max:50'],
            'air_date' => ['nullable', 'string', 'max:20'],
            'stream_servers' => ['nullable', 'array'],
        ]);

        $validated['season_id'] = $seasonId;

        Episode::create($validated);

        // Update show's episode count
        $season = Season::find($seasonId);
        if ($season && $season->tv_show_id) {
            $show = TvShow::find($season->tv_show_id);
            if ($show) {
                $totalEpisodes = Episode::whereIn('season_id', $show->seasons->pluck('id'))->count();
                $show->update(['number_of_episodes' => $totalEpisodes]);
            }
        }

        return back()->with('success', 'Episode created successfully!');
    }

    public function updateEpisode(Request $request, int $episodeId): RedirectResponse
    {
        $episode = Episode::findOrFail($episodeId);

        $validated = $request->validate([
            'episode_number' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'still_path' => ['nullable', 'string', 'max:500'],
            'duration' => ['nullable', 'string', 'max:50'],
            'air_date' => ['nullable', 'string', 'max:20'],
            'stream_servers' => ['nullable', 'array'],
        ]);

        $episode->update($validated);

        return back()->with('success', 'Episode updated successfully!');
    }

    public function destroyEpisode(int $episodeId): RedirectResponse
    {
        $episode = Episode::findOrFail($episodeId);
        $seasonId = $episode->season_id;
        $episode->delete();

        $season = Season::find($seasonId);
        if ($season && $season->tv_show_id) {
            $show = TvShow::find($season->tv_show_id);
            if ($show) {
                $totalEpisodes = Episode::whereIn('season_id', $show->seasons->pluck('id'))->count();
                $show->update(['number_of_episodes' => $totalEpisodes]);
            }
        }

        return back()->with('success', 'Episode deleted.');
    }

    public function importFromTmdb(Request $request, TmdbService $tmdbService): RedirectResponse
    {
        $input = trim((string) $request->input('tmdb_id', ''));

        if (empty($input)) {
            return back()->with('error', 'Please provide a TMDB ID or TMDB URL.');
        }

        // Support TMDB URL format (e.g. https://www.themoviedb.org/tv/1399-game-of-thrones)
        if (preg_match('/tv\/(\d+)/', $input, $matches)) {
            $input = $matches[1];
        }

        $show = $tmdbService->saveOrUpdateTv($input);

        if (! $show) {
            return back()->with('error', "Could not import TV show with ID: {$input}. Please verify the TMDB ID.");
        }

        return back()->with('success', "TV Show '{$show->title}' imported successfully!");
    }
}
