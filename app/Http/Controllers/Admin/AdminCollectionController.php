<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CuratedCollection;
use App\Models\CuratedCollectionItem;
use App\Models\Movie;
use App\Models\TvShow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminCollectionController extends Controller
{
    public function index(Request $request): Response
    {
        $collections = CuratedCollection::with(['items.collectible'])
            ->orderBy('order')
            ->orderByDesc('id')
            ->get();

        // Also fetch candidate movies and tv shows for the item selector
        $recentMovies = Movie::select('id', 'title', 'poster_url', 'release_year')->latest()->limit(50)->get();
        $recentTvShows = TvShow::select('id', 'title', 'poster_url', 'first_air_date')->latest()->limit(50)->get();

        return Inertia::render('Admin/Collections/Index', [
            'collections' => $collections,
            'recentMovies' => $recentMovies,
            'recentTvShows' => $recentTvShows,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'backdrop_path' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['boolean'],
            'order' => ['nullable', 'integer'],
        ]);

        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $counter = 1;
        while (CuratedCollection::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        $collection = CuratedCollection::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'backdrop_path' => $validated['backdrop_path'] ?? null,
            'is_featured' => $validated['is_featured'] ?? false,
            'order' => $validated['order'] ?? 0,
        ]);

        AuditLog::log('create_collection', "Created collection: {$collection->title}", $collection);

        return back()->with('success', 'Koleksiyon başarıyla oluşturuldu.');
    }

    public function update(Request $request, CuratedCollection $collection): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'backdrop_path' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['boolean'],
            'order' => ['nullable', 'integer'],
        ]);

        $collection->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'backdrop_path' => $validated['backdrop_path'] ?? null,
            'is_featured' => $validated['is_featured'] ?? false,
            'order' => $validated['order'] ?? $collection->order,
        ]);

        AuditLog::log('update_collection', "Updated collection: {$collection->title}", $collection);

        return back()->with('success', 'Koleksiyon güncellendi.');
    }

    public function destroy(CuratedCollection $collection): RedirectResponse
    {
        $title = $collection->title;
        $collection->items()->delete();
        $collection->delete();

        AuditLog::log('delete_collection', "Deleted collection: {$title}");

        return back()->with('success', 'Koleksiyon silindi.');
    }

    public function addItem(Request $request, CuratedCollection $collection): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:movie,tv'],
            'id' => ['required', 'integer'],
        ]);

        $modelClass = $validated['type'] === 'movie' ? Movie::class : TvShow::class;

        // Check if item exists
        $model = $modelClass::find($validated['id']);
        if (! $model) {
            return back()->with('error', 'Seçilen içerik bulunamadı.');
        }

        // Avoid duplicate in same collection
        $exists = $collection->items()
            ->where('collectible_type', $modelClass)
            ->where('collectible_id', $model->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Bu içerik zaten koleksiyonda yer alıyor.');
        }

        $nextOrder = ($collection->items()->max('order') ?? 0) + 1;

        $collection->items()->create([
            'collectible_type' => $modelClass,
            'collectible_id' => $model->id,
            'order' => $nextOrder,
        ]);

        AuditLog::log('add_collection_item', "Added {$model->title} to collection: {$collection->title}", $collection);

        return back()->with('success', "{$model->title} koleksiyona eklendi.");
    }

    public function removeItem(CuratedCollection $collection, CuratedCollectionItem $item): RedirectResponse
    {
        if ($item->curated_collection_id !== $collection->id) {
            return back()->with('error', 'Geçersiz koleksiyon öğesi.');
        }

        $item->delete();

        AuditLog::log('remove_collection_item', "Removed item from collection: {$collection->title}", $collection);

        return back()->with('success', 'İçerik koleksiyondan kaldırıldı.');
    }
}
