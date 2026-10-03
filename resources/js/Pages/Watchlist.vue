<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      <div class="flex items-center justify-between border-b border-white/10 pb-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-white flex items-center gap-2.5">
            <i class="fas fa-bookmark text-netflix"></i>
            <span>{{ $t('watchlist.title') }}</span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">{{ $t('watchlist.subtitle') }}</p>
        </div>

        <div class="text-xs font-semibold text-gray-400">
          {{ $t('watchlist.saved_titles', { count: totalItems }) }}
        </div>
      </div>

      <!-- Watchlist Grid -->
      <div v-if="totalItems > 0" class="space-y-8">
        <!-- Saved Movies -->
        <div v-if="movies && movies.length" class="space-y-4">
          <h3 class="text-base font-bold text-white flex items-center gap-2">
            <i class="fas fa-film text-netflix text-xs"></i>
            <span>{{ $t('watchlist.saved_movies', { count: movies.length }) }}</span>
          </h3>
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <MediaCard 
              v-for="movie in movies" 
              :key="'m-' + movie.id"
              :item="movie"
              mediaType="movie"
            />
          </div>
        </div>

        <!-- Saved Shows -->
        <div v-if="shows && shows.length" class="space-y-4">
          <h3 class="text-base font-bold text-white flex items-center gap-2">
            <i class="fas fa-tv text-netflix text-xs"></i>
            <span>{{ $t('watchlist.saved_shows', { count: shows.length }) }}</span>
          </h3>
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <MediaCard 
              v-for="show in shows" 
              :key="'s-' + show.id"
              :item="show"
              mediaType="tv"
            />
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-24 bg-gray-900/40 rounded-2xl border border-white/5 space-y-4">
        <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mx-auto text-gray-500 text-2xl">
          <i class="far fa-bookmark"></i>
        </div>
        <div>
          <h3 class="text-lg font-bold text-white">{{ $t('watchlist.empty_title') }}</h3>
          <p class="text-xs text-gray-400 mt-1">{{ $t('watchlist.empty_desc') }}</p>
        </div>
        <Link 
          href="/movies"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-netflix text-white text-xs font-bold hover:bg-red-700 transition shadow"
        >
          <i class="fas fa-compass"></i>
          <span>{{ $t('watchlist.browse_titles') }}</span>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import MediaCard from '@/Components/MediaCard.vue';

const props = defineProps({
  movies: {
    type: Array,
    default: () => [],
  },
  shows: {
    type: Array,
    default: () => [],
  },
});

const totalItems = computed(() => {
  return props.movies.length + props.shows.length;
});
</script>
