<template>
  <AppLayout>
    <Head :title="$t('movies.browse_movies') + ' - Movie®'" />

    <main class="trending-v2 movies-v2">
      <!-- Hero Section with Popular Movie Pick -->
      <section v-if="lead" class="trending-v2-hero">
        <div class="trending-v2-hero-backdrop">
          <img :src="lead.backdrop_path || lead.backdrop_url || lead.poster_path" alt="" fetchpriority="high" />
        </div>
        <div class="trending-v2-hero-shade"></div>

        <div class="trending-v2-shell trending-v2-hero-layout">
          <div class="trending-v2-lead">
            <p class="trending-v2-kicker"><i class="fas fa-film"></i> {{ $t('movies.library') }}</p>
            <div class="trending-v2-lead-title movies-v2-lead-title">
              <div>
                <small>{{ $t('movies.popular_pick') }}</small>
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
              <a href="#movies-catalogue">
                <i class="fas fa-th-large"></i> {{ $t('movies.browse_movies') }}
              </a>
            </div>
          </div>

          <!-- Stack of Popular Picks -->
          <aside v-if="stack && stack.length" class="trending-v2-chart trending-v2-card-stack" :aria-label="$t('movies.popular_picks')">
            <div class="trending-v2-chart-head">
              <div><i class="fas fa-clapperboard"></i><span>{{ $t('movies.popular_picks') }}</span></div>
              <small>{{ $t('movies.for_you') }}</small>
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

      <!-- Movies Catalogue Section -->
      <section id="movies-catalogue" class="trending-v2-catalogue">
        <div class="trending-v2-shell">
          <header class="trending-v2-heading">
            <div>
              <span></span>
              <div>
                <p>{{ $t('movies.explore_subtitle') }}</p>
                <h2>{{ $t('movies.explore_title') }}</h2>
              </div>
            </div>
            <p>{{ $t('movies.explore_desc') }}</p>
          </header>

          <!-- Filter Bar -->
          <form @submit.prevent="applyFilters" class="mb-8 p-3.5 rounded-2xl bg-white/[0.04] border border-white/10 backdrop-blur-md">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5">
              <!-- Search -->
              <div class="trending-v2-search col-span-1 sm:col-span-2 lg:col-span-2">
                <span class="sr-only">{{ $t('movies.search_placeholder') }}</span>
                <i class="fas fa-search"></i>
                <input 
                  type="search" 
                  v-model="filterForm.search" 
                  :placeholder="$t('movies.search_placeholder')" 
                />
              </div>

              <!-- Genre -->
              <div class="trending-v2-select">
                <select v-model="filterForm.genre" @change="applyFilters">
                  <option value="">{{ $t('common.all') }}</option>
                  <option v-for="g in genres" :key="g" :value="g">{{ g }}</option>
                </select>
                <i class="fas fa-chevron-down"></i>
              </div>

              <!-- Min Rating -->
              <div class="trending-v2-select">
                <select v-model="filterForm.min_rating" @change="applyFilters">
                  <option value="">{{ $t('movies.min_rating') }}</option>
                  <option value="8.5">{{ $t('movies.rating_masterpiece') }}</option>
                  <option value="8.0">{{ $t('movies.rating_legend') }}</option>
                  <option value="7.0">{{ $t('movies.rating_great') }}</option>
                  <option value="6.0">{{ $t('movies.rating_good') }}</option>
                </select>
                <i class="fas fa-chevron-down"></i>
              </div>

              <!-- Sort -->
              <div class="trending-v2-select">
                <select v-model="filterForm.sort" @change="applyFilters">
                  <option value="popular">{{ $t('movies.sort_popular') }}</option>
                  <option value="rating">{{ $t('movies.sort_rating') }}</option>
                  <option value="newest">{{ $t('movies.sort_newest') }}</option>
                </select>
                <i class="fas fa-chevron-down"></i>
              </div>
            </div>

            <!-- Quick Filter Chips & Action Row -->
            <div class="mt-3 pt-3 border-t border-white/5 flex flex-wrap items-center justify-between gap-2 text-xs">
              <!-- Year Quick Selectors -->
              <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-gray-400 font-medium mr-1"><i class="fas fa-calendar-alt mr-1"></i> {{ $t('movies.period') }}</span>
                <button 
                  type="button" 
                  @click="setYearRange('', '')"
                  :class="[!filterForm.year_from && !filterForm.year_to ? 'bg-netflix text-white font-semibold' : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10', 'px-2.5 py-1 rounded-full transition']"
                >
                  {{ $t('common.all') }}
                </button>
                <button 
                  type="button" 
                  @click="setYearRange('2024', '2025')"
                  :class="[filterForm.year_from === '2024' ? 'bg-netflix text-white font-semibold' : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10', 'px-2.5 py-1 rounded-full transition']"
                >
                  2024 - 2025
                </button>
                <button 
                  type="button" 
                  @click="setYearRange('2020', '2023')"
                  :class="[filterForm.year_from === '2020' ? 'bg-netflix text-white font-semibold' : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10', 'px-2.5 py-1 rounded-full transition']"
                >
                  2020 - 2023
                </button>
                <button 
                  type="button" 
                  @click="setYearRange('2010', '2019')"
                  :class="[filterForm.year_from === '2010' ? 'bg-netflix text-white font-semibold' : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10', 'px-2.5 py-1 rounded-full transition']"
                >
                  2010 - 2019
                </button>
                <button 
                  type="button" 
                  @click="setYearRange('1990', '2009')"
                  :class="[filterForm.year_from === '1990' ? 'bg-netflix text-white font-semibold' : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10', 'px-2.5 py-1 rounded-full transition']"
                >
                  1990 - 2009
                </button>
                <button 
                  type="button" 
                  @click="setYearRange('', '1989')"
                  :class="[filterForm.year_to === '1989' ? 'bg-netflix text-white font-semibold' : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10', 'px-2.5 py-1 rounded-full transition']"
                >
                  {{ $t('movies.classics') }}
                </button>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-2">
                <button 
                  v-if="hasActiveFilters"
                  type="button" 
                  @click="resetFilters" 
                  class="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-red-500/20 text-gray-400 hover:text-red-400 transition flex items-center gap-1.5"
                >
                  <i class="fas fa-times"></i> {{ $t('movies.reset') }}
                </button>
                <button 
                  type="submit" 
                  class="px-4 py-1.5 rounded-lg bg-netflix hover:bg-red-700 text-white font-semibold shadow-lg shadow-netflix/20 transition flex items-center gap-1.5"
                >
                  <i class="fas fa-filter"></i> {{ $t('movies.filter_action') }}
                </button>
              </div>
            </div>
          </form>

          <!-- Header Banner Ad Slot -->
          <AdSlot placement="header_banner" />

          <!-- Counter -->
          <div class="movies-v2-results">
            <div><i class="fas fa-film"></i><span>{{ $t('movies.library') }}</span></div>
            <p><strong>{{ movies.total || movies.data.length }}</strong> {{ $t('home.movies') }}</p>
          </div>

          <!-- Movies Grid -->
          <div v-if="movies.data && movies.data.length > 0" class="trending-v2-grid">
            <template v-for="(movie, index) in movies.data" :key="'movie-wrap-' + movie.id">
              <!-- In-Feed Ad Every 12 items -->
              <InFeedAd v-if="index > 0 && index % 12 === 0" />

              <article class="trending-v2-card">
                <Link :href="getItemUrl(movie)" class="trending-v2-poster">
                  <img :src="movie.poster_path || movie.poster_url" :alt="movie.title" loading="lazy" />
                  <span class="trending-v2-card-play"><i class="fas fa-play"></i></span>
                  <small class="trending-v2-card-badge">HD</small>
                </Link>
                <div class="trending-v2-card-copy">
                  <Link :href="getItemUrl(movie)">{{ movie.title }}</Link>
                  <p>
                    <span>{{ getYear(movie) }}</span>
                    <span><i class="fas fa-star"></i> {{ Number(movie.vote_average || 7.0).toFixed(1) }}</span>
                  </p>
                  <small>{{ formatGenres(movie.genres) }}</small>
                </div>
              </article>
            </template>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-16 bg-white/5 rounded-xl border border-white/10 my-8">
            <i class="fas fa-film text-4xl text-gray-600 mb-3 block"></i>
            <p class="text-gray-300 font-semibold">{{ $t('movies.no_movies') }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $t('movies.no_movies_desc') }}</p>
          </div>

          <!-- Pagination -->
          <nav v-if="movies.links && movies.links.length > 3" class="trending-v2-pagination">
            <template v-for="(link, i) in movies.links" :key="i">
              <Link 
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                :class="link.active ? 'is-active' : ''"
              />
              <span 
                v-else 
                v-html="link.label"
                class="is-disabled opacity-40 cursor-not-allowed"
              />
            </template>
          </nav>
        </div>
      </section>
    </main>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AdSlot from '@/Components/AdSlot.vue';
import InFeedAd from '@/Components/InFeedAd.vue';

const props = defineProps({
  movies: { type: Object, required: true },
  lead: { type: Object, default: null },
  stack: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  genres: { type: Array, default: () => [] },
});

const filterForm = ref({
  search: props.filters?.search || '',
  genre: props.filters?.genre || '',
  sort: props.filters?.sort || 'popular',
  min_rating: props.filters?.min_rating || '',
  year_from: props.filters?.year_from || '',
  year_to: props.filters?.year_to || '',
});

const hasActiveFilters = computed(() => {
  return Boolean(
    filterForm.value.search ||
    filterForm.value.genre ||
    (filterForm.value.sort && filterForm.value.sort !== 'popular') ||
    filterForm.value.min_rating ||
    filterForm.value.year_from ||
    filterForm.value.year_to
  );
});

const applyFilters = () => {
  const cleanParams = {};
  Object.keys(filterForm.value).forEach(key => {
    if (filterForm.value[key] !== '' && filterForm.value[key] !== null && filterForm.value[key] !== undefined) {
      cleanParams[key] = filterForm.value[key];
    }
  });

  router.get('/movies', cleanParams, {
    preserveState: true,
    preserveScroll: true,
  });
};

const setYearRange = (from, to) => {
  filterForm.value.year_from = from;
  filterForm.value.year_to = to;
  applyFilters();
};

const resetFilters = () => {
  filterForm.value = {
    search: '',
    genre: '',
    sort: 'popular',
    min_rating: '',
    year_from: '',
    year_to: '',
  };
  applyFilters();
};

const getItemUrl = (item) => {
  const rawSlug = item.slug || '';
  const slug = rawSlug.startsWith('watch-') ? rawSlug : `watch-${rawSlug}`;
  return `/movie/${slug}`;
};

const getYear = (item) => {
  const d = item.release_date;
  return d ? String(d).substring(0, 4) : '2025';
};

const getDuration = (item) => {
  if (item.runtime) {
    return typeof item.runtime === 'string' && item.runtime.includes('min') ? item.runtime : `${item.runtime} min`;
  }
  return '1h 48m';
};

const formatGenres = (genres) => {
  if (Array.isArray(genres)) {
    return genres.slice(0, 3).join(' · ');
  }
  if (typeof genres === 'string') {
    return genres;
  }
  return 'Mystery · Animation';
};
</script>
