<template>
  <AppLayout>
    <Head title="Anime Streaming - Watch Trending & Popular Anime - Movie®" />

    <main class="anime-v2">
      <!-- Mobile Hero Section -->
      <section v-if="lead" class="anime-classic-mobile block px-4 pt-4 pb-2 lg:hidden">
        <div class="anime-classic-mobile-card relative min-h-[280px] overflow-hidden rounded-2xl shadow-2xl">
          <img :src="lead.backdrop_path || lead.poster_path" :alt="lead.title" class="absolute inset-0 h-full w-full object-cover object-center" fetchpriority="high" />
          <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/60 to-transparent"></div>
          <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>

          <div class="relative z-10 flex min-h-[280px] flex-col justify-end p-5">
            <div class="mb-2 flex flex-wrap items-center gap-2">
              <span class="rounded bg-netflix/90 px-2 py-1 text-xs font-medium text-white backdrop-blur-sm">
                <i class="fas fa-torii-gate mr-1"></i>ANIME
              </span>
              <span class="flex items-center gap-1 rounded bg-green-600/90 px-2 py-1 text-xs font-medium text-white">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span>{{ $t('anime_section.airing') }}
              </span>
              <span class="flex items-center gap-1 rounded bg-black/60 px-2 py-1 text-xs font-bold text-yellow-400">
                <i class="fas fa-star"></i>{{ Number(lead.vote_average || 8.5).toFixed(1) }}
              </span>
            </div>
            <h1 class="mb-1 text-xl font-bold leading-tight text-white">{{ lead.title }}</h1>
            <p class="mb-3 text-xs text-gray-300">{{ formatGenres(lead.genres) }}</p>
            <div class="flex gap-2">
              <Link :href="`/tv-show/${lead.slug}`" class="flex items-center gap-1.5 rounded-lg bg-netflix px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-netflix/80">
                <i class="fas fa-play"></i>{{ $t('anime_section.watch_now') }}
              </Link>
              <Link :href="`/tv-show/${lead.slug}`" class="glass flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-white/20">
                <i class="fas fa-info-circle"></i>{{ $t('anime_section.info') }}
              </Link>
            </div>
          </div>
        </div>
      </section>

      <!-- Desktop Hero Section -->
      <section v-if="lead" class="anime-classic-desktop relative hidden h-[calc(75vh-12px)] min-h-[520px] overflow-hidden pt-4 lg:block">
        <div class="absolute inset-0 z-0">
          <img :src="lead.backdrop_path || lead.poster_path" :alt="lead.title" class="h-full w-full object-cover object-top opacity-40" fetchpriority="high" />
          <div class="absolute inset-0 bg-gradient-to-r from-gray-900 via-gray-900/80 to-transparent"></div>
          <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent"></div>
        </div>

        <div class="relative z-10 mx-auto flex h-full max-w-7xl gap-6 px-4 sm:px-6 lg:px-8">
          <div class="flex flex-1 flex-col justify-end pb-14">
            <div class="mb-3 flex flex-wrap items-center gap-2">
              <span class="rounded bg-netflix/90 px-2 py-1 text-xs font-medium text-white backdrop-blur-sm">
                <i class="fas fa-torii-gate mr-1"></i>ANIME
              </span>
              <span class="rounded bg-black/60 px-2 py-1 text-xs font-medium text-white backdrop-blur-sm">TV</span>
              <span class="flex items-center gap-1 rounded bg-green-600/90 px-2 py-1 text-xs font-medium text-white">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span>{{ $t('anime_section.airing') }}
              </span>
              <span class="flex items-center gap-1 rounded bg-black/60 px-2 py-1 text-xs font-bold text-yellow-400">
                <i class="fas fa-star"></i>{{ Number(lead.vote_average || 8.5).toFixed(1) }}/10
              </span>
            </div>

            <h1 class="mb-2 text-4xl font-bold leading-tight text-white lg:text-5xl xl:text-6xl">{{ lead.title }}</h1>
            <div class="mb-3 flex items-center gap-3 text-sm text-gray-300">
              <span v-if="lead.number_of_episodes">{{ $t('anime_section.episodes_count', { count: lead.number_of_episodes }) }}</span>
              <span class="text-gray-600">·</span>
              <span>{{ formatGenres(lead.genres) }}</span>
            </div>
            <p class="mb-5 max-w-xl text-sm leading-relaxed text-gray-300 line-clamp-3">
              {{ lead.overview }}
            </p>
            <div class="flex gap-3">
              <Link :href="`/tv-show/${lead.slug}`" class="flex items-center gap-2 rounded-lg bg-netflix px-6 py-3 font-semibold text-white transition-colors hover:bg-netflix/80">
                <i class="fas fa-play"></i>{{ $t('anime_section.watch_now') }}
              </Link>
              <Link :href="`/tv-show/${lead.slug}`" class="glass flex items-center gap-2 rounded-lg px-6 py-3 font-semibold text-white transition-colors hover:bg-white/20">
                <i class="fas fa-info-circle"></i>{{ $t('anime_section.info') }}
              </Link>
            </div>
          </div>

          <!-- Trending Aside -->
          <aside v-if="trendingSide && trendingSide.length" class="flex w-72 flex-shrink-0 flex-col justify-end space-y-4 pb-14">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $t('anime_section.trending_title') }}</p>
            <Link 
              v-for="side in trendingSide" 
              :key="side.id"
              :href="`/tv-show/${side.slug}`"
              class="anime-classic-side-card glass-dark group flex gap-3 rounded-lg p-3 transition-all hover:bg-white/5"
            >
              <div class="relative flex-shrink-0">
                <img :src="side.poster_path || side.backdrop_path" :alt="side.title" class="h-20 w-14 rounded-md object-cover transition-all group-hover:brightness-75" loading="lazy" />
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="line-clamp-2 text-sm font-semibold leading-snug text-white">{{ side.title }}</h3>
                <div class="mt-1 flex items-center gap-2 text-xs text-gray-400">
                  <span class="flex items-center gap-1 text-yellow-400"><i class="fas fa-star"></i>{{ Number(side.vote_average || 7.5).toFixed(1) }}</span>
                  <span v-if="side.number_of_episodes">{{ $t('anime_section.episodes_count', { count: side.number_of_episodes }) }}</span>
                </div>
                <p class="mt-0.5 truncate text-xs text-gray-500">{{ formatGenres(side.genres) }}</p>
              </div>
            </Link>
          </aside>
        </div>
      </section>

      <!-- Catalogue Section -->
      <section class="anime-v2-catalogue">
        <div class="anime-v2-shell">
          <div class="anime-v2-tools">
            <form @submit.prevent="applySearch" class="anime-v2-search">
              <i class="fas fa-search"></i>
              <input type="search" v-model="searchQuery" :placeholder="$t('anime_section.search_placeholder')" autocomplete="off" />
              <button type="submit">{{ $t('common.search') }}</button>
            </form>

            <nav class="anime-v2-genres" aria-label="Anime genres">
              <button 
                type="button" 
                :class="!selectedGenre ? 'is-active' : ''" 
                @click="filterGenre('')"
              >{{ $t('common.all') }}</button>
              <button 
                v-for="g in genres" 
                :key="g"
                type="button" 
                :class="selectedGenre === g ? 'is-active' : ''" 
                @click="filterGenre(g)"
              >{{ g }}</button>
            </nav>
          </div>

          <!-- Airing Grid -->
          <section class="anime-v2-section">
            <header class="anime-v2-section-head">
              <div><span></span><div><p>{{ $t('anime_section.fresh_episodes') }}</p><h2>{{ $t('anime_section.currently_airing') }}</h2></div></div>
              <small>{{ $t('anime_section.anime_titles_count', { count: shows.total || shows.data.length }) }}</small>
            </header>

            <div v-if="shows.data && shows.data.length > 0" class="anime-v2-grid">
              <article v-for="show in shows.data" :key="show.id" class="anime-v2-card">
                <Link :href="`/tv-show/${show.slug}`" class="anime-v2-card-poster">
                  <img :src="show.poster_path || show.backdrop_path" :alt="show.title" loading="lazy" />
                  <div class="anime-v2-card-shade"></div>
                  <span class="anime-v2-card-status"><i></i> {{ $t('anime_section.airing') }}</span>
                  <span class="anime-v2-card-score"><i class="fas fa-star"></i> {{ Number(show.vote_average || 8.0).toFixed(1) }}</span>
                  <span class="anime-v2-card-play"><i class="fas fa-play"></i></span>
                </Link>
                <div class="anime-v2-card-copy">
                  <Link :href="`/tv-show/${show.slug}`">{{ show.title }}</Link>
                  <p>
                    <span v-if="show.number_of_episodes">{{ $t('anime_section.episodes_count', { count: show.number_of_episodes }) }}</span>
                    <span>TV</span>
                  </p>
                  <small>{{ formatGenres(show.genres) }}</small>
                </div>
              </article>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16 bg-white/5 rounded-xl border border-white/10 my-8">
              <i class="fas fa-torii-gate text-4xl text-gray-600 mb-3 block"></i>
              <p class="text-gray-300 font-semibold">{{ $t('anime_section.no_anime') }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ $t('anime_section.no_anime_desc') }}</p>
            </div>

            <!-- Pagination -->
            <div v-if="shows.links && shows.links.length > 3" class="flex justify-center items-center gap-1 pt-8 pb-4">
              <template v-for="(link, i) in shows.links" :key="i">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  v-html="link.label"
                  :class="[
                    link.active 
                      ? 'bg-netflix text-white font-bold' 
                      : 'bg-white/5 hover:bg-white/10 text-gray-300'
                  ]"
                  class="px-3.5 py-2 rounded-lg text-xs transition border border-white/5"
                />
                <span 
                  v-else 
                  v-html="link.label"
                  class="px-3.5 py-2 rounded-lg text-xs text-gray-600 bg-white/5 cursor-not-allowed opacity-50"
                />
              </template>
            </div>
          </section>
        </div>
      </section>
    </main>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  shows: Object,
  lead: Object,
  trendingSide: Array,
  filters: Object,
  genres: Array,
});

const searchQuery = ref(props.filters?.search || props.filters?.q || '');
const selectedGenre = ref(props.filters?.genre || '');

const applySearch = () => {
  router.get('/anime', {
    q: searchQuery.value || undefined,
    genre: selectedGenre.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const filterGenre = (genre) => {
  selectedGenre.value = genre;
  router.get('/anime', {
    q: searchQuery.value || undefined,
    genre: genre || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const formatGenres = (genres) => {
  if (!genres) return '';
  if (Array.isArray(genres)) {
    return genres.slice(0, 3).join(' · ');
  }
  return genres;
};
</script>
