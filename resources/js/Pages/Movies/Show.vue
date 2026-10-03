<template>
  <AppLayout>
    <Head>
      <title>{{ `${movie.title} (${releaseYear}) - Watch Online Free | Movie®` }}</title>
      <meta name="description" :content="movie.overview ? movie.overview.substring(0, 160) : 'Watch full movie online in HD on Movie®.'" />
      
      <!-- Open Graph -->
      <meta property="og:title" :content="`${movie.title} (${releaseYear})`" />
      <meta property="og:description" :content="movie.overview ? movie.overview.substring(0, 200) : ''" />
      <meta property="og:type" content="video.movie" />
      <meta property="og:image" :content="movie.backdrop_url || movie.poster_url" />
      <meta property="og:url" :content="currentUrl" />

      <!-- Twitter Card -->
      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" :content="`${movie.title} (${releaseYear})`" />
      <meta name="twitter:description" :content="movie.overview ? movie.overview.substring(0, 200) : ''" />
      <meta name="twitter:image" :content="movie.backdrop_url || movie.poster_url" />

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
          <img :src="movie.backdrop_url || movie.backdrop_path || movie.poster_url" alt="" fetchpriority="high">
        </div>
        <div class="movie-view-hero-shade"></div>

        <div class="movie-view-shell movie-view-hero-content">
          <!-- Poster Column with Tomato Score -->
          <div class="movie-view-poster-column">
            <div class="movie-view-poster">
              <img :src="movie.poster_url || movie.poster_path" :alt="`${movie.title} poster`">
              <span>HD</span>
            </div>

            <div class="movie-view-tomato" :aria-label="`Movie rating ${movie.vote_average} out of 10`">
              <img src="/images/fresh.png" alt="Fresh">
              <div>
                <span>Tomatometer</span>
                <strong>{{ movie.tomato_percent || '75%' }}</strong>
              </div>
              <small>{{ movie.vote_average }}/10</small>
            </div>
          </div>

          <!-- Copy Column -->
          <div class="movie-view-copy">
            <p class="movie-view-kicker"><span></span> {{ $t('movies.popular_pick') }}</p>
            <h1>{{ movie.title }}</h1>
            <p v-if="movie.tagline" class="movie-view-tagline">“{{ movie.tagline }}”</p>
            
            <div class="movie-view-meta">
              <span class="movie-view-rating">
                <i class="fas fa-star"></i> {{ movie.vote_average }}<small>/10</small>
              </span>
              <span>{{ releaseYear }}</span>
              <span>{{ movie.runtime || '120 min' }}</span>
              <span class="movie-view-quality">HD</span>
              <span>EN</span>
            </div>

            <!-- Genres -->
            <div v-if="movie.genres && movie.genres.length" class="movie-view-genres">
              <Link 
                v-for="g in movie.genres" 
                :key="g"
                :href="`/movies?genre=${encodeURIComponent(g)}`"
              >
                {{ g }}
              </Link>
            </div>

            <!-- Overview -->
            <p class="movie-view-overview">
              {{ movie.overview || 'No synopsis available for this title.' }}
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
                    <a :href="`https://twitter.com/intent/tweet?url=${currentUrl}&text=${encodeURIComponent(movie.title)}`" target="_blank" class="text-[#1DA1F2] text-sm hover:scale-110 transition"><i class="fab fa-twitter"></i></a>
                    <a :href="`https://api.whatsapp.com/send?text=${encodeURIComponent(movie.title)}%20${currentUrl}`" target="_blank" class="text-[#25D366] text-sm hover:scale-110 transition"><i class="fab fa-whatsapp"></i></a>
                    <button @click="copyLink" class="text-gray-300 hover:text-white text-sm" :title="$t('movies.share')"><i :class="[copied ? 'fas fa-check text-green-400' : 'fas fa-copy']"></i></button>
                  </div>
                </div>
              </details>
            </div>
          </div>
        </div>
      </section>

      <!-- 2. Theater / Video Player Section -->
      <section id="movie-theater" class="movie-view-section movie-view-theater-section" :class="{'relative z-50 py-8': isCinemaMode}">
        <div class="movie-view-shell" :class="{'max-w-[1400px]': isWidePlayer}">
          <div class="movie-view-section-head">
            <div>
              <span class="movie-view-section-mark"></span>
              <div>
                <p>{{ $t('movies.choose_source') }}</p>
                <h2>{{ $t('movies.watch_title', { title: movie.title }) }}</h2>
              </div>
            </div>

            <!-- Cinema Mode & Controls -->
            <div class="flex items-center gap-2">
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

              <button v-if="movie.trailer_url" @click="showTrailerModal = true" class="text-xs text-amber-400 font-semibold hover:underline flex items-center gap-1 ml-1">
                <i class="fab fa-youtube"></i> {{ $t('home.trailer') }}
              </button>

              <button 
                type="button"
                @click="markAsWatched"
                :class="['px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition border', isWatched ? 'bg-green-500/20 text-green-400 border-green-500/30' : 'bg-white/5 hover:bg-white/10 text-gray-300 border-white/10']"
              >
                <i :class="isWatched ? 'fas fa-check-circle' : 'far fa-check-circle'"></i>
                <span>{{ isWatched ? 'İzlendi' : 'İzledim' }}</span>
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
              v-if="activePlayerSource && activePlayerSource.type === 'embed'"
              :src="activePlayerSource.url" 
              class="w-full h-full border-0"
              allowfullscreen
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              @load="playerLoading = false"
            ></iframe>

            <div v-else-if="activePlayerSource && activePlayerSource.type === 'torrent'" class="p-8 text-center flex flex-col items-center justify-center h-full gap-4">
              <i class="fas fa-magnet text-4xl text-netflix"></i>
              <div>
                <h4 class="text-lg font-bold text-white">{{ activePlayerSource.name }}</h4>
                <p class="text-xs text-gray-400 mt-1">{{ $t('movies.magnet_ready') }}</p>
              </div>
              <a :href="activePlayerSource.url" class="px-6 py-2.5 bg-netflix hover:bg-red-700 text-white rounded-lg font-bold text-xs shadow-lg transition">
                {{ $t('movies.open_magnet', { server: activePlayerSource.server }) }}
              </a>
            </div>
          </div>

          <!-- Source Pills -->
          <div class="movie-view-sources" aria-label="Playback sources">
            <button 
              v-for="(source, sIdx) in embedSources" 
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
                <strong class="block font-bold">{{ source.server || `Server ${sIdx + 1}` }}</strong>
                <small class="text-[10px] font-semibold text-emerald-400">{{ source.quality || 'HD' }}</small>
              </span>
              <i v-if="activeSourceIndex === sIdx && playerLoading" class="fas fa-spinner fa-spin loading-indicator ml-1"></i>
            </button>

            <!-- Torrent Modal Button -->
            <button 
              v-if="torrentSources.length" 
              type="button" 
              class="movie-view-torrent" 
              @click="showTorrentModal = true"
            >
              <span class="movie-view-source-icon"><i class="fas fa-magnet"></i></span>
              <span><strong>{{ $t('movies.torrents') }}</strong><small>{{ torrentSources.length }} links</small></span>
            </button>
          </div>

          <!-- Ad Slot: Player Bottom -->
          <AdSlot placement="player_bottom" />
        </div>
      </section>

      <!-- 3. Story, Cast, and Movie Details Section -->
      <section class="movie-view-section movie-view-story">
        <div class="movie-view-shell">
          <div class="movie-view-story-grid">
            <div class="movie-view-story-main">
              <div class="movie-view-section-head compact">
                <div><span class="movie-view-section-mark"></span><div><p>{{ $t('movies.the_story') }}</p><h2>{{ $t('movies.about_movie') }}</h2></div></div>
              </div>
              <p class="movie-view-story-copy">
                {{ movie.overview }}
              </p>

              <!-- Top Cast -->
              <div v-if="movie.cast && movie.cast.length" class="movie-view-cast-head">
                <h3>{{ $t('movies.top_cast') }}</h3>
                <span>{{ $t('movies.credited', { count: movie.cast.length }) }}</span>
              </div>
              <div v-if="movie.cast && movie.cast.length" class="movie-view-cast">
                <article v-for="(actor, cIdx) in movie.cast" :key="cIdx">
                  <div>
                    <img :src="actor.profile_path || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=185&q=80'" :alt="actor.name" loading="lazy" />
                  </div>
                  <strong>{{ actor.name }}</strong>
                  <small>{{ actor.character || 'Actor' }}</small>
                </article>
              </div>
            </div>

            <!-- Movie Details Facts Sidebar -->
            <aside class="movie-view-facts">
              <h3>{{ $t('movies.movie_details') }}</h3>
              <div><span>{{ $t('movies.status') }}</span><strong>{{ $t('movies.released') }}</strong></div>
              <div><span>{{ $t('movies.release_date') }}</span><strong>{{ movie.release_date || '2025' }}</strong></div>
              <div><span>{{ $t('movies.runtime') }}</span><strong>{{ movie.runtime || '120 minutes' }}</strong></div>
              <div><span>{{ $t('admin.language') }}</span><strong>{{ movie.original_language ? movie.original_language.toUpperCase() : 'EN' }}</strong></div>
              <div><span>{{ $t('movies.director') }}</span><strong>{{ movie.director || '-' }}</strong></div>
              <div><span>{{ $t('movies.budget') }}</span><strong>{{ movie.budget || '-' }}</strong></div>
              <div><span>{{ $t('movies.revenue') }}</span><strong>{{ movie.revenue || '-' }}</strong></div>
              <div><span>{{ $t('movies.audience') }}</span><strong>{{ movie.vote_count ? $t('movies.ratings_count', { count: movie.vote_count }) : $t('movies.ratings_count', { count: 1137 }) }}</strong></div>

              <!-- Sidebar Ad Slot -->
              <AdSlot placement="sidebar_banner" custom-class="mt-4" />
            </aside>
          </div>
        </div>
      </section>

      <!-- 4. Community Reviews & 1-10 Ratings -->
      <section class="movie-view-section py-4">
        <div class="movie-view-shell">
          <ReviewsSection 
            :reviews="reviews || []"
            media-type="movie"
            :media-id="movie.id"
            :community-rating="communityRating || movie.vote_average"
          />
        </div>
      </section>

      <!-- 5. Related Movies Section ("More Like This") -->
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
            <Link href="/movies">{{ $t('home.view_all') }} <i class="fas fa-arrow-right"></i></Link>
          </div>
          
          <div class="movie-view-related-rail">
            <article v-for="sim in similar" :key="sim.id">
              <Link :href="`/movie/${sim.slug}`">
                <div>
                  <img :src="sim.poster_url || sim.poster_path" :alt="sim.title" loading="lazy">
                  <i class="fas fa-play movie-view-related-play"></i>
                </div>
                <strong>{{ sim.title }}</strong>
                <small><span>{{ sim.release_date ? sim.release_date.substring(0, 4) : '2025' }}</span><span><i class="fas fa-star"></i>{{ sim.vote_average }}</span></small>
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
              <Link :href="`/movie/${rec.slug}`">
                <div>
                  <img :src="rec.poster_url || rec.poster_path" :alt="rec.title" loading="lazy">
                  <i class="fas fa-play movie-view-related-play"></i>
                </div>
                <strong>{{ rec.title }}</strong>
                <small>
                  <span>{{ rec.release_date ? rec.release_date.substring(0, 4) : '2025' }}</span>
                  <span><i class="fas fa-star text-amber-400"></i>{{ rec.vote_average }}</span>
                </small>
              </Link>
            </article>
          </div>
        </div>
      </section>
    </article>

    <!-- Torrent Links Modal -->
    <div v-if="showTorrentModal" class="movie-view-modal movie-view-torrent-modal" @click="showTorrentModal = false">
      <div @click.stop>
        <button type="button" @click="showTorrentModal = false"><i class="fas fa-times"></i></button>
        <i class="fas fa-magnet movie-view-modal-symbol"></i>
        <h3>{{ $t('movies.torrent_modal_title') }}</h3>
        <p>{{ $t('movies.torrent_modal_desc') }}</p>
        <div class="movie-view-torrent-list">
          <div v-for="(t, tidx) in torrentSources" :key="'t-' + tidx">
            <a :href="t.url">
              <strong>{{ t.name }}</strong>
              <small>Magnet ({{ t.server }})</small>
            </a>
            <button type="button" @click="copyMagnet(t.url, tidx)" :title="$t('movies.share')">
              <i :class="copiedTorrentIndex === tidx ? 'fas fa-check text-green-400' : 'far fa-copy'"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- YouTube Trailer Modal -->
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
              <p class="text-[11px] text-gray-400">{{ movie.title }}</p>
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
      media-type="movie"
      :media-id="movie.id"
      :title="movie.title"
      @close="showAddToListModal = false"
    />
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import ReviewsSection from '@/Components/ReviewsSection.vue';
import AddToListModal from '@/Components/AddToListModal.vue';
import AdSlot from '@/Components/AdSlot.vue';

const props = defineProps({
  movie: Object,
  similar: Array,
  recommendations: Array,
  reviews: Array,
  communityRating: [Number, String],
});

const showAddToListModal = ref(false);
const isSaved = ref(false);
const playerLoading = ref(false);
const activeSourceIndex = ref(0);
const showTorrentModal = ref(false);
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
    '@type': 'Movie',
    'name': props.movie.title,
    'image': props.movie.poster_url || props.movie.backdrop_url,
    'description': props.movie.overview,
    'datePublished': props.movie.release_date,
    'aggregateRating': {
      '@type': 'AggregateRating',
      'ratingValue': props.communityRating || props.movie.vote_average || 8.0,
      'bestRating': '10',
      'ratingCount': (props.reviews?.length || 0) + 142
    }
  });
});

const handleKeyDown = (e) => {
  if (e.key === 'Escape') {
    if (isCinemaMode.value) isCinemaMode.value = false;
    if (showTrailerModal.value) showTrailerModal.value = false;
    if (showTorrentModal.value) showTorrentModal.value = false;
  }
};

const releaseYear = computed(() => {
  return props.movie.release_date ? props.movie.release_date.substring(0, 4) : '2025';
});

const isWatched = ref(false);

const markAsWatched = async () => {
  try {
    await axios.post('/api/watch/progress', {
      media_type: 'movie',
      media_id: props.movie.id,
      progress_percent: 100
    });
    isWatched.value = true;
  } catch (error) {
    console.error('Watch progress error', error);
  }
};

const handleBeforeUnload = () => {
  if (!isWatched.value && navigator.sendBeacon) {
    const data = new Blob([JSON.stringify({
      media_type: 'movie',
      media_id: props.movie.id,
      progress_percent: 25 // Approximate progress on unload
    })], { type: 'application/json' });
    navigator.sendBeacon('/api/watch/progress', data);
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
  window.addEventListener('beforeunload', handleBeforeUnload);
  
  // Fake tracking for partial progress (e.g., 20%) to show up in "Continue Watching"
  setTimeout(() => {
    if (!isWatched.value) {
      axios.post('/api/watch/progress', {
        media_type: 'movie',
        media_id: props.movie.id,
        progress_percent: 20
      }).catch(e => e);
    }
  }, 10000); // Send 20% progress after 10 seconds of being on page
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
  window.removeEventListener('beforeunload', handleBeforeUnload);
});

const currentUrl = computed(() => {
  return typeof window !== 'undefined' ? window.location.href : '';
});

const embedSources = computed(() => {
  const sources = props.movie.stream_servers || [];
  const embeds = sources.filter(s => s.type === 'embed');
  if (embeds.length) return embeds;

  const id = props.movie.tmdb_id || '1368166';
  return [
    { type: 'embed', name: 'Watch Here', url: `https://iembed.top/embed/movie/${id}`, server: 'Watch Here' },
    { type: 'embed', name: 'Server 2', url: `https://vidsrcme.ru/embed/movie/${id}`, server: 'Server 2' },
    { type: 'embed', name: 'Server 3', url: `https://embedmaster.link/movie/${id}`, server: 'Server 3' },
  ];
});

const copiedTorrentIndex = ref(null);

const torrentSources = computed(() => {
  const sources = props.movie.stream_servers || [];
  const tor = sources.filter(s => s.type === 'torrent');
  if (tor.length) return tor;
  const t = encodeURIComponent(props.movie.title || 'Movie');
  return [
    { type: 'torrent', name: '720p (bluray)', server: '1.18 GB', url: `magnet:?xt=urn:btih:7E09509E3E2326E7708C838CDDA043E6D7D9D928&dn=${t}&tr=udp%3A%2F%2Ftracker.opentrackr.org%3A1337%2Fannounce` },
    { type: 'torrent', name: '1080p (bluray)', server: '2.42 GB', url: `magnet:?xt=urn:btih:7A524FDADB18ED1A5BD30A13C9115406FF6A93D5&dn=${t}&tr=udp%3A%2F%2Ftracker.opentrackr.org%3A1337%2Fannounce` },
    { type: 'torrent', name: '2160p 4K (web-dl)', server: '6.85 GB', url: `magnet:?xt=urn:btih:90CEF35FEBF5EA4C7967E4F8720FFC983045C05B&dn=${t}&tr=udp%3A%2F%2Ftracker.opentrackr.org%3A1337%2Fannounce` },
  ];
});

const trailerEmbedUrl = computed(() => {
  const url = props.movie.trailer_url;
  if (!url) {
    const q = encodeURIComponent(`${props.movie.title} official trailer`);
    return `https://www.youtube.com/embed?listType=search&list=${q}&autoplay=1`;
  }
  if (url.includes('embed/')) return url.includes('?') ? `${url}&autoplay=1` : `${url}?autoplay=1`;
  const m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
  if (m && m[1]) {
    return `https://www.youtube.com/embed/${m[1]}?autoplay=1`;
  }
  return url;
});

const copyMagnet = async (magnet, index) => {
  try {
    await navigator.clipboard.writeText(magnet);
    copiedTorrentIndex.value = index;
    setTimeout(() => { copiedTorrentIndex.value = null; }, 2000);
  } catch (e) {
    console.error(e);
  }
};

const activePlayerSource = computed(() => {
  return embedSources.value[activeSourceIndex.value] || embedSources.value[0];
});

const selectSource = (source, index) => {
  activeSourceIndex.value = index;
  playerLoading.value = true;
};

const scrollToTheater = () => {
  const el = document.getElementById('movie-theater');
  if (el) el.scrollIntoView({ behavior: 'smooth' });
};

const toggleWatchlist = async () => {
  try {
    const res = await axios.post('/api/watchlist/toggle', {
      media_type: 'movie',
      media_id: props.movie.id,
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
      media_type: 'movie',
      media_id: props.movie.id,
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
