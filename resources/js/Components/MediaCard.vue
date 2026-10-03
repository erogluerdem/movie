<template>
  <div class="group relative flex flex-col rounded-xl overflow-hidden bg-gray-900/60 border border-white/5 hover:border-white/20 transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-black/80">
    <!-- Poster Image & Overlays -->
    <Link :href="itemUrl" class="relative aspect-[2/3] w-full overflow-hidden bg-gray-800 block">
      <img 
        :src="posterUrl" 
        :alt="item.title"
        loading="lazy"
        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
        @error="handleImgError"
      />

      <!-- Dark Gradient Overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 group-hover:opacity-80 transition-opacity"></div>

      <!-- Play Button Overlay -->
      <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <div class="w-12 h-12 rounded-full bg-netflix text-white flex items-center justify-center shadow-lg shadow-red-600/50 transform scale-75 group-hover:scale-100 transition-transform">
          <i class="fas fa-play text-sm ml-0.5"></i>
        </div>
      </div>

      <!-- Top Badges (Quality & Rating) -->
      <div class="absolute top-2 left-2 flex flex-col gap-1 z-10">
        <span class="bg-black/60 backdrop-blur-md text-[10px] font-black uppercase tracking-wider text-white px-1.5 py-0.5 rounded border border-white/10">
          HD
        </span>
      </div>

      <div v-if="item.vote_average" class="absolute top-2 right-2 z-10">
        <span class="bg-black/70 backdrop-blur-md text-amber-400 text-xs font-bold px-2 py-0.5 rounded-full border border-amber-500/30 flex items-center gap-1 shadow">
          <i class="fas fa-star text-[9px]"></i>
          <span>{{ item.vote_average }}</span>
        </span>
      </div>

      <!-- Quick Watchlist Bookmark Button -->
      <button 
        @click.prevent.stop="toggleWatchlist"
        class="absolute bottom-2 right-2 z-10 w-8 h-8 rounded-full bg-black/60 hover:bg-netflix text-white flex items-center justify-center backdrop-blur-md border border-white/10 opacity-0 group-hover:opacity-100 transition-all duration-200"
        :title="isSaved ? $t('common.remove_from_list') : $t('common.add_to_list')"
      >
        <i :class="[isSaved ? 'fas fa-bookmark text-netflix hover:text-white' : 'far fa-bookmark text-xs']"></i>
      </button>
    </Link>

    <!-- Content Info -->
    <div class="p-3 flex flex-col flex-1 justify-between bg-gradient-to-b from-gray-900/40 to-gray-900/90">
      <Link :href="itemUrl" class="block">
        <h3 class="font-semibold text-sm text-white group-hover:text-netflix transition-colors line-clamp-1 leading-snug">
          {{ item.title }}
        </h3>
      </Link>

      <div class="flex items-center justify-between text-xs text-gray-400 mt-2">
        <span class="font-medium text-gray-300">
          {{ releaseYear }}
        </span>
        <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-white/5 text-gray-400 border border-white/5">
          {{ isTvShow ? (item.is_anime ? $t('home.anime') : $t('home.tv_show')) : $t('home.movie') }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  mediaType: {
    type: String,
    default: null, // 'movie' or 'tv'
  }
});

const isSaved = ref(false);
const imgFailed = ref(false);

const isTvShow = computed(() => {
  if (props.mediaType) return props.mediaType === 'tv';
  return Boolean(props.item.first_air_date || props.item.number_of_seasons);
});

const itemUrl = computed(() => {
  return isTvShow.value ? `/tv-show/${props.item.slug}` : `/movie/${props.item.slug}`;
});

const posterUrl = computed(() => {
  if (imgFailed.value) {
    return 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=500&q=80';
  }
  const raw = props.item.poster_url || props.item.poster_path;
  if (!raw) {
    return 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=500&q=80';
  }
  if (raw.startsWith('http')) {
    return raw;
  }
  return `https://image.tmdb.org/t/p/w500/${raw.replace(/^\//, '')}`;
});


const releaseYear = computed(() => {
  const d = props.item.release_date || props.item.first_air_date;
  return d ? d.substring(0, 4) : '2025';
});

const handleImgError = () => {
  imgFailed.value = true;
};

const toggleWatchlist = async () => {
  try {
    const type = isTvShow.value ? 'tv' : 'movie';
    const res = await axios.post('/api/watchlist/toggle', {
      media_type: type,
      media_id: props.item.id
    });
    isSaved.value = res.data.in_watchlist;
  } catch (e) {
    console.error(e);
  }
};
</script>
