<template>
  <DashboardLayout>
    <div class="space-y-10">
      <!-- 1. Stats Counter Grid -->
      <section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        <!-- Watch Time -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white/5 border border-white/10 hover:border-netflix/40 transition group">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400">{{ $t('dashboard.stream_time') }}</span>
            <div class="w-8 h-8 rounded-xl bg-red-500/10 text-netflix flex items-center justify-center text-sm group-hover:scale-110 transition-transform">
              <i class="fas fa-clock"></i>
            </div>
          </div>
          <div class="text-xl sm:text-2xl font-black text-white tracking-tight">
            {{ stats.watchTimeHours || 0 }} <span class="text-xs font-normal text-gray-400">{{ $t('dashboard.hrs') }}</span>
          </div>
          <p class="text-[11px] text-gray-500 mt-1">{{ $t('dashboard.total_time_desc') }}</p>
        </div>

        <!-- Movies Watched -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white/5 border border-white/10 hover:border-blue-500/40 transition group">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400">{{ $t('dashboard.movies_watched') }}</span>
            <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">
              <i class="fas fa-film"></i>
            </div>
          </div>
          <div class="text-xl sm:text-2xl font-black text-white tracking-tight">
            {{ stats.moviesWatchedCount || 0 }}
          </div>
          <p class="text-[11px] text-gray-500 mt-1">{{ $t('dashboard.full_films_desc') }}</p>
        </div>

        <!-- Episodes Watched -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white/5 border border-white/10 hover:border-purple-500/40 transition group">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400">{{ $t('dashboard.series_anime') }}</span>
            <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">
              <i class="fas fa-tv"></i>
            </div>
          </div>
          <div class="text-xl sm:text-2xl font-black text-white tracking-tight">
            {{ stats.seriesWatchedCount || 0 }}
          </div>
          <p class="text-[11px] text-gray-500 mt-1">{{ $t('dashboard.episodes_streamed') }}</p>
        </div>

        <!-- Watchlist Count -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white/5 border border-white/10 hover:border-emerald-500/40 transition group">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400">{{ $t('dashboard.watchlist') }}</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">
              <i class="fas fa-bookmark"></i>
            </div>
          </div>
          <div class="text-xl sm:text-2xl font-black text-white tracking-tight">
            {{ stats.watchlistCount || 0 }}
          </div>
          <p class="text-[11px] text-gray-500 mt-1">{{ $t('dashboard.saved_later') }}</p>
        </div>

        <!-- Requests Count -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white/5 border border-white/10 hover:border-amber-500/40 transition group col-span-2 sm:col-span-1">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400">{{ $t('dashboard.requests') }}</span>
            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-sm group-hover:scale-110 transition-transform">
              <i class="fas fa-paper-plane"></i>
            </div>
          </div>
          <div class="text-xl sm:text-2xl font-black text-white tracking-tight">
            {{ stats.requestsCount || 0 }}
          </div>
          <p class="text-[11px] text-gray-500 mt-1">{{ $t('dashboard.submitted_titles') }}</p>
        </div>
      </section>

      <!-- 2. Continue Watching Row -->
      <section class="space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-2.5 h-2.5 rounded-full bg-netflix animate-pulse"></div>
            <h2 class="text-lg sm:text-xl font-extrabold text-white tracking-tight">{{ $t('dashboard.continue_watching') }}</h2>
          </div>
          <Link 
            v-if="continueWatching.length" 
            href="/dashboard/history" 
            class="text-xs text-gray-400 hover:text-netflix font-semibold transition flex items-center gap-1.5"
          >
            <span>{{ $t('dashboard.full_history') }}</span>
            <i class="fas fa-chevron-right text-[10px]"></i>
          </Link>
        </div>

        <!-- Continue Items Grid -->
        <div v-if="continueWatching.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div 
            v-for="item in continueWatching" 
            :key="item.id"
            class="group relative rounded-2xl overflow-hidden bg-gray-900/80 border border-white/10 hover:border-netflix/50 transition-all duration-300 hover:shadow-xl hover:shadow-black/70 flex flex-col"
          >
            <!-- Image with play overlay and progress bar -->
            <div class="relative aspect-video w-full overflow-hidden bg-gray-800">
              <img 
                :src="item.backdrop || item.poster" 
                :alt="item.title"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent"></div>

              <!-- Center Play Icon -->
              <Link 
                :href="item.playUrl" 
                class="absolute inset-0 flex items-center justify-center group/play"
              >
                <div class="w-12 h-12 rounded-full bg-netflix text-white flex items-center justify-center shadow-xl shadow-red-600/50 transform scale-90 group-hover:scale-110 transition-all duration-200">
                  <i class="fas fa-play text-sm ml-0.5"></i>
                </div>
              </Link>

              <!-- Progress Percentage Badge -->
              <div class="absolute top-2.5 right-2.5 z-10">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-black/70 backdrop-blur-md text-white border border-white/10 flex items-center gap-1">
                  <span>{{ item.progress_percent }}%</span>
                </span>
              </div>

              <!-- Media Type Badge -->
              <div class="absolute top-2.5 left-2.5 z-10">
                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-netflix/90 text-white">
                  {{ item.media_type === 'tv' ? $t('home.tv_show') : $t('home.movie') }}
                </span>
              </div>

              <!-- Persistent Progress Bar on Bottom of Image -->
              <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-white/20 z-10">
                <div 
                  class="h-full bg-netflix rounded-r transition-all duration-500" 
                  :style="{ width: item.progress_percent + '%' }"
                ></div>
              </div>
            </div>

            <!-- Details -->
            <div class="p-4 flex-1 flex flex-col justify-between">
              <div>
                <Link :href="item.playUrl" class="block">
                  <h3 class="font-bold text-sm text-white group-hover:text-netflix transition-colors line-clamp-1">
                    {{ item.title }}
                  </h3>
                </Link>
                <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ item.subtitle }}</p>
              </div>

              <div class="mt-4 pt-3 border-t border-white/5 flex items-center justify-between text-xs text-gray-400">
                <span class="flex items-center gap-1 text-[11px]">
                  <i class="far fa-clock text-gray-500"></i>
                  {{ item.last_watched_at }}
                </span>

                <div class="flex items-center gap-2">
                  <Link 
                    :href="item.playUrl"
                    class="px-2.5 py-1 rounded-lg bg-netflix/20 hover:bg-netflix text-netflix hover:text-white font-bold text-xs transition flex items-center gap-1.5"
                  >
                    <i class="fas fa-play text-[10px]"></i>
                    <span>{{ $t('dashboard.resume') }}</span>
                  </Link>

                  <button 
                    @click="removeItem(item.id)" 
                    :title="$t('dashboard.remove_from_history')"
                    class="w-7 h-7 rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-red-400 flex items-center justify-center text-xs transition"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty Continue Watching State -->
        <div 
          v-else 
          class="p-8 sm:p-10 rounded-2xl bg-white/5 border border-dashed border-white/10 text-center space-y-3"
        >
          <div class="w-12 h-12 rounded-2xl bg-white/5 text-gray-500 flex items-center justify-center mx-auto text-lg">
            <i class="fas fa-film"></i>
          </div>
          <div>
            <h3 class="text-sm sm:text-base font-bold text-white">{{ $t('dashboard.no_in_progress') }}</h3>
            <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
              {{ $t('dashboard.no_in_progress_desc') }}
            </p>
          </div>
          <div class="pt-2">
            <Link 
              href="/trending" 
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg"
            >
              <i class="fas fa-fire"></i>
              <span>{{ $t('dashboard.explore_trending') }}</span>
            </Link>
          </div>
        </div>
      </section>

      <!-- 3. Two-Column Row: Recent Activity & VIP Perks / Quick Actions -->
      <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Recent Activity Timeline (2 Cols) -->
        <div class="lg:col-span-2 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-extrabold text-white flex items-center gap-2">
              <i class="fas fa-history text-netflix text-sm"></i>
              <span>{{ $t('dashboard.recent_activity') }}</span>
            </h2>
            <Link 
              href="/dashboard/history" 
              class="text-xs text-gray-400 hover:text-white font-medium transition"
            >
              {{ $t('dashboard.view_all') }}
            </Link>
          </div>

          <div v-if="recentActivity.length > 0" class="space-y-2.5">
            <div 
              v-for="act in recentActivity" 
              :key="'act-' + act.id"
              class="p-3 sm:p-4 rounded-xl bg-white/5 border border-white/5 hover:border-white/15 transition flex items-center justify-between gap-4"
            >
              <div class="flex items-center gap-3.5 min-w-0">
                <img 
                  :src="act.poster || act.backdrop" 
                  :alt="act.title" 
                  class="w-10 h-14 object-cover rounded-lg flex-shrink-0 shadow"
                />
                <div class="min-w-0">
                  <Link :href="act.playUrl" class="font-bold text-xs sm:text-sm text-white hover:text-netflix transition truncate block">
                    {{ act.title }}
                  </Link>
                  <p class="text-[11px] text-gray-400 truncate mt-0.5">{{ act.subtitle }}</p>
                  <div class="flex items-center gap-2 mt-1">
                    <span 
                      :class="act.completed ? 'bg-green-500/20 text-green-400 border-green-500/30' : 'bg-amber-500/20 text-amber-400 border-amber-500/30'"
                      class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded border"
                    >
                      {{ act.completed ? $t('dashboard.completed') : $t('dashboard.watched_percent', { percent: act.progress_percent }) }}
                    </span>
                    <span class="text-[10px] text-gray-500">{{ act.last_watched_at }}</span>
                  </div>
                </div>
              </div>

              <Link 
                :href="act.playUrl" 
                class="flex-shrink-0 w-8 h-8 rounded-lg bg-white/10 hover:bg-netflix text-white flex items-center justify-center text-xs transition"
                :title="$t('common.play')"
              >
                <i class="fas fa-play text-[10px] ml-0.5"></i>
              </Link>
            </div>
          </div>

          <div v-else class="p-6 rounded-xl bg-white/5 text-center text-xs text-gray-400">
            {{ $t('dashboard.no_activity') }}
          </div>
        </div>

        <!-- Right: VIP Status & Quick Actions -->
        <div class="space-y-4">
          <h2 class="text-base sm:text-lg font-extrabold text-white flex items-center gap-2">
            <i class="fas fa-gem text-netflix text-sm"></i>
            <span>{{ $t('dashboard.account_status') }}</span>
          </h2>

          <div class="p-5 rounded-2xl bg-gradient-to-b from-white/10 via-white/5 to-transparent border border-white/10 space-y-4 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-white/10">
              <span class="text-xs text-gray-400 font-medium">{{ $t('dashboard.subscription') }}</span>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-netflix text-white uppercase tracking-wider">
                {{ $t('dashboard.vip_premium') }}
              </span>
            </div>

            <div class="space-y-2.5 text-xs">
              <div class="flex items-center justify-between text-gray-300">
                <span class="flex items-center gap-2">
                  <i class="fas fa-check text-green-400 text-xs"></i>
                  {{ $t('dashboard.resolution') }}
                </span>
                <span class="font-bold text-white">{{ $page.props.auth?.user?.preferred_quality || '4K Ultra HD' }}</span>
              </div>

              <div class="flex items-center justify-between text-gray-300">
                <span class="flex items-center gap-2">
                  <i class="fas fa-check text-green-400 text-xs"></i>
                  {{ $t('dashboard.audio_subs') }}
                </span>
                <span class="font-bold text-white uppercase">{{ $page.props.auth?.user?.preferred_language || 'EN' }}</span>
              </div>

              <div class="flex items-center justify-between text-gray-300">
                <span class="flex items-center gap-2">
                  <i class="fas fa-check text-green-400 text-xs"></i>
                  {{ $t('dashboard.ad_free') }}
                </span>
                <span class="font-bold text-green-400">{{ $t('dashboard.active') }}</span>
              </div>

              <div class="flex items-center justify-between text-gray-300">
                <span class="flex items-center gap-2">
                  <i class="fas fa-check text-green-400 text-xs"></i>
                  {{ $t('dashboard.autoplay_next') }}
                </span>
                <span class="font-bold text-white">
                  {{ $page.props.auth?.user?.autoplay_next ? $t('dashboard.enabled') : $t('dashboard.disabled') }}
                </span>
              </div>
            </div>

            <div class="pt-3 border-t border-white/10 flex flex-col gap-2">
              <Link 
                href="/dashboard/requests" 
                class="w-full py-2.5 px-3 rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-bold text-center transition flex items-center justify-center gap-2"
              >
                <i class="fas fa-plus text-netflix"></i>
                <span>{{ $t('dashboard.request_movie_show') }}</span>
              </Link>

              <Link 
                href="/dashboard/settings" 
                class="w-full py-2.5 px-3 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold text-center transition shadow-lg flex items-center justify-center gap-2"
              >
                <i class="fas fa-sliders-h"></i>
                <span>{{ $t('dashboard.configure_prefs') }}</span>
              </Link>
            </div>
          </div>
        </div>
      </section>

      <!-- 4. Recommended For You Carousel/Grid -->
      <section v-if="recommendations && recommendations.length" class="space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <i class="fas fa-sparkles text-amber-400"></i>
            <h2 class="text-lg sm:text-xl font-extrabold text-white tracking-tight">{{ $t('dashboard.recommended_for_you') }}</h2>
          </div>
          <Link 
            href="/trending" 
            class="text-xs text-gray-400 hover:text-netflix font-semibold transition flex items-center gap-1.5"
          >
            <span>{{ $t('dashboard.see_all') }}</span>
            <i class="fas fa-chevron-right text-[10px]"></i>
          </Link>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <MediaCard 
            v-for="rec in recommendations" 
            :key="'rec-' + rec.id"
            :item="rec"
            mediaType="movie"
          />
        </div>
      </section>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import DashboardLayout from './Layout.vue';
import MediaCard from '@/Components/MediaCard.vue';
import { useI18n } from '@/Composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      watchTimeHours: 0,
      moviesWatchedCount: 0,
      seriesWatchedCount: 0,
      watchlistCount: 0,
      requestsCount: 0,
    }),
  },
  continueWatching: {
    type: Array,
    default: () => [],
  },
  recentActivity: {
    type: Array,
    default: () => [],
  },
  recommendations: {
    type: Array,
    default: () => [],
  },
});

const removeItem = (id) => {
  if (confirm(t('dashboard.confirm_remove_history'))) {
    router.delete(`/dashboard/history/${id}`, {
      preserveScroll: true,
    });
  }
};
</script>
