<template>
  <div class="glass-card rounded-2xl p-5 sm:p-6 border border-white/10 space-y-6">
    <!-- Season Selection Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-white/10">
      <div class="flex items-center gap-2">
        <i class="fas fa-list-ol text-netflix text-lg"></i>
        <h3 class="font-bold text-lg text-white">{{ $t('home.episodes') }}</h3>
        <span class="text-xs text-gray-400 font-medium ml-2">
          ({{ currentSeasonEpisodes.length }} {{ $t('home.episodes') }})
        </span>
      </div>

      <!-- Season Dropdown / Pills -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
        <button
          v-for="season in seasons"
          :key="season.id"
          @click="changeSeason(season)"
          :class="[
            selectedSeasonId === season.id
              ? 'bg-netflix text-white font-bold shadow-lg shadow-red-900/40'
              : 'bg-white/5 hover:bg-white/10 text-gray-300 font-medium'
          ]"
          class="px-3.5 py-1.5 rounded-lg text-xs transition flex-shrink-0 flex items-center gap-1.5"
        >
          <span>{{ season.name || $t('tv.season_title', { number: season.season_number }) }}</span>
        </button>
      </div>
    </div>

    <!-- Episodes Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 max-h-[500px] overflow-y-auto pr-1">
      <div
        v-for="ep in currentSeasonEpisodes"
        :key="ep.id"
        @click="$emit('select-episode', ep)"
        :class="[
          activeEpisode?.id === ep.id 
            ? 'ring-2 ring-netflix bg-white/10' 
            : 'bg-white/5 hover:bg-white/10'
        ]"
        class="group cursor-pointer rounded-xl overflow-hidden border border-white/5 transition-all duration-200 flex flex-col"
      >
        <!-- Still Image Container -->
        <div class="relative aspect-video w-full bg-gray-900 overflow-hidden">
          <img 
            :src="ep.still_path || fallbackImage" 
            :alt="ep.name" 
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            @error="handleImgError($event)"
          />
          <div class="absolute inset-0 bg-black/40 group-hover:bg-black/10 transition-colors"></div>

          <!-- Play overlay icon -->
          <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
            <div class="w-9 h-9 rounded-full bg-netflix text-white flex items-center justify-center text-xs shadow-lg">
              <i class="fas fa-play ml-0.5"></i>
            </div>
          </div>

          <!-- Episode Number Badge -->
          <span class="absolute top-2 left-2 bg-black/70 backdrop-blur px-2 py-0.5 rounded text-[10px] font-extrabold text-white">
            EP {{ ep.episode_number }}
          </span>

          <!-- Duration Badge -->
          <span v-if="ep.duration" class="absolute bottom-2 right-2 bg-black/70 backdrop-blur px-1.5 py-0.5 rounded text-[10px] text-gray-300">
            {{ ep.duration }}
          </span>
        </div>

        <!-- Episode Info -->
        <div class="p-3 flex-1 flex flex-col justify-between">
          <div>
            <h4 class="font-semibold text-sm text-white group-hover:text-netflix transition-colors line-clamp-1">
              {{ ep.episode_number }}. {{ ep.name }}
            </h4>
            <p class="text-xs text-gray-400 mt-1 line-clamp-2 leading-relaxed">
              {{ ep.overview || $t('tv.watch_full_ep') }}
            </p>
          </div>
          <div class="mt-2 text-[10px] text-gray-500 font-medium">
            {{ $t('tv.air_date', { date: ep.air_date || 'N/A' }) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  seasons: {
    type: Array,
    default: () => [],
  },
  initialSeasonId: {
    type: [Number, String],
    default: null,
  },
  activeEpisode: {
    type: Object,
    default: null,
  },
  fallbackImage: {
    type: String,
    default: 'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?w=500&q=80',
  }
});

const emit = defineEmits(['select-episode', 'select-season']);

const selectedSeasonId = ref(props.initialSeasonId || (props.seasons[0]?.id ?? null));

const currentSeasonEpisodes = computed(() => {
  const current = props.seasons.find(s => s.id === selectedSeasonId.value);
  return current ? current.episodes || [] : [];
});

const changeSeason = (season) => {
  selectedSeasonId.value = season.id;
  emit('select-season', season);
  if (season.episodes && season.episodes.length > 0) {
    emit('select-episode', season.episodes[0]);
  }
};

const handleImgError = (event) => {
  event.target.src = props.fallbackImage;
};

watch(() => props.initialSeasonId, (newId) => {
  if (newId) selectedSeasonId.value = newId;
});
</script>
