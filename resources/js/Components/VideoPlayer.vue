<template>
  <div class="w-full flex flex-col gap-4">
    <!-- Cinema Backdrop Dimmer when active -->
    <div 
      v-if="cinemaMode" 
      class="fixed inset-0 bg-black/95 z-40 transition-opacity duration-300 pointer-events-none"
    ></div>

    <!-- Main Player Frame Container -->
    <div 
      :class="[cinemaMode ? 'relative z-50 shadow-2xl ring-2 ring-netflix' : 'relative z-10']"
      class="w-full rounded-2xl overflow-hidden bg-black border border-white/10 shadow-2xl aspect-video relative flex items-center justify-center group"
    >
      <!-- Loading Spinner Indicator -->
      <div 
        v-if="isLoading" 
        class="absolute inset-0 bg-black/90 flex flex-col items-center justify-center gap-3 z-20"
      >
        <div class="w-12 h-12 border-4 border-netflix border-t-transparent rounded-full animate-spin"></div>
        <span class="text-sm font-medium text-gray-300">{{ $t('movies.connecting_server') }}</span>
      </div>

      <!-- Iframe Video Embed -->
      <iframe
        v-if="currentSource && currentSource.type === 'embed'"
        :src="currentSource.url"
        class="w-full h-full border-0 absolute inset-0"
        allowfullscreen
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        @load="handleIframeLoaded"
      ></iframe>

      <!-- Fallback / Custom / Torrent message if non-embed active -->
      <div 
        v-else-if="currentSource && currentSource.type === 'torrent'"
        class="p-8 text-center flex flex-col items-center justify-center gap-4 z-10 max-w-md"
      >
        <div class="w-16 h-16 rounded-full bg-netflix/20 text-netflix flex items-center justify-center text-2xl">
          <i class="fas fa-magnet"></i>
        </div>
        <div>
          <h4 class="text-lg font-bold text-white">{{ currentSource.name }}</h4>
          <p class="text-xs text-gray-400 mt-1">{{ $t('movies.magnet_ready') }}</p>
        </div>
        <a 
          :href="currentSource.url" 
          class="px-6 py-2.5 bg-netflix hover:bg-red-700 text-white rounded-lg font-bold text-sm shadow-lg flex items-center gap-2 transition"
        >
          <i class="fas fa-download"></i>
          <span>{{ $t('movies.open_magnet', { server: currentSource.server || 'Download' }) }}</span>
        </a>
      </div>

      <!-- Empty / Fallback state -->
      <div v-else class="text-center p-8 text-gray-400">
        <i class="fas fa-film text-4xl mb-3 text-gray-600"></i>
        <p>{{ $t('movies.no_stream_source') }}</p>
      </div>

      <!-- Top Overlay Controls Bar (Cinema Mode & Fullscreen) -->
      <div class="absolute top-4 right-4 z-30 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
        <button 
          @click="toggleCinemaMode"
          :class="[cinemaMode ? 'bg-netflix text-white' : 'bg-black/70 hover:bg-black text-gray-300']"
          class="px-3 py-1.5 rounded-lg text-xs font-semibold backdrop-blur border border-white/10 flex items-center gap-1.5 transition"
          :title="$t('movies.cinema_mode')"
        >
          <i class="fas fa-lightbulb"></i>
          <span class="hidden sm:inline">{{ cinemaMode ? $t('movies.lights_on') : $t('movies.lights_off') }}</span>
        </button>
      </div>
    </div>

    <!-- Server Selector Bar & Source Pills -->
    <div class="glass-card rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <!-- Left: Server Buttons -->
      <div class="flex items-center flex-wrap gap-2">
        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mr-1 flex items-center gap-1.5">
          <i class="fas fa-server text-netflix"></i>
          <span>{{ $t('movies.servers') }}</span>
        </span>

        <button 
          v-for="(source, index) in sources" 
          :key="index"
          @click="selectSource(source, index)"
          :class="[
            activeSourceIndex === index 
              ? 'bg-netflix text-white shadow-lg shadow-red-900/30' 
              : 'bg-white/5 hover:bg-white/10 text-gray-300 border border-white/5'
          ]"
          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 flex items-center gap-1.5"
        >
          <i :class="[source.type === 'torrent' ? 'fas fa-magnet' : 'fas fa-play', 'text-[10px]']"></i>
          <span>{{ source.server || source.name || `Server ${index + 1}` }}</span>
        </button>

        <!-- Trailer Button -->
        <button 
          v-if="trailerUrl"
          @click="showTrailer = !showTrailer"
          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 border border-amber-500/30 transition flex items-center gap-1.5"
        >
          <i class="fab fa-youtube"></i>
          <span>{{ $t('home.trailer') }}</span>
        </button>
      </div>

      <!-- Right: Tips -->
      <div class="text-[11px] text-gray-400 flex items-center gap-2">
        <i class="fas fa-info-circle text-netflix"></i>
        <span>{{ $t('movies.server_buffer_tip') }}</span>
      </div>
    </div>

    <!-- Trailer Modal -->
    <div 
      v-if="showTrailer" 
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md"
    >
      <div class="relative w-full max-w-4xl bg-gray-900 rounded-2xl overflow-hidden border border-white/10 shadow-2xl">
        <div class="flex items-center justify-between p-4 border-b border-white/10">
          <h3 class="font-bold text-white text-base flex items-center gap-2">
            <i class="fab fa-youtube text-red-500"></i>
            <span>{{ $t('movies.official_trailer') }}</span>
          </h3>
          <button @click="showTrailer = false" class="text-gray-400 hover:text-white p-1">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>
        <div class="aspect-video w-full bg-black">
          <iframe 
            :src="trailerUrl" 
            class="w-full h-full border-0" 
            allowfullscreen
            allow="autoplay; encrypted-media"
          ></iframe>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  sources: {
    type: Array,
    default: () => [],
  },
  trailerUrl: {
    type: String,
    default: null,
  }
});

const activeSourceIndex = ref(0);
const currentSource = ref(null);
const isLoading = ref(false);
const cinemaMode = ref(false);
const showTrailer = ref(false);

const selectSource = (source, index) => {
  activeSourceIndex.value = index;
  currentSource.value = source;
  if (source.type === 'embed') {
    isLoading.value = true;
  } else {
    isLoading.value = false;
  }
};

const handleIframeLoaded = () => {
  isLoading.value = false;
};

const toggleCinemaMode = () => {
  cinemaMode.value = !cinemaMode.value;
};

watch(() => props.sources, (newSources) => {
  if (newSources && newSources.length > 0) {
    selectSource(newSources[0], 0);
  }
}, { immediate: true });

onMounted(() => {
  if (props.sources && props.sources.length > 0) {
    selectSource(props.sources[0], 0);
  }
});
</script>
