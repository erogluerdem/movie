<template>
  <AppLayout>
    <Head>
      <title>{{ `${show.title} (${firstAirYear}) - Watch Online Free | Movie®` }}</title>
      <meta name="description" :content="show.overview ? show.overview.substring(0, 160) : 'Watch TV show episodes online in HD on Movie®.'" />
      
      <!-- Open Graph -->
      <meta property="og:title" :content="`${show.title} (${firstAirYear})`" />
      <meta property="og:description" :content="show.overview ? show.overview.substring(0, 200) : ''" />
      <meta property="og:type" content="video.tv_show" />
      <meta property="og:image" :content="show.backdrop_url || show.poster_url" />
      <meta property="og:url" :content="currentUrl" />

      <!-- Twitter Card -->
      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" :content="`${show.title} (${firstAirYear})`" />
      <meta name="twitter:description" :content="show.overview ? show.overview.substring(0, 200) : ''" />
      <meta name="twitter:image" :content="show.backdrop_url || show.poster_url" />

      <!-- Schema.org JSON-LD -->
      <component is="script" type="application/ld+json">
        {{ jsonLdSchema }}
      </component>
    </Head>

    <!-- Cinema Mode ("Lights Off") Overlay -->
    <div 
      v-if="isCinemaMode" 
      class="fixed inset-0 bg-black/95 z-40 transition-opacity duration-300 flex items-start justify-end p-6 cursor-pointer"
      @click="isCinemaMode = false"
    >
      <button 
        type="button"
        @click.stop="isCinemaMode = false" 
        class="flex items-center gap-2 px-5 py-2.5 rounded-full bg-white/10 hover:bg-white/20 text-white text-xs font-bold backdrop-blur border border-white/15 transition shadow-2xl"
      >
        <i class="fas fa-lightbulb text-amber-400"></i>
        <span>{{ $t('movies.lights_on') }}</span>
        <span class="text-gray-400 text-[10px] ml-1">(Esc)</span>
      </button>
    </div>

    <article class="movie-view-v2">
      <!-- 1. Hero Section -->
      <section class="movie-view-hero">
        <div class="movie-view-backdrop">
          <img :src="show.backdrop_url || show.backdrop_path || show.poster_url" alt="" fetchpriority="high">
        </div>
        <div class="movie-view-hero-shade"></div>

        <div class="movie-view-shell movie-view-hero-content">
          <!-- Poster Column with Tomato Score -->
          <div class="movie-view-poster-column">
            <div class="movie-view-poster">
              <img :src="show.poster_url || show.poster_path" :alt="`${show.title} poster`">
              <span>HD</span>
            </div>

            <div class="movie-view-tomato" :aria-label="`Rating ${show.vote_average} out of 10`">
              <img src="/images/fresh.png" alt="Fresh">
              <div>
                <span>Tomatometer</span>
                <strong>{{ show.tomato_percent || '84%' }}</strong>
              </div>
              <small>{{ show.vote_average }}/10</small>
            </div>
          </div>

          <!-- Copy Column -->
          <div class="movie-view-copy">
            <p class="movie-view-kicker"><span></span> {{ show.is_anime ? $t('home.anime') : $t('tv.popular_series') }}</p>
            <h1>{{ show.title }}</h1>
            <p v-if="show.tagline" class="movie-view-tagline">“{{ show.tagline }}”</p>

            <div class="movie-view-meta">
              <span class="movie-view-rating">
                <i class="fas fa-star"></i> {{ show.vote_average }}<small>/10</small>
              </span>
              <span>{{ firstAirYear }}</span>
              <span>{{ $t('tv.seasons', { count: show.number_of_seasons }) }}</span>
              <span class="movie-view-quality">HD</span>
              <span>EN</span>
            </div>

            <!-- Genres -->
            <div v-if="show.genres && show.genres.length" class="movie-view-genres">
              <Link 
                v-for="g in show.genres" 
                :key="g"
                :href="`/tv-shows?genre=${encodeURIComponent(g)}`"
              >
                {{ g }}
              </Link>
            </div>

            <!-- Overview -->
            <p class="movie-view-overview">
              {{ show.overview || 'No synopsis available for this series.' }}
            </p>

            <!-- Actions Bar -->
            <div class="movie-view-actions">
              <button type="button" class="movie-view-watch" @click="scrollToTheater">
                <i class="fas fa-play"></i>
                <span>{{ $t('home.watch_now') }}</span>
              </button>

              <button 
                type="button" 
                @click="toggleWatchlist" 
                :class="['movie-view-icon', isSaved ? 'text-netflix' : '']"
                :title="isSaved ? $t('common.delete') : $t('home.my_list')"
              >
                <i :class="[isSaved ? 'fas fa-check text-green-400' : 'fas fa-plus']"></i>
              </button>

              <!-- Add to Custom List (Letterboxd style) -->
              <button 
                type="button" 
                @click="showAddToListModal = true" 
                class="movie-view-icon hover:text-amber-400 transition"
                :title="$t('movies.custom_list_add')"
              >
                <i class="fas fa-bookmark"></i>
              </button>

              <!-- Share Dropdown -->
              <details class="movie-view-more">
                <summary class="movie-view-icon" :title="$t('movies.share')"><i class="fas fa-ellipsis-h"></i></summary>
                <div class="movie-view-more-menu p-3 text-xs bg-black/95 border border-white/10 rounded-xl shadow-2xl">
                  <div class="flex items-center space-x-3">
                    <span class="text-gray-400 text-xs">{{ $t('movies.share') }}:</span>
                    <a :href="`https://www.facebook.com/sharer/sharer.php?u=${currentUrl}`" target="_blank" class="text-[#1877F2] text-sm hover:scale-110 transition"><i class="fab fa-facebook-f"></i></a>
                    <a :href="`https://twitter.com/intent/tweet?url=${currentUrl}&text=${encodeURIComponent(show.title)}`" target="_blank" class="text-[#1DA1F2] text-sm hover:scale-110 transition"><i class="fab fa-twitter"></i></a>
                    <a :href="`https://api.whatsapp.com/send?text=${encodeURIComponent(show.title)}%20${currentUrl}`" target="_blank" class="text-[#25D366] text-sm hover:scale-110 transition"><i class="fab fa-whatsapp"></i></a>
                    <button @click="copyLink" class="text-gray-300 hover:text-white text-sm" :title="$t('movies.share')"><i :class="[copied ? 'fas fa-check text-green-400' : 'fas fa-copy']"></i></button>
                  </div>
                </div>
              </details>
            </div>
          </div>
        </div>
      </section>

      <!-- 2. Theater Section (Episode Player) -->
      <section id="tv-theater" class="movie-view-section movie-view-theater-section" :class="{'relative z-50 py-8': isCinemaMode}">
        <div class="movie-view-shell" :class="{'max-w-[1400px]': isWidePlayer}">
          <div class="movie-view-section-head">
            <div>
              <span class="movie-view-section-mark"></span>
              <div>
                <p>{{ $t('tv.now_streaming') }}</p>
                <h2>{{ $t('tv.seasons', { count: currentSeason?.season_number || 1 }) }}, {{ $t('tv.episodes', { count: currentEpisode?.episode_number || 1 }) }}: {{ currentEpisode?.name }}</h2>
              </div>
            </div>

            <!-- Cinema Mode & Controls -->
            <div class="flex items-center gap-2 flex-wrap">
              <!-- Prev / Next Episode Navigation -->
              <button 
                type="button" 
                v-if="hasPrevEpisode"
                @click="goToPrevEpisode"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition border bg-white/5 hover:bg-white/10 text-gray-300 border-white/10"
                :title="$t('tv.prev_episode')"
              >
                <i class="fas fa-step-backward text-xs"></i>
                <span class="hidden sm:inline">{{ $t('tv.prev_episode') }}</span>
              </button>

              <button 
                type="button" 
                v-if="hasNextEpisode"
                @click="goToNextEpisode"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition border bg-indigo-600 hover:bg-indigo-500 text-white border-indigo-500 shadow-md shadow-indigo-600/30"
                :title="$t('tv.next_episode')"
              >
                <span>{{ $t('tv.next_episode') }}</span>
                <i class="fas fa-step-forward text-xs"></i>
              </button>

              <button 
                type="button" 
                @click="isCinemaMode = !isCinemaMode"
                :class="['px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition border', isCinemaMode ? 'bg-amber-500 text-black border-amber-400 shadow-lg shadow-amber-500/20' : 'bg-white/5 hover:bg-white/10 text-gray-300 border-white/10']"
                :title="$t('movies.cinema_mode')"
              >
                <i :class="isCinemaMode ? 'fas fa-lightbulb' : 'far fa-lightbulb'"></i>
                <span>{{ isCinemaMode ? $t('movies.lights_on') : $t('movies.cinema_mode') }}</span>
              </button>

              <button 
                type="button"
                @click="isWidePlayer = !isWidePlayer"
                :class="['px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition border', isWidePlayer ? 'bg-netflix text-white border-red-600' : 'bg-white/5 hover:bg-white/10 text-gray-300 border-white/10']"
                :title="$t('movies.wide_size')"
              >
                <i :class="isWidePlayer ? 'fas fa-compress' : 'fas fa-expand'"></i>
                <span>{{ isWidePlayer ? $t('movies.normal_size') : $t('movies.wide_size') }}</span>
              </button>

              <button 
                type="button"
                @click="showReportModal = true"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition border bg-red-500/10 hover:bg-red-500/20 text-red-400 border-red-500/30"
                :title="$t('movies.report_issue')"
              >
                <i class="fas fa-flag"></i>
                <span>{{ $t('movies.report_issue') }}</span>
              </button>

              <button v-if="show.trailer_url" @click="showTrailerModal = true" class="text-xs text-amber-400 font-semibold hover:underline flex items-center gap-1 ml-1">
                <i class="fab fa-youtube"></i> {{ $t('home.trailer') }}
              </button>
            </div>
          </div>

          <!-- Ad Slot: Player Top -->
          <AdSlot placement="player_top" />

          <!-- Video Player Box -->
          <div 
            class="movie-view-player w-full rounded-xl overflow-hidden bg-black border border-white/10 relative my-4 shadow-2xl transition-all duration-300"
            :class="[isWidePlayer ? 'aspect-[16/9] md:aspect-[21/9]' : 'aspect-video', isCinemaMode ? 'ring-2 ring-netflix/50 shadow-netflix/20' : '']"
          >
            <div v-if="playerLoading" class="movie-view-player-loading absolute inset-0 bg-black/90 flex flex-col items-center justify-center gap-3 z-10">
              <i class="fas fa-spinner fa-spin text-2xl text-netflix"></i>
              <span>{{ $t('movies.preparing_movie') }}</span>
            </div>

            <iframe 
              v-if="activePlayerSource"
              :src="activePlayerSource.url" 
              class="w-full h-full border-0"
              allowfullscreen
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              @load="playerLoading = false"
            ></iframe>
          </div>

          <!-- Source Pills -->
          <div class="movie-view-sources" aria-label="Playback sources">
            <button 
              v-for="(source, sIdx) in episodeSources" 
              :key="'source-' + sIdx"
              type="button" 
              :class="['player-option', activeSourceIndex === sIdx ? 'bg-netflix text-white border-netflix ring-1 ring-netflix shadow-lg shadow-netflix/30' : 'bg-white/5 hover:bg-white/10 text-gray-300 border-white/10']"
              class="px-4 py-2 rounded-xl flex items-center gap-2.5 transition border text-xs"
              @click="selectSource(source, sIdx)"
            >
              <span class="w-6 h-6 rounded-lg bg-netflix/20 text-netflix flex items-center justify-center text-xs">
                <i class="fas fa-server"></i>
              </span>
              <span class="text-left">
                <strong class="block font-bold">{{ source.server || source.name || `Server ${sIdx + 1}` }}</strong>
                <small class="text-[10px] font-semibold text-emerald-400">{{ source.quality || 'HD' }}</small>
              </span>
              <i v-if="activeSourceIndex === sIdx && playerLoading" class="fas fa-spinner fa-spin loading-indicator ml-1"></i>
            </button>
          </div>

          <!-- Ad Slot: Player Bottom -->
          <AdSlot placement="player_bottom" />
        </div>
      </section>

      <!-- 3. Episode Selector Section -->
      <section class="movie-view-section">
        <div class="movie-view-shell">
          <EpisodeSelector 
            v-if="show.seasons && show.seasons.length"
            :seasons="show.seasons"
            :initialSeasonId="currentSeason?.id"
            :activeEpisode="currentEpisode"
            :fallbackImage="show.backdrop_url || show.poster_url"
            @select-season="onSeasonSelected"
            @select-episode="onEpisodeSelected"
          />
        </div>
      </section>

      <!-- 4. Story, Cast, and Facts Section -->
      <section class="movie-view-section movie-view-story">
        <div class="movie-view-shell">
          <div class="movie-view-story-grid">
            <div class="movie-view-story-main">
              <div class="movie-view-section-head compact">
                <div><span class="movie-view-section-mark"></span><div><p>{{ $t('movies.the_story') }}</p><h2>{{ $t('movies.about_movie') }}</h2></div></div>
              </div>
              <p class="movie-view-story-copy">
                {{ show.overview }}
              </p>

              <!-- Top Cast -->
              <div v-if="show.cast && show.cast.length" class="movie-view-cast-head">
                <h3>{{ $t('movies.top_cast') }}</h3>
                <span>{{ $t('movies.credited', { count: show.cast.length }) }}</span>
              </div>
              <div v-if="show.cast && show.cast.length" class="movie-view-cast">
                <article v-for="(actor, cIdx) in show.cast" :key="cIdx">
                  <div>
                    <img :src="actor.profile_path || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=185&q=80'" :alt="actor.name" loading="lazy" />
                  </div>
                  <strong>{{ actor.name }}</strong>
                  <small>{{ actor.character || 'Regular' }}</small>
                </article>
              </div>
            </div>

            <!-- TV Show Facts Sidebar -->
            <aside class="movie-view-facts">
              <h3>{{ $t('movies.movie_details') }}</h3>
              <div><span>{{ $t('movies.status') }}</span><strong>{{ show.status || 'Active' }}</strong></div>
              <div><span>{{ $t('movies.release_date') }}</span><strong>{{ show.first_air_date || '2024' }}</strong></div>
              <div><span>{{ $t('home.series') }}</span><strong>{{ show.number_of_seasons }}</strong></div>
              <div><span>{{ $t('tv.episodes_title') }}</span><strong>{{ show.number_of_episodes || '16' }}</strong></div>
              <div><span>{{ $t('admin.language') }}</span><strong>EN</strong></div>
              <div><span>{{ $t('home.quality') || 'Kalite' }}</span><strong class="text-netflix">1080p HD</strong></div>
              <div><span>{{ $t('movies.audience') }}</span><strong>{{ show.vote_count ? $t('movies.ratings_count', { count: show.vote_count }) : $t('movies.ratings_count', { count: 1420 }) }}</strong></div>

              <!-- Sidebar Ad Slot -->
              <AdSlot placement="sidebar_banner" custom-class="mt-4" />
            </aside>
          </div>
        </div>
      </section>

      <!-- 5. Community Reviews & 1-10 Ratings -->
      <section class="movie-view-section py-4">
        <div class="movie-view-shell">
          <ReviewsSection 
            :reviews="reviews || []"
            media-type="tv"
            :media-id="show.id"
            :community-rating="communityRating || show.vote_average"
          />
        </div>
      </section>

      <!-- 6. Related Rail -->
      <section v-if="similar && similar.length" class="movie-view-section movie-view-related">
        <div class="movie-view-shell">
          <div class="movie-view-section-head">
            <div>
              <span class="movie-view-section-mark"></span>
              <div>
                <p>{{ $t('movies.because_watched') }}</p>
                <h2>{{ $t('movies.more_like_this') }}</h2>
              </div>
            </div>
            <Link href="/tv-shows">{{ $t('home.view_all') }} <i class="fas fa-arrow-right"></i></Link>
          </div>
          
          <div class="movie-view-related-rail">
            <article v-for="sim in similar" :key="sim.id">
              <Link :href="`/tv-show/${sim.slug}`">
                <div>
                  <img :src="sim.poster_url || sim.poster_path" :alt="sim.title" loading="lazy">
                  <i class="fas fa-play movie-view-related-play"></i>
                </div>
                <strong>{{ sim.title }}</strong>
                <small><span>{{ sim.first_air_date ? sim.first_air_date.substring(0, 4) : '2024' }}</span><span><i class="fas fa-star"></i>{{ sim.vote_average }}</span></small>
              </Link>
            </article>
          </div>
        </div>
      </section>

      <!-- 6. TMDB Recommendations Section ("Bunu Beğenenler Bunları da Sevdi") -->
      <section v-if="recommendations && recommendations.length" class="movie-view-section movie-view-related pt-0">
        <div class="movie-view-shell">
          <div class="movie-view-section-head">
            <div>
              <span class="movie-view-section-mark" style="background-color: #6366f1;"></span>
              <div>
                <p class="text-indigo-400 font-bold">{{ $t('movies.tmdb_smart_recs') }}</p>
                <h2>{{ $t('movies.tmdb_people_also_liked') }}</h2>
              </div>
            </div>
          </div>
          
          <div class="movie-view-related-rail">
            <article v-for="rec in recommendations" :key="'rec-' + rec.id">
              <Link :href="`/tv-show/${rec.slug}`">
                <div>
                  <img :src="rec.poster_url || rec.poster_path" :alt="rec.title" loading="lazy">
                  <i class="fas fa-play movie-view-related-play"></i>
                </div>
                <strong>{{ rec.title }}</strong>
                <small>
                  <span>{{ rec.release_date ? rec.release_date.substring(0, 4) : '2024' }}</span>
                  <span><i class="fas fa-star text-amber-400"></i>{{ rec.vote_average }}</span>
                </small>
              </Link>
            </article>
          </div>
        </div>
      </section>
    </article>

    <!-- Trailer Modal -->
    <div v-if="showTrailerModal" class="movie-view-modal movie-view-trailer-modal" @click="showTrailerModal = false">
      <div @click.stop>
        <button type="button" @click="showTrailerModal = false"><i class="fas fa-times"></i></button>
        <div id="trailerContainer" class="w-full aspect-video">
          <iframe 
            :src="trailerEmbedUrl" 
            class="w-full h-full border-0" 
            allowfullscreen 
            allow="autoplay; encrypted-media"
          ></iframe>
        </div>
      </div>
    </div>

    <!-- Stream Issue Report Modal -->
    <div v-if="showReportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click="showReportModal = false">
      <div @click.stop class="w-full max-w-md bg-gray-950 border border-white/10 rounded-2xl shadow-2xl p-6 space-y-4 text-left">
        <div class="flex items-center justify-between pb-3 border-b border-white/10">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-red-500/10 text-netflix flex items-center justify-center text-sm">
              <i class="fas fa-flag"></i>
            </div>
            <div>
              <h3 class="text-sm font-bold text-white">{{ $t('movies.report_modal_title') }}</h3>
              <p class="text-[11px] text-gray-400">{{ show.title }} - S{{ currentSeason?.season_number }}E{{ currentEpisode?.episode_number }}</p>
            </div>
          </div>
          <button @click="showReportModal = false" class="text-gray-400 hover:text-white text-sm">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div v-if="reportSubmitted" class="py-6 text-center text-emerald-400 space-y-2">
          <i class="fas fa-check-circle text-4xl"></i>
          <div class="text-sm font-bold text-white">{{ $t('movies.report_success_title') }}</div>
          <p class="text-xs text-gray-400">{{ $t('movies.report_success_desc') }}</p>
        </div>

        <form v-else @submit.prevent="submitReport" class="space-y-4">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-300">{{ $t('movies.report_issue_type') }}</label>
            <select 
              v-model="reportForm.issue_type" 
              class="w-full px-3.5 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix"
            >
              <option value="dead_link">{{ $t('movies.report_dead_link') }}</option>
              <option value="audio_desync">{{ $t('movies.report_audio_desync') }}</option>
              <option value="buffering">{{ $t('movies.report_buffering') }}</option>
              <option value="wrong_video">{{ $t('movies.report_wrong_video') }}</option>
              <option value="other">{{ $t('movies.report_other') }}</option>
            </select>
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-gray-300">{{ $t('movies.report_notes') }}</label>
            <textarea 
              v-model="reportForm.notes" 
              rows="3" 
              :placeholder="$t('movies.report_placeholder')"
              class="w-full px-3.5 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix placeholder-gray-500"
            ></textarea>
          </div>

          <div class="text-[11px] text-gray-500 bg-white/5 p-2.5 rounded-xl border border-white/5">
            <span>{{ $t('movies.report_active_server') }} </span>
            <strong class="text-gray-300">{{ activePlayerSource?.name || 'Ana Sunucu' }}</strong>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2">
            <button 
              type="button" 
              @click="showReportModal = false" 
              class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white text-xs transition"
            >
              {{ $t('movies.report_cancel') }}
            </button>
            <button 
              type="submit" 
              :disabled="reportSubmitting"
              class="px-5 py-2 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition flex items-center gap-2 disabled:opacity-50"
            >
              <i :class="reportSubmitting ? 'fas fa-spinner fa-spin' : 'fas fa-paper-plane'"></i>
              <span>{{ $t('movies.report_submit') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add to Custom List Modal -->
    <AddToListModal
      :is-open="showAddToListModal"
      media-type="tv"
      :media-id="show.id"
      :title="show.title"
      @close="showAddToListModal = false"
    />
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import EpisodeSelector from '@/Components/EpisodeSelector.vue';
import ReviewsSection from '@/Components/ReviewsSection.vue';
import AddToListModal from '@/Components/AddToListModal.vue';
import AdSlot from '@/Components/AdSlot.vue';

const props = defineProps({
  show: Object,
  selectedSeason: Object,
  selectedEpisode: Object,
  similar: Array,
  recommendations: Array,
  reviews: Array,
  communityRating: [Number, String],
});

const showAddToListModal = ref(false);
const isSaved = ref(false);
const playerLoading = ref(false);
const activeSourceIndex = ref(0);
const showTrailerModal = ref(false);
const showReportModal = ref(false);
const reportSubmitting = ref(false);
const reportSubmitted = ref(false);
const reportForm = reactive({
  issue_type: 'dead_link',
  notes: '',
});
const copied = ref(false);
const isCinemaMode = ref(false);
const isWidePlayer = ref(false);

const jsonLdSchema = computed(() => {
  return JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'TVSeries',
    'name': props.show.title,
    'image': props.show.poster_url || props.show.backdrop_url,
    'description': props.show.overview,
    'startDate': props.show.first_air_date,
    'numberOfSeasons': props.show.number_of_seasons || 1,
    'numberOfEpisodes': props.show.number_of_episodes || 12,
    'aggregateRating': {
      '@type': 'AggregateRating',
      'ratingValue': props.communityRating || props.show.vote_average || 8.0,
      'bestRating': '10',
      'ratingCount': (props.reviews?.length || 0) + 168
    }
  });
});

const handleKeyDown = (e) => {
  if (e.key === 'Escape') {
    if (isCinemaMode.value) isCinemaMode.value = false;
    if (showTrailerModal.value) showTrailerModal.value = false;
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

const currentSeason = ref(props.selectedSeason || (props.show.seasons ? props.show.seasons[0] : null));
const currentEpisode = ref(props.selectedEpisode || (currentSeason.value?.episodes ? currentSeason.value.episodes[0] : null));

const allEpisodes = computed(() => {
  if (!props.show?.seasons) return [];
  const list = [];
  props.show.seasons.forEach((s) => {
    if (s.episodes) {
      s.episodes.forEach((e) => {
        list.push({
          ...e,
          seasonNumber: s.season_number,
        });
      });
    }
  });
  return list;
});

const currentEpisodeIndex = computed(() => {
  if (!currentEpisode.value || !allEpisodes.value.length) return -1;
  return allEpisodes.value.findIndex((e) => e.id === currentEpisode.value.id);
});

const hasPrevEpisode = computed(() => currentEpisodeIndex.value > 0);
const hasNextEpisode = computed(() => currentEpisodeIndex.value >= 0 && currentEpisodeIndex.value < allEpisodes.value.length - 1);

const goToPrevEpisode = () => {
  if (!hasPrevEpisode.value) return;
  const prevEp = allEpisodes.value[currentEpisodeIndex.value - 1];
  router.visit(`/tv-show/${props.show.slug}?season=${prevEp.seasonNumber}&episode=${prevEp.episode_number}`, { preserveScroll: true });
};

const goToNextEpisode = () => {
  if (!hasNextEpisode.value) return;
  const nextEp = allEpisodes.value[currentEpisodeIndex.value + 1];
  router.visit(`/tv-show/${props.show.slug}?season=${nextEp.seasonNumber}&episode=${nextEp.episode_number}`, { preserveScroll: true });
};

const firstAirYear = computed(() => {
  return props.show.first_air_date ? props.show.first_air_date.substring(0, 4) : '2024';
});

const currentUrl = computed(() => {
  return typeof window !== 'undefined' ? window.location.href : '';
});

const trailerEmbedUrl = computed(() => {
  const url = props.show.trailer_url;
  if (!url) {
    const q = encodeURIComponent(`${props.show.title} official trailer`);
    return `https://www.youtube.com/embed?listType=search&list=${q}&autoplay=1`;
  }
  if (url.includes('embed/')) return url.includes('?') ? `${url}&autoplay=1` : `${url}?autoplay=1`;
  const m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
  if (m && m[1]) {
    return `https://www.youtube.com/embed/${m[1]}?autoplay=1`;
  }
  return url;
});

const episodeSources = computed(() => {
  if (currentEpisode.value?.stream_servers && currentEpisode.value.stream_servers.length) {
    return currentEpisode.value.stream_servers;
  }
  const id = props.show.tmdb_id || '100001';
  const sNum = currentSeason.value?.season_number || 1;
  const eNum = currentEpisode.value?.episode_number || 1;
  return [
    { name: 'Server 1 (Fast)', url: `https://iembed.top/embed/tv/${id}/${sNum}/${eNum}` },
    { name: 'Server 2 (HD)', url: `https://vidsrcme.ru/embed/tv/${id}/${sNum}/${eNum}` },
    { name: 'Server 3', url: `https://embedmaster.link/tv/${id}/${sNum}/${eNum}` },
  ];
});

const activePlayerSource = computed(() => {
  return episodeSources.value[activeSourceIndex.value] || episodeSources.value[0];
});

const selectSource = (source, index) => {
  activeSourceIndex.value = index;
  playerLoading.value = true;
};

const onSeasonSelected = (season) => {
  currentSeason.value = season;
};

const onEpisodeSelected = (episode) => {
  currentEpisode.value = episode;
  playerLoading.value = true;
  scrollToTheater();
};

const scrollToTheater = () => {
  const el = document.getElementById('tv-theater');
  if (el) el.scrollIntoView({ behavior: 'smooth' });
};

const toggleWatchlist = async () => {
  try {
    const res = await axios.post('/api/watchlist/toggle', {
      media_type: 'tv',
      media_id: props.show.id,
    });
    isSaved.value = res.data.in_watchlist;
  } catch (e) {
    console.error(e);
  }
};

const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(window.location.href);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
  } catch (e) {
    console.error(e);
  }
};

const submitReport = async () => {
  reportSubmitting.value = true;
  try {
    await axios.post('/api/reports', {
      media_type: 'tv',
      media_id: props.show.id,
      server_name: activePlayerSource.value?.name || 'Ana Sunucu',
      issue_type: reportForm.issue_type,
      notes: reportForm.notes,
    });
    reportSubmitted.value = true;
    setTimeout(() => {
      showReportModal.value = false;
      reportSubmitted.value = false;
      reportForm.notes = '';
    }, 2000);
  } catch (e) {
    alert('Bildirim gönderilirken bir hata oluştu.');
  } finally {
    reportSubmitting.value = false;
  }
};
</script>
