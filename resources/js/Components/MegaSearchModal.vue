<template>
  <Teleport to="body">
    <div 
      v-if="isOpen" 
      class="fixed inset-0 z-50 flex items-start justify-center p-3 sm:p-6 overflow-y-auto"
      @keydown.esc="closeModal"
    >
      <!-- Dark Blur Backdrop -->
      <div 
        class="fixed inset-0 bg-black/85 backdrop-blur-2xl transition-opacity duration-300"
        @click="closeModal"
      ></div>

      <!-- Modal Card -->
      <div 
        class="relative w-full max-w-3xl bg-zinc-950/95 border border-white/15 rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl shadow-black z-10 my-4 sm:my-10 animate-scale-up ring-1 ring-white/10"
      >
        <!-- Top Search Bar Header -->
        <div class="relative flex items-center px-4 sm:px-6 py-4 border-b border-white/10 bg-zinc-900/60">
          <svg class="w-5 h-5 text-zinc-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>

          <input 
            ref="inputRef"
            type="text" 
            v-model="searchQuery" 
            @input="onSearchInput"
            @keydown.enter="goToFullSearch"
            :placeholder="$t('search.placeholder')" 
            autocomplete="off"
            class="w-full bg-transparent text-white placeholder-zinc-500 text-sm sm:text-base px-3.5 focus:outline-none font-medium"
          />

          <!-- Loading Spinner or Clear Button -->
          <div class="flex items-center gap-2 flex-shrink-0">
            <div v-if="loading" class="w-4 h-4 border-2 border-white/15 border-t-netflix rounded-full animate-spin"></div>

            <button 
              v-else-if="searchQuery"
              type="button" 
              @click="clearQuery"
              class="w-6 h-6 rounded-full bg-white/10 hover:bg-white/20 text-zinc-400 hover:text-white flex items-center justify-center text-xs transition cursor-pointer"
              :title="$t('search.clear')"
            >
              <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>

            <!-- Keyboard shortcut ESC badge -->
            <kbd class="hidden sm:inline-block px-2 py-0.5 rounded bg-white/5 border border-white/10 text-[10px] font-mono text-zinc-400">
              ESC
            </kbd>

            <!-- Close button -->
            <button 
              type="button" 
              @click="closeModal"
              class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/15 text-zinc-400 hover:text-white flex items-center justify-center transition cursor-pointer ml-1"
              :aria-label="$t('common.close')"
            >
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
        </div>

        <!-- Filter Pills Bar -->
        <div class="px-4 sm:px-6 py-2.5 bg-black/40 border-b border-white/5 flex flex-wrap items-center justify-between gap-2 text-xs">
          <!-- Main Category Tabs -->
          <div class="flex items-center gap-1 overflow-x-auto no-scrollbar py-0.5">
            <button
              v-for="cat in categories"
              :key="cat.id"
              type="button"
              @click="setCategory(cat.id)"
              :class="[
                'px-3 py-1 rounded-lg font-semibold whitespace-nowrap transition-all cursor-pointer',
                selectedCategory === cat.id
                  ? 'bg-netflix text-white shadow-sm'
                  : 'bg-white/5 hover:bg-white/10 text-zinc-400 hover:text-white'
              ]"
            >
              {{ cat.label }}
            </button>
          </div>

          <!-- Advanced Filter Toggle -->
          <button 
            type="button"
            @click="showAdvancedFilters = !showAdvancedFilters"
            :class="[
              'flex items-center gap-1.5 px-2.5 py-1 rounded-lg border transition cursor-pointer text-xs',
              hasActiveFilters || showAdvancedFilters
                ? 'bg-netflix/15 border-netflix text-white'
                : 'bg-white/5 border-white/10 text-zinc-400 hover:text-white'
            ]"
          >
            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            <span>{{ $t('search.filters') }}</span>
            <span v-if="hasActiveFilters" class="w-1.5 h-1.5 rounded-full bg-netflix"></span>
          </button>
        </div>

        <!-- Collapsible Advanced Filters Drawer -->
        <div 
          v-if="showAdvancedFilters" 
          class="px-4 sm:px-6 py-3 bg-zinc-900/50 border-b border-white/5 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs"
        >
          <!-- 1. Minimum IMDb Score -->
          <div>
            <label class="block text-zinc-400 text-[11px] font-semibold mb-1">{{ $t('search.imdb_rating') }}</label>
            <div class="flex items-center gap-1">
              <button 
                v-for="score in [0, 7.0, 8.0]" 
                :key="score"
                type="button"
                @click="setMinRating(score)"
                :class="[
                  'px-2.5 py-1 rounded text-xs transition cursor-pointer',
                  minRating === score ? 'bg-netflix text-white font-bold' : 'bg-white/5 text-zinc-400 hover:text-white'
                ]"
              >
                {{ score === 0 ? $t('search.all') : score + '+' }}
              </button>
            </div>
          </div>

          <!-- 2. Year Range -->
          <div>
            <label class="block text-zinc-400 text-[11px] font-semibold mb-1">{{ $t('search.release_year') }}</label>
            <select 
              v-model="yearRange" 
              @change="triggerSearch"
              class="w-full bg-zinc-900 text-zinc-200 border border-white/10 rounded-lg px-2.5 py-1 text-xs focus:outline-none focus:border-netflix"
            >
              <option value="all">{{ $t('search.all_years') }}</option>
              <option value="2024-2025">{{ $t('search.year_latest') }}</option>
              <option value="2020-2023">{{ $t('search.year_recent') }}</option>
              <option value="2010-2019">{{ $t('search.year_decade') }}</option>
              <option value="classic">{{ $t('search.year_classics') }}</option>
            </select>
          </div>

          <!-- 3. Genre Selector -->
          <div>
            <label class="block text-zinc-400 text-[11px] font-semibold mb-1">{{ $t('search.genre') }}</label>
            <select 
              v-model="selectedGenre" 
              @change="triggerSearch"
              class="w-full bg-zinc-900 text-zinc-200 border border-white/10 rounded-lg px-2.5 py-1 text-xs focus:outline-none focus:border-netflix"
            >
              <option value="">{{ $t('search.all_genres') }}</option>
              <option v-for="g in availableGenres" :key="g" :value="g">{{ g }}</option>
            </select>
          </div>
        </div>

        <!-- Modal Body Content -->
        <div class="max-h-[65vh] overflow-y-auto p-4 sm:p-6 space-y-6">
          <!-- 1. EMPTY QUERY STATE: Recent Searches, Popular Tags, Top Picks -->
          <div v-if="!searchQuery.trim()" class="space-y-6">
            <!-- Recent Searches -->
            <div v-if="recentSearches.length > 0">
              <div class="flex items-center justify-between text-xs text-zinc-400 mb-2">
                <span class="font-semibold uppercase tracking-wider text-[10px]">{{ $t('search.recent_searches') }}</span>
                <button 
                  type="button" 
                  @click="clearRecentSearches" 
                  class="text-zinc-500 hover:text-zinc-300 transition cursor-pointer text-[11px]"
                >
                  {{ $t('search.clear_all') }}
                </button>
              </div>
              <div class="flex flex-wrap gap-1.5">
                <button
                  v-for="term in recentSearches"
                  :key="term"
                  type="button"
                  @click="applyRecentSearch(term)"
                  class="px-3 py-1 rounded-full bg-white/5 hover:bg-white/10 border border-white/5 hover:border-white/15 text-zinc-300 hover:text-white text-xs flex items-center gap-1.5 transition cursor-pointer group"
                >
                  <svg class="w-3 h-3 text-zinc-500 group-hover:text-zinc-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                  </svg>
                  <span>{{ term }}</span>
                </button>
              </div>
            </div>

            <!-- Trending Searches -->
            <div>
              <div class="text-xs text-zinc-400 mb-2">
                <span class="font-semibold uppercase tracking-wider text-[10px]">{{ $t('search.trending_searches') }}</span>
              </div>
              <div class="flex flex-wrap gap-1.5">
                <button
                  v-for="tag in trendingKeywords"
                  :key="tag"
                  type="button"
                  @click="applySearchTerm(tag)"
                  class="px-3 py-1 rounded-full bg-white/5 hover:bg-netflix/20 border border-white/5 hover:border-netflix/40 text-zinc-300 hover:text-white text-xs transition cursor-pointer"
                >
                  {{ tag }}
                </button>
              </div>
            </div>

            <!-- Top Curated Picks Showcase -->
            <div v-if="topPicks.length > 0">
              <div class="flex items-center justify-between text-xs text-zinc-400 mb-3">
                <span class="font-semibold uppercase tracking-wider text-[10px]">{{ $t('search.featured_today') }}</span>
              </div>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <Link
                  v-for="item in topPicks"
                  :key="item.type + '-' + item.id"
                  :href="item.url"
                  @click="closeModal"
                  class="group relative rounded-xl overflow-hidden bg-zinc-900 border border-white/5 hover:border-netflix transition-all duration-300 flex flex-col"
                >
                  <div class="aspect-[16/10] w-full relative overflow-hidden bg-black">
                    <img 
                      :src="item.backdrop || item.poster || '/images/placeholder-poster.jpg'" 
                      :alt="item.title"
                      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent"></div>
                    <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-black/70 text-white border border-white/10">
                      {{ item.type === 'movie' ? $t('home.movie') : (item.type === 'anime' ? $t('home.anime') : $t('home.tv_show')) }}
                    </span>
                    <span v-if="item.rating" class="absolute bottom-1.5 left-1.5 text-amber-400 font-bold text-[10px] flex items-center gap-1">
                      <span>★</span>
                      <span>{{ item.rating }}</span>
                    </span>
                  </div>
                  <div class="p-2">
                    <h5 class="text-xs font-bold text-white truncate group-hover:text-netflix transition">
                      {{ item.title }}
                    </h5>
                    <p class="text-[10px] text-zinc-400 mt-0.5">{{ item.year }}</p>
                  </div>
                </Link>
              </div>
            </div>
          </div>

          <!-- 2. ACTIVE SEARCH RESULTS -->
          <div v-else class="space-y-6">
            <!-- Results Count & Summary -->
            <div class="flex items-center justify-between text-xs text-zinc-400 border-b border-white/5 pb-2">
              <span>{{ $t('search.results_count', { count: totalResults }) }}</span>
              <button 
                type="button" 
                @click="goToFullSearch"
                class="text-netflix hover:underline font-semibold flex items-center gap-1 cursor-pointer"
              >
                <span>{{ $t('search.view_all_results') }}</span>
                <span>→</span>
              </button>
            </div>

            <!-- A. Live Sports Results (if matching) -->
            <div v-if="sportsResults.length > 0" class="space-y-2">
              <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                <span>{{ $t('home.live_sports') }}</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <Link
                  v-for="sport in sportsResults"
                  :key="'sport-' + sport.id"
                  :href="sport.url"
                  @click="closeModal"
                  class="p-3 rounded-xl bg-zinc-900/80 hover:bg-zinc-900 border border-white/5 hover:border-red-500/40 transition flex items-center justify-between gap-3 group"
                >
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 text-[10px] mb-1">
                      <span class="px-1.5 py-0.2 rounded bg-white/10 text-zinc-300 font-bold">{{ sport.league }}</span>
                      <span v-if="sport.is_live" class="text-red-400 font-bold animate-pulse">{{ $t('search.live') }}</span>
                      <span v-else class="text-zinc-500">{{ sport.match_time }}</span>
                    </div>
                    <div class="text-xs font-bold text-white group-hover:text-red-400 transition truncate">
                      {{ sport.team_home }} vs {{ sport.team_away }}
                    </div>
                  </div>
                  <span class="px-2.5 py-1 rounded-lg bg-netflix text-white text-[10px] font-bold">
                    {{ $t('home.watch_now') }}
                  </span>
                </Link>
              </div>
            </div>

            <!-- B. Movies & TV Shows Results List -->
            <div v-if="combinedMediaResults.length > 0" class="space-y-2">
              <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">
                {{ $t('search.media_results', { count: combinedMediaResults.length }) }}
              </div>

              <div class="divide-y divide-white/5">
                <Link
                  v-for="item in combinedMediaResults"
                  :key="item.type + '-' + item.id"
                  :href="item.url"
                  @click="onSelectResult(item)"
                  class="flex items-center gap-3.5 p-2.5 rounded-xl hover:bg-white/[0.05] transition-all duration-200 group"
                >
                  <!-- Poster Thumbnail -->
                  <div class="w-12 sm:w-14 aspect-[2/3] rounded-lg overflow-hidden bg-zinc-900 border border-white/10 flex-shrink-0">
                    <img 
                      :src="item.poster || '/images/placeholder-poster.jpg'" 
                      :alt="item.title"
                      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" 
                      loading="lazy"
                    />
                  </div>

                  <!-- Item Info -->
                  <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                      <span 
                        class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider"
                        :class="item.type === 'movie' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : (item.type === 'anime' ? 'bg-pink-500/20 text-pink-400 border border-pink-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30')"
                      >
                        {{ item.type === 'movie' ? $t('home.movie') : (item.type === 'anime' ? $t('home.anime') : $t('home.tv_show')) }}
                      </span>
                      <span class="text-xs text-zinc-400">{{ item.year }}</span>
                      <span v-if="item.rating" class="text-amber-400 font-bold text-xs flex items-center gap-1">
                        <span>★</span>
                        <span>{{ typeof item.rating === 'number' ? item.rating.toFixed(1) : item.rating }}</span>
                      </span>
                    </div>

                    <h4 class="text-sm font-bold text-white group-hover:text-netflix transition truncate">
                      {{ item.title }}
                    </h4>

                    <!-- Genres Chips -->
                    <div v-if="item.genres && item.genres.length" class="flex items-center gap-1 mt-1 overflow-hidden">
                      <span 
                        v-for="g in item.genres.slice(0, 3)" 
                        :key="g"
                        class="text-[10px] text-zinc-500 truncate"
                      >
                        {{ g }}<span class="ml-1 text-zinc-700">•</span>
                      </span>
                    </div>
                  </div>

                  <!-- Action Arrow -->
                  <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity pr-2">
                    <div class="w-8 h-8 rounded-full bg-netflix text-white flex items-center justify-center shadow-lg shadow-netflix/40">
                      <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z"/>
                      </svg>
                    </div>
                  </div>
                </Link>
              </div>
            </div>

            <!-- C. No Results Found State -->
            <div v-else-if="!loading" class="text-center py-16 space-y-3">
              <div class="w-12 h-12 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mx-auto text-zinc-500">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="11" cy="11" r="8"></circle>
                  <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
              </div>
              <h4 class="text-sm font-bold text-white">{{ $t('search.no_results') }}</h4>
              <p class="text-xs text-zinc-400 max-w-xs mx-auto">
                {{ $t('search.no_results_desc', { query: searchQuery }) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Footer Navigation Tips -->
        <div class="px-4 sm:px-6 py-2.5 bg-black/60 border-t border-white/5 flex items-center justify-between text-[11px] text-zinc-500">
          <div class="flex items-center gap-3">
            <span>{{ $t('search.mega_search_title') }}</span>
            <span class="hidden sm:inline">•</span>
            <span class="hidden sm:inline">{{ $t('search.mega_search_subtitle') }}</span>
          </div>
          <button 
            type="button" 
            @click="goToFullSearch"
            class="text-zinc-400 hover:text-white transition cursor-pointer font-medium"
          >
            {{ $t('search.search_page') }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'open']);

const inputRef = ref(null);
const searchQuery = ref('');
const loading = ref(false);
const showAdvancedFilters = ref(false);

const selectedCategory = ref('all'); // 'all', 'movie', 'tv', 'anime', 'sports'
const minRating = ref(0);
const yearRange = ref('all');
const selectedGenre = ref('');

const moviesResults = ref([]);
const showsResults = ref([]);
const sportsResults = ref([]);
const topPicks = ref([]);
const trendingKeywords = ref([]);
const availableGenres = computed(() => [
  t('common.genres.action'),
  t('common.genres.scifi'),
  t('common.genres.comedy'),
  t('common.genres.horror'),
  t('common.genres.drama'),
  t('common.genres.animation'),
  t('common.genres.adventure'),
  t('common.genres.thriller'),
]);

const recentSearches = ref([]);

const categories = computed(() => [
  { id: 'all', label: t('search.all') },
  { id: 'movie', label: t('search.movies_tab') },
  { id: 'tv', label: t('search.tv_tab') },
  { id: 'anime', label: t('search.anime_tab') },
  { id: 'sports', label: t('search.sports_tab') },
]);

const hasActiveFilters = computed(() => {
  return minRating.value > 0 || yearRange.value !== 'all' || selectedGenre.value !== '';
});

const combinedMediaResults = computed(() => {
  return [...moviesResults.value, ...showsResults.value];
});

const totalResults = computed(() => {
  return moviesResults.value.length + showsResults.value.length + sportsResults.value.length;
});

const closeModal = () => {
  emit('close');
};

const clearQuery = () => {
  searchQuery.value = '';
  moviesResults.value = [];
  showsResults.value = [];
  sportsResults.value = [];
  fetchInitialData();
  nextTick(() => inputRef.value?.focus());
};

const setCategory = (catId) => {
  selectedCategory.value = catId;
  triggerSearch();
};

const setMinRating = (score) => {
  minRating.value = score;
  triggerSearch();
};

let debounceTimer = null;
const onSearchInput = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    triggerSearch();
  }, 250);
};

const triggerSearch = async () => {
  const q = searchQuery.value.trim();
  if (!q) {
    fetchInitialData();
    return;
  }

  loading.value = true;
  try {
    const params = new URLSearchParams({
      q: q,
      type: selectedCategory.value,
      min_rating: minRating.value.toString(),
      year_range: yearRange.value,
      genre: selectedGenre.value,
    });

    const res = await fetch(`/api/search/mega?${params.toString()}`);
    const data = await res.json();

    if (data.status === 'success') {
      moviesResults.value = data.movies || [];
      showsResults.value = data.shows || [];
      sportsResults.value = data.sports || [];
    }
  } catch (err) {
    console.error('Mega search error:', err);
  } finally {
    loading.value = false;
  }
};

const fetchInitialData = async () => {
  try {
    const res = await fetch('/api/search/mega');
    const data = await res.json();
    if (data.status === 'success') {
      topPicks.value = data.top_picks || [];
      trendingKeywords.value = data.trending_keywords || [];
      if (data.genres && data.genres.length) {
        availableGenres.value = data.genres;
      }
    }
  } catch (err) {
    console.error('Initial mega search fetch error:', err);
  }
};

const loadRecentSearches = () => {
  try {
    const stored = localStorage.getItem('movie_recent_searches');
    if (stored) {
      recentSearches.value = JSON.parse(stored).slice(0, 8);
    }
  } catch (e) {}
};

const saveRecentSearch = (term) => {
  if (!term || !term.trim()) return;
  const clean = term.trim();
  const existing = recentSearches.value.filter((t) => t.toLowerCase() !== clean.toLowerCase());
  const updated = [clean, ...existing].slice(0, 8);
  recentSearches.value = updated;
  try {
    localStorage.setItem('movie_recent_searches', JSON.stringify(updated));
  } catch (e) {}
};

const clearRecentSearches = () => {
  recentSearches.value = [];
  try {
    localStorage.removeItem('movie_recent_searches');
  } catch (e) {}
};

const applyRecentSearch = (term) => {
  searchQuery.value = term;
  triggerSearch();
};

const applySearchTerm = (term) => {
  searchQuery.value = term;
  triggerSearch();
};

const onSelectResult = (item) => {
  saveRecentSearch(item.title);
  closeModal();
};

const goToFullSearch = () => {
  if (searchQuery.value.trim()) {
    saveRecentSearch(searchQuery.value.trim());
    closeModal();
    router.visit(`/search?q=${encodeURIComponent(searchQuery.value.trim())}`);
  }
};

// Global keydown listener for Ctrl+K / Cmd+K
const onGlobalKeydown = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault();
    if (props.isOpen) {
      closeModal();
    } else {
      emit('open');
    }
  }
};

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    loadRecentSearches();
    fetchInitialData();
    nextTick(() => {
      inputRef.value?.focus();
    });
  }
});

onMounted(() => {
  window.addEventListener('keydown', onGlobalKeydown);
  loadRecentSearches();
});

onUnmounted(() => {
  window.removeEventListener('keydown', onGlobalKeydown);
});
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
