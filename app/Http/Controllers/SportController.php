<?php

namespace App\Http\Controllers;

use App\Models\SportMatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SportController extends Controller
{
    public function index(Request $request): Response
    {
        $league = $request->get('league');
        $query = SportMatch::query();

        if ($league) {
            $query->where('league', $league);
        }

        $matches = $query->orderByDesc('is_live')->get();
        $selectedSlug = $request->get('watch');

        $activeMatch = null;
        if ($selectedSlug) {
            $activeMatch = SportMatch::where('slug', $selectedSlug)->first();
        }
        if (! $activeMatch && $matches->isNotEmpty()) {
            $activeMatch = $matches->firstWhere('is_live', true) ?? $matches->first();
        }

        $leagues = SportMatch::distinct()->pluck('league')->filter()->values();

        return Inertia::render('Sports/Index', [
            'matches' => $matches,
            'activeMatch' => $activeMatch,
            'leagues' => $leagues,
            'currentLeague' => $league,
        ]);
    }

    public function event(string $slug): Response
    {
        $activeMatch = SportMatch::where('slug', $slug)->firstOrFail();
        $matches = SportMatch::all();
        $leagues = SportMatch::distinct()->pluck('league')->filter()->values();

        return Inertia::render('Sports/Index', [
            'matches' => $matches,
            'activeMatch' => $activeMatch,
            'leagues' => $leagues,
            'currentLeague' => $activeMatch->league,
        ]);
    }
}
