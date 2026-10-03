<template>
  <DashboardLayout>
    <div class="space-y-6">
      <!-- Top Action Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl bg-white/5 border border-white/10">
        <!-- Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
          <button 
            @click="activeFilter = 'all'"
            :class="[activeFilter === 'all' ? 'bg-netflix text-white shadow-md' : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10']"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0"
          >
            <span>{{ $t('dashboard.all_tab') }}</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/40">{{ history.total || historyItems.length }}</span>
          </button>

          <button 
            @click="activeFilter = 'in_progress'"
            :class="[activeFilter === 'in_progress' ? 'bg-netflix text-white shadow-md' : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10']"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0"
          >
            <i class="fas fa-play text-[10px]"></i>
            <span>{{ $t('dashboard.in_progress_tab') }}</span>
          </button>

          <button 
            @click="activeFilter = 'completed'"
            :class="[activeFilter === 'completed' ? 'bg-netflix text-white shadow-md' : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10']"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0"
          >
            <i class="fas fa-check-circle text-[10px]"></i>
            <span>{{ $t('dashboard.completed_tab') }}</span>
          </button>

          <button 
            @click="activeFilter = 'movie'"
            :class="[activeFilter === 'movie' ? 'bg-netflix text-white shadow-md' : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10']"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0"
          >
            <i class="fas fa-film text-[10px]"></i>
            <span>{{ $t('watchlist.movies') }}</span>
          </button>

          <button 
            @click="activeFilter = 'tv'"
            :class="[activeFilter === 'tv' ? 'bg-netflix text-white shadow-md' : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10']"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0"
          >
            <i class="fas fa-tv text-[10px]"></i>
            <span>{{ $t('watchlist.tv_shows') }}</span>
          </button>
        </div>

        <!-- Right Side: Search & Clear History -->
        <div class="flex items-center gap-3">
          <!-- Search Input -->
          <div class="relative flex-1 sm:w-48">
            <input 
              type="text" 
              v-model="searchQuery" 
              :placeholder="$t('dashboard.filter_history')" 
              class="w-full h-9 bg-black/50 border border-white/10 rounded-xl pl-8 pr-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
            />
            <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-[11px] text-gray-500"></i>
          </div>

          <!-- Clear History Button -->
          <button 
            v-if="historyItems.length > 0"
            @click="confirmClearAll"
            class="h-9 px-3.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-bold transition border border-red-500/20 flex items-center gap-1.5 flex-shrink-0"
          >
            <i class="fas fa-trash-alt"></i>
            <span class="hidden sm:inline">{{ $t('dashboard.clear_history') }}</span>
          </button>
        </div>
      </div>

      <!-- History List -->
      <div v-if="filteredList.length > 0" class="space-y-3">
        <div 
          v-for="item in filteredList" 
          :key="'hist-' + item.id"
          class="group p-4 rounded-2xl bg-white/5 border border-white/10 hover:border-white/20 transition-all duration-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
        >
          <!-- Left: Media Thumbnail + Meta -->
          <div class="flex items-center gap-4 min-w-0">
            <!-- Thumbnail with overlay & progress bar -->
            <div class="relative w-24 sm:w-32 aspect-video rounded-xl overflow-hidden bg-gray-800 flex-shrink-0 shadow-md">
              <img 
                :src="item.backdrop || item.poster" 
                :alt="item.title"
                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>

              <!-- Quick Play Overlay -->
              <Link 
                :href="item.playUrl" 
                class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
              >
                <div class="w-8 h-8 rounded-full bg-netflix text-white flex items-center justify-center shadow">
                  <i class="fas fa-play text-[10px] ml-0.5"></i>
                </div>
              </Link>

              <!-- Progress bar at bottom of thumb -->
              <div class="absolute bottom-0 left-0 right-0 h-1 bg-white/20">
                <div 
                  class="h-full bg-netflix" 
                  :style="{ width: item.progress_percent + '%' }"
                ></div>
              </div>
            </div>

            <!-- Title & Info -->
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <span 
                  :class="item.media_type === 'tv' ? 'bg-purple-500/20 text-purple-400 border-purple-500/30' : 'bg-blue-500/20 text-blue-400 border-blue-500/30'"
                  class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded border"
                >
                  {{ item.media_type === 'tv' ? $t('home.tv_show') : $t('home.movie') }}
                </span>
                <span 
                  :class="item.completed ? 'bg-green-500/20 text-green-400 border-green-500/30' : 'bg-amber-500/20 text-amber-400 border-amber-500/30'"
                  class="text-[9px] font-bold px-1.5 py-0.2 rounded border"
                >
                  {{ item.completed ? $t('dashboard.completed') : $t('dashboard.watched_percent', { percent: item.progress_percent }) }}
                </span>
              </div>

              <Link 
                :href="item.playUrl"
                class="font-bold text-sm sm:text-base text-white group-hover:text-netflix transition-colors truncate block mt-1"
              >
                {{ item.title }}
              </Link>

              <div class="flex items-center gap-3 text-xs text-gray-400 mt-1">
                <span>{{ item.subtitle }}</span>
                <span>•</span>
                <span class="flex items-center gap-1 text-[11px] text-gray-500">
                  <i class="far fa-clock"></i>
                  {{ item.last_watched_at }}
                </span>
              </div>
            </div>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center justify-end gap-2 sm:flex-shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-white/5">
            <Link 
              :href="item.playUrl" 
              class="px-4 py-2 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow flex items-center gap-2"
            >
              <i class="fas fa-play text-[10px]"></i>
              <span>{{ item.completed ? $t('common.play') : $t('dashboard.resume') }}</span>
            </Link>

            <button 
              @click="removeItem(item.id)" 
              class="w-8 h-8 rounded-xl bg-white/5 hover:bg-red-500/20 text-gray-400 hover:text-red-400 flex items-center justify-center text-xs transition border border-white/5"
              :title="$t('dashboard.remove_from_history')"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>

        <!-- Pagination (if more pages) -->
        <div v-if="history.links && history.links.length > 3" class="flex justify-center gap-1 pt-6">
          <Link 
            v-for="(link, i) in history.links" 
            :key="i"
            :href="link.url || '#'"
            v-html="link.label"
            :class="[
              link.active ? 'bg-netflix text-white font-bold' : 'bg-white/5 text-gray-400 hover:text-white',
              !link.url ? 'opacity-40 pointer-events-none' : ''
            ]"
            class="px-3 py-1.5 rounded-lg text-xs transition"
          />
        </div>
      </div>

      <!-- Empty State -->
      <div 
        v-else 
        class="py-20 px-4 rounded-3xl bg-white/5 border border-white/10 text-center space-y-4 max-w-lg mx-auto"
      >
        <div class="w-16 h-16 rounded-3xl bg-white/10 text-gray-500 flex items-center justify-center mx-auto text-2xl">
          <i class="fas fa-history"></i>
        </div>
        <div>
          <h3 class="text-lg font-bold text-white">{{ $t('dashboard.no_history_title') }}</h3>
          <p class="text-xs text-gray-400 mt-1.5 max-w-sm mx-auto leading-relaxed">
            <span v-if="searchQuery">{{ $t('watchlist.no_search_match', { query: searchQuery }) }}</span>
            <span v-else>{{ $t('dashboard.no_history_desc') }}</span>
          </p>
        </div>
        <div class="pt-2">
          <Link 
            href="/trending" 
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg"
          >
            <i class="fas fa-play"></i>
            <span>{{ $t('dashboard.start_streaming') }}</span>
          </Link>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import DashboardLayout from './Layout.vue';
import { useI18n } from '@/Composables/useI18n';

const { t } = useI18n();

const props = defineProps({
  history: {
    type: Object,
    default: () => ({ data: [], links: [], total: 0 }),
  },
});

const historyItems = computed(() => {
  return props.history?.data || [];
});

const activeFilter = ref('all'); // 'all', 'in_progress', 'completed', 'movie', 'tv'
const searchQuery = ref('');

const filteredList = computed(() => {
  let list = historyItems.value;

  if (activeFilter.value === 'in_progress') {
    list = list.filter(item => !item.completed);
  } else if (activeFilter.value === 'completed') {
    list = list.filter(item => item.completed);
  } else if (activeFilter.value === 'movie') {
    list = list.filter(item => item.media_type === 'movie');
  } else if (activeFilter.value === 'tv') {
    list = list.filter(item => item.media_type === 'tv');
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(item => item.title && item.title.toLowerCase().includes(q));
  }

  return list;
});

const removeItem = (id) => {
  if (confirm(t('dashboard.confirm_remove_history'))) {
    router.delete(`/dashboard/history/${id}`, {
      preserveScroll: true,
    });
  }
};

const confirmClearAll = () => {
  if (confirm(t('dashboard.confirm_clear_history'))) {
    router.post('/dashboard/history/clear', {}, {
      preserveScroll: true,
    });
  }
};
</script>
