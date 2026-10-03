<template>
  <AppLayout>
    <Head :title="$t('trending.most_watched_today') + ' - Movie®'" />

    <main class="trending-v2">
      <!-- Hero Section with Lead Pick and Live Chart Stack -->
      <section v-if="lead" class="trending-v2-hero">
        <div class="trending-v2-hero-backdrop">
          <img :src="lead.backdrop_path || lead.backdrop_url || lead.poster_path" alt="" fetchpriority="high" />
        </div>
        <div class="trending-v2-hero-shade"></div>

        <div class="trending-v2-shell trending-v2-hero-layout">
          <!-- Lead Item (Rank 01) -->
          <div class="trending-v2-lead">
            <p class="trending-v2-kicker"><i class="fas fa-fire"></i> {{ $t('trending.kicker') }}</p>
            <div class="trending-v2-lead-title">
              <span>01</span>
              <div>
                <small>{{ $t('trending.most_watched_today') }}</small>
                <h1>{{ lead.title }}</h1>
              </div>
            </div>
            <div class="trending-v2-lead-meta">
              <span><i class="fas fa-star"></i> {{ Number(lead.vote_average || 7.5).toFixed(1) }}</span>
              <span>{{ getYear(lead) }}</span>
              <span>{{ getDuration(lead) }}</span>
              <span class="trending-v2-quality">HD</span>
            </div>
            <div class="trending-v2-lead-genres">
              {{ formatGenres(lead.genres) }}
            </div>
            <p class="trending-v2-lead-copy">
              {{ lead.overview }}
            </p>
            <div class="trending-v2-lead-actions">
              <Link :href="getItemUrl(lead)">
                <i class="fas fa-play"></i> {{ $t('movies.view_movie') }}
              </Link>
              <a href="#trending-catalogue">
                <i class="fas fa-arrow-down"></i> {{ $t('trending.browse_chart') }}
              </a>
            </div>
          </div>

          <!-- Mobile Horizontal Swipe Rail of Top Picks (02, 03, 04, 05) -->
          <div v-if="stack && stack.length" class="lg:hidden w-full mt-4">
            <div class="flex items-center justify-between mb-2.5 px-0.5">
              <span class="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fas fa-chart-line text-netflix"></i> {{ $t('trending.top_today') }}
              </span>
              <span class="text-[10px] text-zinc-400 font-semibold">{{ $t('trending.live_chart') }}</span>
            </div>
            <div class="flex gap-2.5 overflow-x-auto no-scrollbar snap-x pb-2 -mx-2 px-2">
              <Link 
                v-for="(item, sIdx) in stack" 
                :key="'m-stack-' + item.id"
                :href="getItemUrl(item)"
                class="flex-shrink-0 w-48 snap-start rounded-2xl bg-white/[0.04] border border-white/10 p-2.5 flex items-center gap-2.5 active:scale-95 touch-press transition"
              >
                <div class="relative w-12 h-16 rounded-xl overflow-hidden flex-shrink-0 bg-zinc-800 shadow-md">
                  <img :src="item.poster_path || item.poster_url" :alt="item.title" class="w-full h-full object-cover" loading="lazy" />
                  <span class="absolute top-0 left-0 bg-netflix text-[9px] font-black text-white px-1.5 py-0.5 rounded-br-lg shadow">
                    #{{ sIdx + 2 }}
                  </span>
                </div>
                <div class="min-w-0 flex-1">
                  <h3 class="text-xs font-bold text-white truncate">{{ item.title }}</h3>
                  <div class="flex items-center gap-2 text-[10px] text-gray-400 mt-1">
                    <span class="text-amber-400 font-bold"><i class="fas fa-star text-[9px]"></i> {{ Number(item.vote_average || 7.0).toFixed(1) }}</span>
                    <span>{{ getYear(item) }}</span>
                  </div>
                  <small class="text-[9px] text-zinc-500 truncate block mt-0.5">{{ formatGenres(item.genres) }}</small>
                </div>
              </Link>
            </div>
          </div>

          <!-- Desktop Stack of Next Top Picks -->
          <aside v-if="stack && stack.length" class="hidden lg:flex trending-v2-chart trending-v2-card-stack" :aria-label="$t('trending.top_today')">
            <div class="trending-v2-chart-head">
              <div><i class="fas fa-chart-line"></i><span>{{ $t('trending.top_today') }}</span></div>
              <small>{{ $t('trending.live_chart') }}</small>
            </div>

            <Link 
              v-for="item in stack" 
              :key="'stack-' + item.id"
              :href="getItemUrl(item)"
            >
              <div class="trending-v2-chart-image">
                <img :src="item.poster_path || item.poster_url" :alt="item.title" loading="lazy" />
              </div>
              <div class="trending-v2-chart-copy">
                <h2>{{ item.title }}</h2>
                <p>
                  <span><i class="fas fa-star"></i> {{ Number(item.vote_average || 7.0).toFixed(1) }}</span>
                  <span>{{ getYear(item) }}</span>
                </p>
                <small>{{ formatGenres(item.genres) }}</small>
              </div>
              <i class="fas fa-chevron-right"></i>
            </Link>
          </aside>
        </div>
      </section>

      <!-- Catalogue Section -->
      <section id="trending-catalogue" class="trending-v2-catalogue pb-dock">
        <div class="trending-v2-shell">
          <header class="trending-v2-heading">
            <div>
              <span></span>
              <div>
                <p>{{ $t('trending.updated_popularity') }}</p>
                <h2>{{ $t('trending.everyone_watching') }}</h2>
              </div>
            </div>
            <p>{{ $t('trending.everyone_desc') }}</p>
          </header>

          <!-- Filter Bar -->
          <form @submit.prevent="applyFilters" class="trending-v2-filters">
            <input type="hidden" name="type" :value="filterForm.type" />
            <label class="trending-v2-search">
              <span class="sr-only">{{ $t('trending.search_placeholder') }}</span>
              <i class="fas fa-search"></i>
              <input 
                type="search" 
                v-model="filterForm.search" 
                :placeholder="$t('trending.search_placeholder')" 
              />
            </label>
            <label class="trending-v2-select">
              <span class="sr-only">{{ $t('common.all') }}</span>
              <select v-model="filterForm.genre" @change="applyFilters">
                <option value="">{{ $t('common.all') }}</option>
                <option v-for="g in genres" :key="g" :value="g">{{ getGenreName(g) }}</option>
              </select>
              <i class="fas fa-chevron-down"></i>
            </label>
            <button type="submit" class="touch-press">
              <i class="fas fa-sliders-h"></i><span>{{ $t('movies.filter_action') }}</span>
            </button>
          </form>

          <!-- Horizontal Genre Filter Chips (Mobile & Desktop) -->
          <div v-if="genres && genres.length" class="flex items-center gap-2 overflow-x-auto no-scrollbar py-2.5 my-1 -mx-2 px-2">
            <button 
              type="button" 
              @click="selectGenre('')"
              :class="[
                'px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all duration-200 cursor-pointer flex-shrink-0 active:scale-95 touch-press',
                filterForm.genre === ''
                  ? 'bg-netflix text-white font-bold shadow-md shadow-red-900/40'
                  : 'bg-white/[0.06] hover:bg-white/[0.14] text-zinc-300 hover:text-white border border-white/10'
              ]"
            >
              {{ $t('common.all') }}
            </button>
            <button 
              v-for="g in genres" 
              :key="'pill-' + g"
              type="button" 
              @click="selectGenre(g)"
              :class="[
                'px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all duration-200 cursor-pointer flex-shrink-0 active:scale-95 touch-press',
                filterForm.genre === g
                  ? 'bg-netflix text-white font-bold shadow-md shadow-red-900/40'
                  : 'bg-white/[0.06] hover:bg-white/[0.14] text-zinc-300 hover:text-white border border-white/10'
              ]"
            >
              {{ getGenreName(g) }}
            </button>
          </div>

          <!-- Tabs (Movies / TV Shows) with Counts -->
          <div class="trending-v2-tabs-row">
            <nav class="trending-v2-tabs" :aria-label="$t('trending.kicker')">
              <button 
                type="button" 
                :class="['touch-press', filterForm.type === 'movies' ? 'is-active' : '']"
                @click="switchType('movies')"
              >
                <i class="fas fa-film"></i>
                <span>{{ $t('home.movies') }}</span>
                <small>{{ moviesCount }}</small>
              </button>
              <button 
                type="button" 
                :class="['touch-press', filterForm.type === 'shows' ? 'is-active' : '']"
                @click="switchType('shows')"
              >
                <i class="fas fa-tv"></i>
                <span>{{ $t('home.series') }}</span>
                <small>{{ showsCount }}</small>
              </button>
            </nav>
            <p class="hidden sm:block"><strong>{{ items.length }}</strong> {{ filterForm.type === 'shows' ? $t('trending.shows_label') : $t('trending.movies_label') }}</p>
          </div>

          <!-- Cards Grid with Rank 01, 02, 03... Numbers -->
          <div class="trending-v2-grid">
            <article 
              v-for="(item, idx) in items" 
              :key="'item-' + item.id" 
              class="trending-v2-card touch-press group"
            >
              <Link :href="getItemUrl(item)" class="trending-v2-poster">
                <span class="trending-v2-rank">{{ String(idx + 1).padStart(2, '0') }}</span>
                <img :src="item.poster_path || item.poster_url" :alt="item.title" loading="lazy" />
                <span class="trending-v2-card-play"><i class="fas fa-play"></i></span>
                <small class="trending-v2-card-badge">HD</small>
              </Link>
              <div class="trending-v2-card-copy">
                <Link :href="getItemUrl(item)">{{ item.title }}</Link>
                <p>
                  <span>{{ getYear(item) }}</span>
                  <span><i class="fas fa-star"></i> {{ Number(item.vote_average || 7.0).toFixed(1) }}</span>
                </p>
                <small>{{ formatGenres(item.genres) }}</small>
              </div>
            </article>
          </div>
        </div>
      </section>
    </main>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from '@/Composables/useI18n';

const { locale } = useI18n();

const props = defineProps({
  lead: { type: Object, default: null },
  stack: { type: Array, default: () => [] },
  items: { type: Array, default: () => [] },
  moviesCount: { type: Number, default: 0 },
  showsCount: { type: Number, default: 0 },
  activeType: { type: String, default: 'movies' },
  filters: { type: Object, default: () => ({}) },
  genres: { type: Array, default: () => [] },
});

const filterForm = ref({
  type: props.filters.type || props.activeType || 'movies',
  search: props.filters.search || '',
  genre: props.filters.genre || '',
});

const applyFilters = () => {
  router.get('/trending', filterForm.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const switchType = (newType) => {
  filterForm.value.type = newType;
  applyFilters();
};

const selectGenre = (genre) => {
  filterForm.value.genre = genre;
  applyFilters();
};

const getItemUrl = (item) => {
  if (!item || !item.slug) return '#';

  let isTv = false;
  if (item.media_type === 'tv') {
    isTv = true;
  } else if (item.media_type === 'movie') {
    isTv = false;
  } else if (Boolean(item.first_air_date) || Boolean(item.number_of_seasons)) {
    isTv = true;
  } else if (Boolean(item.release_date)) {
    isTv = false;
  } else if (item.id === props.lead?.id || props.stack?.some(s => s.id === item.id)) {
    isTv = false;
  } else {
    isTv = filterForm.value.type === 'shows';
  }

  const prefix = isTv ? '/tv-show/' : '/movie/';
  return `${prefix}${item.slug}`;
};

const getYear = (item) => {
  const d = item.release_date || item.first_air_date;
  return d ? String(d).substring(0, 4) : '2025';
};

const getDuration = (item) => {
  if (item.number_of_seasons) {
    if (locale.value === 'tr') {
      return `${item.number_of_seasons} Sezon`;
    }
    return `${item.number_of_seasons} Season${item.number_of_seasons > 1 ? 's' : ''}`;
  }
  if (item.runtime) {
    const minStr = locale.value === 'tr' ? 'dk' : 'min';
    return typeof item.runtime === 'string' && item.runtime.includes('min')
      ? (locale.value === 'tr' ? item.runtime.replace('min', 'dk') : item.runtime)
      : `${item.runtime} ${minStr}`;
  }
  return locale.value === 'tr' ? '1 sa 48 dk' : '1h 48m';
};

const genreTranslations = {
  'Action': 'Aksiyon',
  'Adventure': 'Macera',
  'Animation': 'Animasyon',
  'Comedy': 'Komedi',
  'Crime': 'Suç',
  'Drama': 'Dram',
  'Family': 'Aile',
  'Fantasy': 'Fantastik',
  'History': 'Tarih',
  'Horror': 'Korku',
  'Music': 'Müzik',
  'Mystery': 'Gizem',
  'Romance': 'Romantik',
  'Science Fiction': 'Bilim Kurgu',
  'Sci-Fi & Fantasy': 'Bilim Kurgu & Fantastik',
  'Thriller': 'Gerilim',
  'War': 'Savaş',
  'War & Politics': 'Savaş & Politika',
  'Western': 'Vahşi Batı',
  'Action & Adventure': 'Aksiyon & Macera',
  'Talk': 'Talk Show',
  'Soap': 'Pembe Dizi',
  'Reality': 'Reality',
};

const getGenreName = (g) => {
  if (locale.value === 'tr') {
    return genreTranslations[g] || g;
  }
  return g;
};

const formatGenres = (genres) => {
  let list = [];
  if (Array.isArray(genres)) {
    list = genres;
  } else if (typeof genres === 'string') {
    try {
      const parsed = JSON.parse(genres);
      if (Array.isArray(parsed)) list = parsed;
      else list = genres.split(',').map((s) => s.trim());
    } catch {
      list = genres.split(',').map((s) => s.trim());
    }
  }
  if (list && list.length) {
    return list.slice(0, 3).map((g) => getGenreName(g)).join(' · ');
  }
  return locale.value === 'tr' ? 'Aksiyon · Macera' : 'Action · Adventure';
};
</script>
