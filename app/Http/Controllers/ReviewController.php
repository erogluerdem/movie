<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\TvShow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            return back()->with('error', 'You must be logged in to leave a review.');
        }

        $validated = $request->validate([
            'media_type' => ['required', 'in:movie,tv'],
            'media_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:10'],
            'content' => ['required', 'string', 'min:3', 'max:2000'],
            'has_spoiler' => ['boolean'],
        ]);

        $modelClass = $validated['media_type'] === 'movie' ? Movie::class : TvShow::class;
        $media = $modelClass::findOrFail($validated['media_id']);

        $isNew = ! Review::where([
            'user_id' => $user->id,
            'reviewable_type' => $modelClass,
            'reviewable_id' => $media->id,
        ])->exists();

        $review = Review::updateOrCreate(
            [
                'user_id' => $user->id,
                'reviewable_type' => $modelClass,
                'reviewable_id' => $media->id,
            ],
            [
                'rating' => $validated['rating'],
                'content' => $validated['content'],
                'has_spoiler' => $validated['has_spoiler'] ?? false,
            ]
        );

        // Gamification: Award 10 points for a new review
        if ($isNew) {
            $user->increment('points', 10);

            // Check badges
            $badges = is_string($user->badges) ? json_decode($user->badges, true) : ($user->badges ?? []);
            if (! is_array($badges)) {
                $badges = [];
            }

            $newBadges = [];
            if ($user->points >= 50 && ! in_array('Top Critic', $badges)) {
                $newBadges[] = 'Top Critic';
            }
            if ($user->points >= 150 && ! in_array('Movie Buff', $badges)) {
                $newBadges[] = 'Movie Buff';
            }
            if ($user->points >= 500 && ! in_array('Legend', $badges)) {
                $newBadges[] = 'Legend';
            }

            if (count($newBadges) > 0) {
                $badges = array_merge($badges, $newBadges);
                $user->badges = $badges;
                $user->save();
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'review' => $review->load('user'),
            ]);
        }

        return back()->with('success', 'Your review and rating have been posted!');
    }

    public function toggleLike(Review $review, Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $existing = ReviewLike::where('user_id', $user->id)
            ->where('review_id', $review->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $review->decrement('likes_count');

            return response()->json(['liked' => false, 'likes_count' => $review->fresh()->likes_count]);
        }

        ReviewLike::create([
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $review->increment('likes_count');

        return response()->json(['liked' => true, 'likes_count' => $review->fresh()->likes_count]);
    }

    public function destroy(Review $review, Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user || ($user->id !== $review->user_id && $user->role !== 'admin')) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }

            return back()->with('error', 'Unauthorized action.');
        }

        $review->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'deleted']);
        }

        return back()->with('success', 'Review removed.');
    }
}
