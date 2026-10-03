<?php

namespace App\Http\Controllers;

use App\Models\CustomList;
use App\Models\CustomListItem;
use App\Models\Movie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CustomListController extends Controller
{
    /**
     * Display a listing of public lists and user lists.
     */
    public function index(Request $request): Response
    {
        $userId = Auth::id();

        $publicLists = CustomList::with('user:id,name,avatar')
            ->where('is_public', true)
            ->with(['items' => function ($q) {
                $q->take(4)->with(['movie:id,poster_path,poster_url', 'tvShow:id,poster_path,poster_url']);
            }])
            ->orderByDesc('items_count')
            ->orderByDesc('created_at')
            ->paginate(12);

        $myLists = [];
        if ($userId) {
            $myLists = CustomList::where('user_id', $userId)
                ->with(['items' => function ($q) {
                    $q->take(4)->with(['movie:id,poster_path,poster_url', 'tvShow:id,poster_path,poster_url']);
                }])
                ->orderByDesc('updated_at')
                ->get();
        }

        return Inertia::render('Lists/Index', [
            'publicLists' => $publicLists,
            'myLists' => $myLists,
        ]);
    }

    /**
     * Display the specified custom list.
     */
    public function show(string $slug, Request $request): Response
    {
        $customList = CustomList::where('slug', $slug)
            ->orWhere('id', is_numeric($slug) ? (int) $slug : 0)
            ->with(['user:id,name,avatar', 'items.movie', 'items.tvShow'])
            ->firstOrFail();

        $userId = Auth::id();
        $isOwner = $userId && $customList->user_id === $userId;

        if (! $customList->is_public && ! $isOwner) {
            abort(403, 'Bu liste gizlidir.');
        }

        return Inertia::render('Lists/Show', [
            'customList' => $customList,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Store a newly created custom list.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $userId = Auth::id();
        if (! $userId) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Giriş yapmanız gerekiyor.'], 401);
            }

            return redirect()->route('login');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $baseSlug = Str::slug($validated['title']) ?: 'liste';
        $slug = $baseSlug.'-'.strtolower(Str::random(6));

        $list = CustomList::create([
            'user_id' => $userId,
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_public' => $request->boolean('is_public', true),
            'items_count' => 0,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'list' => $list,
                'message' => 'Liste başarıyla oluşturuldu!',
            ]);
        }

        return redirect()->route('lists.show', $list->slug)->with('success', 'Liste oluşturuldu!');
    }

    /**
     * Update the specified list.
     */
    public function update(CustomList $customList, Request $request): JsonResponse|RedirectResponse
    {
        $userId = Auth::id();
        if (! $userId || $customList->user_id !== $userId) {
            abort(403, 'Bu listeyi düzenleme yetkiniz yok.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $customList->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_public' => $request->boolean('is_public', true),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'list' => $customList]);
        }

        return back()->with('success', 'Liste güncellendi!');
    }

    /**
     * Delete the specified list.
     */
    public function destroy(CustomList $customList, Request $request): JsonResponse|RedirectResponse
    {
        $userId = Auth::id();
        if (! $userId || $customList->user_id !== $userId) {
            abort(403, 'Bu listeyi silme yetkiniz yok.');
        }

        $customList->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('lists.index')->with('success', 'Liste silindi.');
    }

    /**
     * Add a movie or TV show to a custom list.
     */
    public function addItem(CustomList $customList, Request $request): JsonResponse
    {
        $userId = Auth::id();
        if (! $userId || $customList->user_id !== $userId) {
            return response()->json(['error' => 'Bu işlem için yetkiniz yok.'], 403);
        }

        $validated = $request->validate([
            'media_type' => ['required', 'string', 'in:movie,tv'],
            'media_id' => ['required', 'integer'],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        $item = CustomListItem::firstOrCreate(
            [
                'custom_list_id' => $customList->id,
                'media_type' => $validated['media_type'],
                'media_id' => $validated['media_id'],
            ],
            [
                'notes' => $validated['notes'] ?? null,
                'order' => $customList->items()->count() + 1,
            ]
        );

        $customList->refreshItemsCount();

        return response()->json([
            'success' => true,
            'status' => 'added',
            'items_count' => $customList->items_count,
            'item' => $item,
        ]);
    }

    /**
     * Remove an item from a custom list.
     */
    public function removeItem(CustomList $customList, Request $request): JsonResponse|RedirectResponse
    {
        $userId = Auth::id();
        if (! $userId || $customList->user_id !== $userId) {
            abort(403, 'Bu işlem için yetkiniz yok.');
        }

        if ($request->filled('item_id')) {
            $customList->items()->where('id', $request->item_id)->delete();
        } else {
            $request->validate([
                'media_type' => ['required', 'string', 'in:movie,tv'],
                'media_id' => ['required', 'integer'],
            ]);

            $customList->items()
                ->where('media_type', $request->media_type)
                ->where('media_id', $request->media_id)
                ->delete();
        }

        $customList->refreshItemsCount();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => 'removed',
                'items_count' => $customList->items_count,
            ]);
        }

        return back()->with('success', 'İçerik listeden çıkarıldı.');
    }

    /**
     * Get user's lists and whether the specified media item is in each list.
     */
    public function itemStatus(Request $request): JsonResponse
    {
        $userId = Auth::id();
        if (! $userId) {
            return response()->json(['authenticated' => false, 'lists' => []]);
        }

        $mediaType = $request->query('media_type');
        $mediaId = (int) $request->query('media_id');

        $lists = CustomList::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($list) use ($mediaType, $mediaId) {
                $contains = false;
                if ($mediaType && $mediaId) {
                    $contains = CustomListItem::where('custom_list_id', $list->id)
                        ->where('media_type', $mediaType)
                        ->where('media_id', $mediaId)
                        ->exists();
                }

                return [
                    'id' => $list->id,
                    'title' => $list->title,
                    'slug' => $list->slug,
                    'items_count' => $list->items_count,
                    'is_public' => $list->is_public,
                    'contains' => $contains,
                ];
            });

        return response()->json([
            'authenticated' => true,
            'lists' => $lists,
        ]);
    }
}
