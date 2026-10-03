<template>
  <div class="glass-card rounded-xl p-4 mb-8 border border-white/10 flex flex-wrap items-center justify-between gap-4">
    <!-- Filters Container -->
    <div class="flex flex-wrap items-center gap-3 flex-1">
      <!-- Genre Selector -->
      <div v-if="genres && genres.length" class="min-w-[140px]">
        <select 
          v-model="localGenre" 
          @change="applyFilters"
          class="w-full bg-gray-900 text-white text-xs rounded-lg px-3 py-2 border border-white/10 focus:outline-none focus:border-netflix cursor-pointer"
        >
          <option value="">{{ $t('movies.all_genres') }}</option>
          <option v-for="g in genres" :key="g" :value="g">{{ g }}</option>
        </select>
      </div>

      <!-- Year Selector -->
      <div class="min-w-[110px]">
        <select 
          v-model="localYear" 
          @change="applyFilters"
          class="w-full bg-gray-900 text-white text-xs rounded-lg px-3 py-2 border border-white/10 focus:outline-none focus:border-netflix cursor-pointer"
        >
          <option value="">{{ $t('movies.all_years') }}</option>
          <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>
      </div>

      <!-- Sort By -->
      <div class="min-w-[130px]">
        <select 
          v-model="localSort" 
          @change="applyFilters"
          class="w-full bg-gray-900 text-white text-xs rounded-lg px-3 py-2 border border-white/10 focus:outline-none focus:border-netflix cursor-pointer"
        >
          <option value="popular">{{ $t('search.sort_popular') }}</option>
          <option value="rating">{{ $t('search.sort_rating') }}</option>
          <option value="newest">{{ $t('search.sort_newest') }}</option>
        </select>
      </div>

      <!-- Reset Button -->
      <button 
        v-if="hasActiveFilters" 
        @click="resetFilters"
        class="text-xs text-gray-400 hover:text-netflix flex items-center gap-1 px-2.5 py-2 transition"
      >
        <i class="fas fa-undo text-[10px]"></i>
        <span>{{ $t('search.reset_filters') }}</span>
      </button>
    </div>

    <!-- Search input in filter bar -->
    <div class="relative w-full sm:w-64">
      <input 
        type="text" 
        v-model="localSearch" 
        @keydown.enter="applyFilters"
        :placeholder="$t('movies.filter_placeholder')"
        class="w-full bg-gray-900 text-white text-xs rounded-lg pl-8 pr-8 py-2 border border-white/10 focus:outline-none focus:border-netflix"
      />
      <i class="fas fa-search absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
      <button 
        v-if="localSearch" 
        @click="localSearch = ''; applyFilters();"
        class="absolute right-2.5 top-2 text-gray-400 hover:text-white"
      >
        <i class="fas fa-times text-xs"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  genres: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  baseRoute: {
    type: String,
    required: true,
  }
});

const localGenre = ref(props.filters.genre || '');
const localYear = ref(props.filters.year || '');
const localSort = ref(props.filters.sort || 'popular');
const localSearch = ref(props.filters.search || '');

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 15 }, (_, i) => currentYear - i);

const hasActiveFilters = computed(() => {
  return localGenre.value || localYear.value || localSort.value !== 'popular' || localSearch.value;
});

const applyFilters = () => {
  router.get(
    props.baseRoute,
    {
      genre: localGenre.value || undefined,
      year: localYear.value || undefined,
      sort: localSort.value !== 'popular' ? localSort.value : undefined,
      search: localSearch.value || undefined,
    },
    { preserveState: true, replace: true }
  );
};

const resetFilters = () => {
  localGenre.value = '';
  localYear.value = '';
  localSort.value = 'popular';
  localSearch.value = '';
  router.get(props.baseRoute, {}, { preserveState: true, replace: true });
};
</script>
