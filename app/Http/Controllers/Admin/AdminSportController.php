<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SportMatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminSportController extends Controller
{
    public function index(Request $request): Response
    {
        $query = SportMatch::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('league', 'like', "%{$search}%")
                    ->orWhere('team_home', 'like', "%{$search}%")
                    ->orWhere('team_away', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_live') && $request->input('is_live') !== '') {
            $query->where('is_live', filter_var($request->input('is_live'), FILTER_VALIDATE_BOOLEAN));
        }

        $matches = $query->orderByDesc('is_live')->orderBy('match_time')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Sports/Index', [
            'matches' => $matches,
            'filters' => $request->only(['search', 'is_live']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:sports_matches,slug'],
            'league' => ['nullable', 'string', 'max:100'],
            'team_home' => ['required', 'string', 'max:100'],
            'team_away' => ['required', 'string', 'max:100'],
            'home_logo' => ['nullable', 'string', 'max:500'],
            'away_logo' => ['nullable', 'string', 'max:500'],
            'match_time' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'is_live' => ['boolean'],
            'stream_url' => ['nullable', 'string', 'max:500'],
            'stream_servers' => ['nullable', 'array'],
        ]);

        if (empty($validated['slug'])) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $count = 1;
            while (SportMatch::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$count++;
            }
            $validated['slug'] = $slug;
        }

        SportMatch::create($validated);

        return back()->with('success', 'Match added successfully!');
    }

    public function update(Request $request, SportMatch $sport): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:sports_matches,slug,'.$sport->id],
            'league' => ['nullable', 'string', 'max:100'],
            'team_home' => ['required', 'string', 'max:100'],
            'team_away' => ['required', 'string', 'max:100'],
            'home_logo' => ['nullable', 'string', 'max:500'],
            'away_logo' => ['nullable', 'string', 'max:500'],
            'match_time' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'is_live' => ['boolean'],
            'stream_url' => ['nullable', 'string', 'max:500'],
            'stream_servers' => ['nullable', 'array'],
        ]);

        $sport->update($validated);

        return back()->with('success', 'Match updated successfully!');
    }

    public function destroy(SportMatch $sport): RedirectResponse
    {
        $sport->delete();

        return back()->with('success', 'Match deleted.');
    }

    public function toggleLive(SportMatch $sport): RedirectResponse
    {
        $sport->update(['is_live' => ! $sport->is_live]);

        return back()->with('success', 'Live stream status updated!');
    }
}
