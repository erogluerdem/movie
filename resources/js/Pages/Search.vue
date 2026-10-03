<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- Search Box & Filter Controls -->
      <div class="bg-zinc-950/80 rounded-3xl p-6 sm:p-8 border border-white/10 shadow-2xl backdrop-blur-xl space-y-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
          <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-2.5">
              <span>{{ $t('search.mega_search') }}</span>
              <span class="text-xs px-2.5 py-0.5 rounded-full bg-netflix/20 border border-netflix/40 text-netflix font-bold">{{ $t('search.comprehensive_discovery') }}</span>
            </h1>
            <p class="text-xs text-zinc-400 mt-1">{{ $t('search.search_lead') }}</p>
          </div>
          
          <button
            type="button"
            @click="openMegaModal"
            class="hidden sm:flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 text-zinc-300 hover:text-white border border-white/10 text-xs font-semibold transition cursor-pointer"
          >
            <kbd class="text-[10px] font-mono text-zinc-400">Ctrl + K</kbd>
            <span>{{ $t('search.quick_modal') }}</span>
          </button>
        </div>

        <!-- Omnibox Search Input -->
        <div class="relative w-full">
          <svg class="w-5 h-5 text-zinc-400 absolute left-4 top-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input 
            type="text" 
            v-model="searchInput" 
            @keydown.enter="performSearch"
            :placeholder="$t('search.omnibox_placeholder')"
            class="w-full bg-zinc-900/90 text-white placeholder-zinc-500 text-sm sm:text-base rounded-2xl pl-12 pr-28 py-3.5 border border-white/10 focus:outline-none focus:border-netflix shadow-inner"
          />
          <button 
            @click="performSearch"
            class="absolute right-2.5 top-2 bg-netflix hover:bg-netflix-hover text-white px-5 py-2 rounded-xl font-bold text-xs transition shadow-md shadow-netflix/30 cursor-pointer"
          >
            {{ $t('search.search_action') }}
          </button>
        </div>

        <!-- Filter Pills Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-white/5">
          <!-- Type Filter Tabs -->
          <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
            <button 
              v-for="t in typeTabs" 
              :key="t.id"
              @click="changeType(t.id)"
              :class="[
                'px-3.5 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer',
                currentType === t.id ? 'bg-netflix text-white shadow-sm' : 'text-zinc-400 hover:text-white bg-white/5'
              ]"
            >
              {{ t.label }}
            </button>
          </div>

          <!-- Rating Filter Pills -->
          <div class="flex items-center gap-1.5 text-xs">
            <span class="text-zinc-500 text-[11px] mr-1 hidden sm:inline">{{ $t('search.min_rating_label') }}</span>
            <button 
              v-for="r in [0, 7.0, 8.0]" 
              :key="r"
              type="button"
              @click="filterRating = r"
              :class="[
                'px-2.5 py-1 rounded-lg text-xs font-semibold transition cursor-pointer',
                filterRating === r ? 'bg-white/20 text-white' : 'bg-white/5 text-zinc-400 hover:text-white'
              ]"
            >
              {{ r === 0 ? $t('search.all') : r + '+ ★' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Results Display Section -->
      <div v-if="query">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-sm font-semibold text-zinc-400">
            {{ $t('search.results_found', { query, count: filteredResults.length }) }}
          </h2>
        </div>

        <!-- Grid of Results -->
        <div v-if="filteredResults.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <MediaCard 
            v-for="item in filteredResults" 
            :key="(item.first_air_date ? 'tv-' : 'movie-') + item.id"
            :item="item"
            :mediaType="item.first_air_date ? 'tv' : 'movie'"
          />
        </div>

        <!-- No Results -->
        <div v-else class="text-center py-20 bg-zinc-950/50 rounded-3xl border border-white/5 space-y-4">
          <div class="w-14 h-14 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mx-auto text-zinc-500">
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
          </div>
          <h3 class="text-base font-bold text-white">{{ $t('search.no_results_for', { query }) }}</h3>
          <p class="text-xs text-zinc-400 max-w-sm mx-auto">
            {{ $t('search.no_results_help') }}
          </p>
          <button 
            type="button" 
            @click="searchInput = ''; performSearch()" 
            class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-semibold transition cursor-pointer"
          >
            {{ $t('search.reset_search') }}
          </button>
        </div>
      </div>

      <!-- Initial State (No search query yet) -->
      <div v-else class="space-y-8">
        <!-- Popular Quick Keyword Badges -->
        <div class="bg-zinc-950/50 rounded-3xl p-6 sm:p-8 border border-white/5 space-y-4">
          <h3 class="text-sm font-bold text-zinc-300 uppercase tracking-wider">{{ $t('search.popular_queries') }}</h3>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="tag in popularKeywords"
              :key="tag"
              type="button"
              @click="applyKeyword(tag)"
              class="px-4 py-2 rounded-xl bg-white/5 hover:bg-netflix/20 border border-white/5 hover:border-netflix/40 text-zinc-300 hover:text-white text-xs font-medium transition cursor-pointer"
            >
              {{ tag }}
            </button>
          </div>
        </div>

        <!-- Quick Genre Discovery -->
        <div class="bg-zinc-950/50 rounded-3xl p-6 sm:p-8 border border-white/5 space-y-4">
          <h3 class="text-sm font-bold text-zinc-300 uppercase tracking-wider">{{ $t('search.explore_genres') }}</h3>
          <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5">
            <button
              v-for="genre in quickGenres"
              :key="genre.id"
              type="button"
              @click="applyKeyword(genre.label)"
              class="p-3 rounded-xl bg-white/[0.03] hover:bg-white/10 border border-white/5 hover:border-white/15 text-center text-xs font-semibold text-zinc-300 hover:text-white transition cursor-pointer"
            >
              {{ genre.label }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import MediaCard from '@/Components/MediaCard.vue';
import { useI18n } from '@/Composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  query: String,
  type: String,
  movies: {
    type: Array,
    default: () => [],
  },
  shows: {
    type: Array,
    default: () => [],
  },
});

const searchInput = ref(props.query || '');
const currentType = ref(props.type || 'all');
const filterRating = ref(0);

const popularKeywords = ['Gladiator', 'Inception', 'Arcane', 'Attack on Titan', 'Real Madrid', 'Interstellar', 'Dune', 'Breaking Bad'];

const quickGenres = computed(() => [
  { id: 'action', label: t('common.genres.action') },
  { id: 'scifi', label: t('common.genres.scifi') },
  { id: 'comedy', label: t('common.genres.comedy') },
  { id: 'horror', label: t('common.genres.horror') },
  { id: 'drama', label: t('common.genres.drama') },
  { id: 'animation', label: t('common.genres.animation') },
  { id: 'adventure', label: t('common.genres.adventure') },
  { id: 'thriller', label: t('common.genres.thriller') },
]);

const typeTabs = computed(() => [
  { id: 'all', label: `${t('search.all')} (${props.movies.length + props.shows.length})` },
  { id: 'movie', label: `${t('search.movies_tab')} (${props.movies.length})` },
  { id: 'tv', label: `${t('search.tv_tab')} (${props.shows.length})` },
]);

const performSearch = () => {
  if (searchInput.value.trim()) {
    router.get('/search', {
      q: searchInput.value.trim(),
      type: currentType.value,
    }, { preserveState: true });
  }
};

const changeType = (newType) => {
  currentType.value = newType;
  if (searchInput.value.trim()) {
    performSearch();
  }
};

const applyKeyword = (kw) => {
  searchInput.value = kw;
  performSearch();
};

const filteredResults = computed(() => {
  let list = [];
  if (currentType.value === 'movie') list = props.movies;
  else if (currentType.value === 'tv') list = props.shows;
  else list = [...props.movies, ...props.shows];

  if (filterRating.value > 0) {
    list = list.filter((item) => (item.vote_average || 0) >= filterRating.value);
  }

  return list;
});

const openMegaModal = () => {
  window.dispatchEvent(new KeyboardEvent('keydown', { key: 'k', ctrlKey: true }));
};
</script>
