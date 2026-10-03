<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Review;
use App\Models\TvShow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Review::with(['user', 'reviewable']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($type = $request->input('type')) {
            if ($type === 'movie') {
                $query->where('reviewable_type', Movie::class);
            } elseif ($type === 'tv') {
                $query->where('reviewable_type', TvShow::class);
            }
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->input('rating'));
        }

        if ($request->filled('has_spoiler')) {
            $hasSpoiler = filter_var($request->input('has_spoiler'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($hasSpoiler !== null) {
                $query->where('has_spoiler', $hasSpoiler);
            }
        }

        $reviews = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $stats = [
            'total_reviews' => Review::count(),
            'avg_rating' => round((float) (Review::avg('rating') ?: 0), 1),
            'spoiler_reviews' => Review::where('has_spoiler', true)->count(),
            'movie_reviews' => Review::where('reviewable_type', Movie::class)->count(),
            'tv_reviews' => Review::where('reviewable_type', TvShow::class)->count(),
        ];

        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $reviews,
            'filters' => $request->only(['search', 'type', 'rating', 'has_spoiler']),
            'stats' => $stats,
        ]);
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->likes()->delete();
        $review->delete();

        return back()->with('success', 'Review removed successfully.');
    }
}
