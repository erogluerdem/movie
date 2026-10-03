<template>
  <Teleport to="body">
    <div 
      v-if="isOpen" 
      class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
    >
      <!-- Dark Backdrop with Blur -->
      <div 
        @click="closeModal" 
        class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity duration-300"
      ></div>

      <!-- Modal Container -->
      <div 
        class="relative w-full max-w-xl bg-zinc-950 border border-white/10 rounded-2xl overflow-hidden shadow-2xl shadow-black z-10 animate-scale-up"
      >
        <!-- Modal Top Bar -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-white/5 bg-zinc-900/40">
          <!-- Type Filter Tabs -->
          <div class="flex items-center gap-1 bg-black/40 p-1 rounded-xl border border-white/5">
            <button
              type="button"
              @click="setFilter('any')"
              :class="[
                'px-3 py-1 rounded-lg text-xs font-semibold transition cursor-pointer',
                filterType === 'any' ? 'bg-netflix text-white shadow-sm' : 'text-zinc-400 hover:text-white'
              ]"
            >
              {{ $t('search.all') }}
            </button>
            <button
              type="button"
              @click="setFilter('movie')"
              :class="[
                'px-3 py-1 rounded-lg text-xs font-semibold transition cursor-pointer',
                filterType === 'movie' ? 'bg-netflix text-white shadow-sm' : 'text-zinc-400 hover:text-white'
              ]"
            >
              {{ $t('home.movie') }}
            </button>
            <button
              type="button"
              @click="setFilter('tv')"
              :class="[
                'px-3 py-1 rounded-lg text-xs font-semibold transition cursor-pointer',
                filterType === 'tv' ? 'bg-netflix text-white shadow-sm' : 'text-zinc-400 hover:text-white'
              ]"
            >
              {{ $t('home.tv_show') }}
            </button>
          </div>

          <!-- Close Button -->
          <button 
            @click="closeModal" 
            class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/15 text-zinc-400 hover:text-white flex items-center justify-center border border-white/5 transition cursor-pointer"
            :aria-label="$t('common.close')"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <!-- 1. Elegant Loading State -->
        <div v-if="loading" class="py-20 px-8 text-center flex flex-col items-center justify-center space-y-4">
          <div class="relative w-12 h-12">
            <div class="w-12 h-12 rounded-full border-2 border-white/10 border-t-netflix animate-spin"></div>
          </div>
          <div class="space-y-1">
            <h3 class="text-sm font-bold text-white tracking-wide">{{ $t('home.surprise_me_preparing') }}</h3>
            <p class="text-xs text-zinc-400">{{ $t('home.surprise_me_scanning') }}</p>
          </div>
        </div>

        <!-- 2. Result State -->
        <div v-else-if="pick" class="flex flex-col">
          <!-- Backdrop Header Preview -->
          <div class="relative h-44 sm:h-52 w-full overflow-hidden bg-black">
            <img 
              :src="pick.backdrop_url || pick.poster_url" 
              :alt="pick.title" 
              class="w-full h-full object-cover"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/60 to-transparent"></div>

            <!-- Top Badges -->
            <div class="absolute top-3 left-4 flex items-center gap-2">
              <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-netflix text-white shadow-sm">
                {{ $t('home.surprise_me_recommended') }}
              </span>
              <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/20 backdrop-blur-xs">
                {{ $t('home.match') }}
              </span>
            </div>
          </div>

          <!-- Card Content Body -->
          <div class="px-6 pb-6 pt-1 relative z-10 flex flex-col sm:flex-row gap-5 items-start">
            <!-- Poster Thumbnail -->
            <div class="w-24 sm:w-28 aspect-[2/3] rounded-xl overflow-hidden shadow-xl border border-white/10 bg-zinc-900 flex-shrink-0 -mt-14 sm:-mt-16 mx-auto sm:mx-0">
              <img 
                :src="pick.poster_url || '/images/placeholders/poster.png'" 
                :alt="pick.title" 
                class="w-full h-full object-cover"
              />
            </div>

            <!-- Content Details -->
            <div class="flex-1 space-y-2.5 min-w-0 text-center sm:text-left">
              <div>
                <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap text-xs text-zinc-400 mb-1">
                  <span class="font-bold text-white uppercase text-[10px] px-2 py-0.5 rounded bg-white/10">
                    {{ pick.type === 'tv' ? $t('home.tv_show') : $t('home.movie') }}
                  </span>
                  <span>{{ pick.release_year }}</span>
                  <span class="text-zinc-600">•</span>
                  <span class="text-amber-400 font-bold flex items-center gap-1">
                    <span>★</span>
                    <span>{{ pick.vote_average ? pick.vote_average.toFixed(1) : '7.8' }}</span>
                  </span>
                  <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-white/10 text-zinc-300">
                    HD
                  </span>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight leading-snug">
                  {{ pick.title }}
                </h2>
              </div>

              <!-- Genres -->
              <div v-if="pick.genres && pick.genres.length" class="flex flex-wrap justify-center sm:justify-start gap-1.5">
                <span 
                  v-for="g in pick.genres" 
                  :key="g"
                  class="px-2 py-0.5 rounded text-[10px] font-medium bg-white/5 border border-white/5 text-zinc-300"
                >
                  {{ g }}
                </span>
              </div>

              <!-- Overview Text -->
              <p class="text-xs text-zinc-300 line-clamp-3 leading-relaxed">
                {{ pick.overview || 'Bu içerik MovieEra arşivinde en çok beğenilen yapımlar arasında yer almaktadır. Hemen izlemeye başlayabilirsiniz.' }}
              </p>

              <!-- Actions -->
              <div class="pt-2 flex items-center justify-center sm:justify-start gap-3 flex-wrap">
                <Link 
                  :href="pick.watch_url" 
                  class="px-5 py-2.5 rounded-xl bg-netflix hover:bg-netflix-hover text-white font-semibold text-xs shadow-md shadow-netflix/20 flex items-center gap-2 transition cursor-pointer"
                >
                  <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8 5v14l11-7z"/>
                  </svg>
                  <span>{{ $t('home.watch_now') }}</span>
                </Link>

                <button 
                  type="button"
                  @click="fetchPick" 
                  class="px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-zinc-200 hover:text-white font-medium text-xs border border-white/10 flex items-center gap-2 transition cursor-pointer"
                  :title="$t('home.surprise_me_pick_another')"
                >
                  <svg class="w-3.5 h-3.5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                    <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                    <path d="M16 21h5v-5"/>
                  </svg>
                  <span>{{ $t('home.surprise_me_pick_another') }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close']);

const loading = ref(false);
const pick = ref(null);
const filterType = ref('any'); // 'any', 'movie', 'tv'

const closeModal = () => {
  emit('close');
};

const setFilter = (type) => {
  filterType.value = type;
  fetchPick();
};

const fetchPick = async () => {
  loading.value = true;
  try {
    const res = await fetch(`/api/random-pick?type=${filterType.value}`);
    const data = await res.json();
    if (data.status === 'success' && data.data) {
      setTimeout(() => {
        pick.value = data.data;
        loading.value = false;
      }, 300);
    } else {
      loading.value = false;
    }
  } catch (e) {
    loading.value = false;
  }
};

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    fetchPick();
  }
});
</script>
