<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Services\TmdbService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminMovieController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Movie::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('tmdb_id', 'like', "%{$search}%");
            });
        }

        if ($request->has('trending') && $request->input('trending') !== '') {
            $query->where('is_trending', filter_var($request->input('trending'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('featured') && $request->input('featured') !== '') {
            $query->where('is_featured', filter_var($request->input('featured'), FILTER_VALIDATE_BOOLEAN));
        }

        $movies = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Movies/Index', [
            'movies' => $movies,
            'filters' => $request->only(['search', 'trending', 'featured']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:movies,slug'],
            'tmdb_id' => ['nullable', 'string', 'max:50'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'poster_path' => ['nullable', 'string', 'max:500'],
            'backdrop_path' => ['nullable', 'string', 'max:500'],
            'release_date' => ['nullable', 'string', 'max:20'],
            'vote_average' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'runtime' => ['nullable', 'string', 'max:50'],
            'director' => ['nullable', 'string', 'max:100'],
            'trailer_url' => ['nullable', 'string', 'max:500'],
            'is_trending' => ['boolean'],
            'is_featured' => ['boolean'],
            'genres' => ['nullable', 'array'],
            'stream_servers' => ['nullable', 'array'],
        ]);

        if (empty($validated['slug'])) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $count = 1;
            while (Movie::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$count++;
            }
            $validated['slug'] = $slug;
        }

        Movie::create($validated);

        return back()->with('success', 'Movie created successfully!');
    }

    public function update(Request $request, Movie $movie): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:movies,slug,'.$movie->id],
            'tmdb_id' => ['nullable', 'string', 'max:50'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'overview' => ['nullable', 'string'],
            'poster_path' => ['nullable', 'string', 'max:500'],
            'backdrop_path' => ['nullable', 'string', 'max:500'],
            'release_date' => ['nullable', 'string', 'max:20'],
            'vote_average' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'runtime' => ['nullable', 'string', 'max:50'],
            'director' => ['nullable', 'string', 'max:100'],
            'trailer_url' => ['nullable', 'string', 'max:500'],
            'is_trending' => ['boolean'],
            'is_featured' => ['boolean'],
            'genres' => ['nullable', 'array'],
            'stream_servers' => ['nullable', 'array'],
        ]);

        $movie->update($validated);

        return back()->with('success', 'Movie updated successfully!');
    }

    public function destroy(Movie $movie): RedirectResponse
    {
        $movie->delete();

        return back()->with('success', 'Movie deleted successfully.');
    }

    public function toggleTrending(Movie $movie): RedirectResponse
    {
        $movie->update(['is_trending' => ! $movie->is_trending]);

        return back()->with('success', 'Trending status updated!');
    }

    public function toggleFeatured(Movie $movie): RedirectResponse
    {
        $movie->update(['is_featured' => ! $movie->is_featured]);

        return back()->with('success', 'Featured status updated!');
    }

    public function importFromTmdb(Request $request, TmdbService $tmdbService): RedirectResponse
    {
        $input = trim((string) $request->input('tmdb_id', ''));

        if (empty($input)) {
            return back()->with('error', 'Please provide a TMDB ID or TMDB URL.');
        }

        // Support TMDB URL format (e.g. https://www.themoviedb.org/movie/157336-interstellar)
        if (preg_match('/movie\/(\d+)/', $input, $matches)) {
            $input = $matches[1];
        }

        if (! $tmdbService->isConfigured()) {
            return back()->with('error', 'TMDB API key is not configured in .env file.');
        }

        $movie = $tmdbService->saveOrUpdateMovie($input);

        if (! $movie) {
            return back()->with('error', "Could not import movie with ID: {$input}. Please verify the TMDB ID.");
        }

        return back()->with('success', "Movie '{$movie->title}' imported successfully with trailer!");
    }
}
