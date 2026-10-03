<template>
  <div v-if="items && items.length > 0" class="my-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4 px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3">
        <span class="w-1.5 h-6 bg-netflix rounded-full"></span>
        <h2 class="text-xl font-bold text-white tracking-wide flex items-center gap-2">
          <i v-if="icon" :class="['fas', icon, 'text-netflix text-base']"></i>
          <span>{{ title }}</span>
        </h2>
      </div>

      <div class="flex items-center gap-3">
        <Link 
          v-if="viewAllLink" 
          :href="viewAllLink"
          class="text-xs font-semibold text-gray-400 hover:text-netflix transition-colors flex items-center gap-1"
        >
          <span>{{ $t('home.view_all') }}</span>
          <i class="fas fa-chevron-right text-[10px]"></i>
        </Link>

        <!-- Carousel navigation arrows -->
        <div class="hidden sm:flex items-center gap-1">
          <button 
            @click="scrollLeft" 
            class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition border border-white/5"
            aria-label="Scroll left"
          >
            <i class="fas fa-chevron-left text-xs"></i>
          </button>
          <button 
            @click="scrollRight" 
            class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition border border-white/5"
            aria-label="Scroll right"
          >
            <i class="fas fa-chevron-right text-xs"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Horizontal Scrollable Container -->
    <div 
      ref="scrollContainer" 
      class="flex gap-4 overflow-x-auto scroll-smooth px-4 sm:px-6 lg:px-8 pb-4 no-scrollbar"
      style="scrollbar-width: none; -ms-overflow-style: none;"
    >
      <div 
        v-for="item in items" 
        :key="item.id" 
        class="w-[160px] sm:w-[190px] md:w-[210px] flex-shrink-0"
      >
        <MediaCard :item="item" :mediaType="mediaType" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import MediaCard from '@/Components/MediaCard.vue';

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  items: {
    type: Array,
    default: () => [],
  },
  viewAllLink: {
    type: String,
    default: null,
  },
  icon: {
    type: String,
    default: null,
  },
  mediaType: {
    type: String,
    default: null,
  }
});

const scrollContainer = ref(null);

const scrollLeft = () => {
  if (scrollContainer.value) {
    scrollContainer.value.scrollBy({ left: -400, behavior: 'smooth' });
  }
};

const scrollRight = () => {
  if (scrollContainer.value) {
    scrollContainer.value.scrollBy({ left: 400, behavior: 'smooth' });
  }
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>
