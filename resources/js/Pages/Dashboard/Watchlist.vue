<template>
  <DashboardLayout>
    <div class="space-y-6">
      <!-- Filter & Search Controls Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl bg-white/5 border border-white/10">
        <!-- Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
          <button 
            @click="activeFilter = 'all'"
            :class="[
              activeFilter === 'all' 
                ? 'bg-netflix text-white shadow-md' 
                : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10'
            ]"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0"
          >
            <span>{{ $t('watchlist.all_titles') }}</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/40">{{ totalCount }}</span>
          </button>

          <button 
            @click="activeFilter = 'movie'"
            :class="[
              activeFilter === 'movie' 
                ? 'bg-netflix text-white shadow-md' 
                : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10'
            ]"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0"
          >
            <i class="fas fa-film text-[10px]"></i>
            <span>{{ $t('watchlist.movies') }}</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/40">{{ moviesList.length }}</span>
          </button>

          <button 
            @click="activeFilter = 'tv'"
            :class="[
              activeFilter === 'tv' 
                ? 'bg-netflix text-white shadow-md' 
                : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10'
            ]"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0"
          >
            <i class="fas fa-tv text-[10px]"></i>
            <span>{{ $t('watchlist.tv_shows') }}</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/40">{{ showsList.length }}</span>
          </button>
        </div>

        <!-- Search & Sort Controls -->
        <div class="flex items-center gap-3">
          <!-- Search in Watchlist -->
          <div class="relative flex-1 sm:w-56">
            <input 
              type="text" 
              v-model="searchQuery" 
              :placeholder="$t('watchlist.search_placeholder')" 
              class="w-full h-9 bg-black/50 border border-white/10 rounded-xl pl-8 pr-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
            />
            <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-[11px] text-gray-500"></i>
            <button 
              v-if="searchQuery" 
              @click="searchQuery = ''" 
              class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 hover:text-white"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>

          <!-- Sort Dropdown -->
          <select 
            v-model="sortBy" 
            class="h-9 bg-black/50 border border-white/10 rounded-xl px-3 text-xs text-gray-300 focus:outline-none focus:border-netflix cursor-pointer"
          >
            <option value="recent">{{ $t('watchlist.sort_recent') }}</option>
            <option value="rating">{{ $t('watchlist.sort_rating') }}</option>
            <option value="title">{{ $t('watchlist.sort_title') }}</option>
          </select>
        </div>
      </div>

      <!-- Watchlist Grid -->
      <div v-if="filteredItems.length > 0" class="space-y-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <div 
            v-for="item in filteredItems" 
            :key="item._type + '-' + item.id"
            class="group relative flex flex-col rounded-2xl overflow-hidden bg-gray-900/70 border border-white/10 hover:border-white/20 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-black"
          >
            <!-- Poster with Play & Remove overlay -->
            <div class="relative aspect-[2/3] w-full overflow-hidden bg-gray-800">
              <img 
                :src="item.poster_url || item.poster_path || 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=500&q=80'" 
                :alt="item.title"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 group-hover:opacity-80 transition-opacity"></div>

              <!-- Center Play Button -->
              <Link 
                :href="item._type === 'tv' ? `/tv-show/${item.slug}` : `/movie/${item.slug}`"
                class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300"
              >
                <div class="w-12 h-12 rounded-full bg-netflix text-white flex items-center justify-center shadow-lg shadow-red-600/50 transform scale-75 group-hover:scale-100 transition-transform">
                  <i class="fas fa-play text-sm ml-0.5"></i>
                </div>
              </Link>

              <!-- Top Left Badge: Media Type -->
              <div class="absolute top-2 left-2 z-10">
                <span class="bg-black/70 backdrop-blur-md text-[10px] font-extrabold uppercase tracking-wider text-white px-2 py-0.5 rounded-md border border-white/10">
                  {{ item._type === 'tv' ? $t('home.tv_show') : $t('home.movie') }}
                </span>
              </div>

              <!-- Top Right Badge: Rating -->
              <div v-if="item.vote_average" class="absolute top-2 right-2 z-10">
                <span class="bg-black/70 backdrop-blur-md text-amber-400 text-[11px] font-bold px-2 py-0.5 rounded-full border border-amber-500/30 flex items-center gap-1 shadow">
                  <i class="fas fa-star text-[9px]"></i>
                  <span>{{ item.vote_average }}</span>
                </span>
              </div>

              <!-- Remove from Watchlist Button -->
              <button 
                @click.prevent.stop="removeFromWatchlist(item)"
                class="absolute bottom-2 right-2 z-10 w-8 h-8 rounded-full bg-black/70 hover:bg-netflix text-white flex items-center justify-center backdrop-blur-md border border-white/10 opacity-90 group-hover:opacity-100 transition shadow"
                :title="$t('watchlist.remove')"
              >
                <i class="fas fa-trash-alt text-xs text-red-400 group-hover:text-white"></i>
              </button>
            </div>

            <!-- Title & Metadata -->
            <div class="p-3 flex flex-col flex-1 justify-between bg-gradient-to-b from-gray-900/40 to-gray-900/90">
              <Link 
                :href="item._type === 'tv' ? `/tv-show/${item.slug}` : `/movie/${item.slug}`" 
                class="block"
              >
                <h3 class="font-bold text-xs sm:text-sm text-white group-hover:text-netflix transition-colors line-clamp-1">
                  {{ item.title }}
                </h3>
              </Link>

              <div class="flex items-center justify-between text-[11px] text-gray-400 mt-2">
                <span>{{ (item.release_date || item.first_air_date || '2025').substring(0, 4) }}</span>
                <span v-if="item.number_of_seasons" class="text-gray-400">
                  {{ $t('tv.seasons', { count: item.number_of_seasons }) }}
                </span>
                <span v-else-if="item.runtime" class="text-gray-400">
                  {{ item.runtime }} min
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div 
        v-else 
        class="py-20 px-4 rounded-3xl bg-white/5 border border-white/10 text-center space-y-4 max-w-lg mx-auto"
      >
        <div class="w-16 h-16 rounded-3xl bg-netflix/10 text-netflix flex items-center justify-center mx-auto text-2xl shadow-inner">
          <i class="fas fa-bookmark"></i>
        </div>
        <div>
          <h3 class="text-lg font-bold text-white">{{ $t('watchlist.no_titles_found') }}</h3>
          <p class="text-xs text-gray-400 mt-1.5 max-w-sm mx-auto leading-relaxed">
            <span v-if="searchQuery">{{ $t('watchlist.no_search_match', { query: searchQuery }) }}</span>
            <span v-else>{{ $t('watchlist.no_watchlist_yet') }}</span>
          </p>
        </div>
        <div class="pt-2 flex items-center justify-center gap-3">
          <button 
            v-if="searchQuery" 
            @click="searchQuery = ''" 
            class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-semibold transition"
          >
            {{ $t('watchlist.clear_search') }}
          </button>
          <Link 
            href="/movies" 
            class="px-5 py-2.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg flex items-center gap-2"
          >
            <i class="fas fa-compass"></i>
            <span>{{ $t('watchlist.explore_catalog') }}</span>
          </Link>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import DashboardLayout from './Layout.vue';

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

const moviesList = ref([...props.movies]);
const showsList = ref([...props.shows]);

const activeFilter = ref('all'); // 'all', 'movie', 'tv'
const searchQuery = ref('');
const sortBy = ref('recent'); // 'recent', 'rating', 'title'

const totalCount = computed(() => {
  return moviesList.value.length + showsList.value.length;
});

const allItems = computed(() => {
  const m = moviesList.value.map(item => ({ ...item, _type: 'movie' }));
  const s = showsList.value.map(item => ({ ...item, _type: 'tv' }));
  return [...m, ...s];
});

const filteredItems = computed(() => {
  let list = allItems.value;

  if (activeFilter.value === 'movie') {
    list = list.filter(item => item._type === 'movie');
  } else if (activeFilter.value === 'tv') {
    list = list.filter(item => item._type === 'tv');
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(item => item.title && item.title.toLowerCase().includes(q));
  }

  // Sorting
  return [...list].sort((a, b) => {
    if (sortBy.value === 'rating') {
      return (b.vote_average || 0) - (a.vote_average || 0);
    }
    if (sortBy.value === 'title') {
      return (a.title || '').localeCompare(b.title || '');
    }
    return (b.id || 0) - (a.id || 0);
  });
});

const removeFromWatchlist = async (item) => {
  try {
    const res = await axios.post('/api/watchlist/toggle', {
      media_type: item._type,
      media_id: item.id,
    });

    if (!res.data.in_watchlist) {
      if (item._type === 'movie') {
        moviesList.value = moviesList.value.filter(m => m.id !== item.id);
      } else {
        showsList.value = showsList.value.filter(s => s.id !== item.id);
      }
    }
  } catch (e) {
    console.error(e);
  }
};
</script>
