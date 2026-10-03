<template>
  <div 
    v-if="shouldRender" 
    class="fixed bottom-0 inset-x-0 z-40 bg-black/95 backdrop-blur-md border-t border-white/10 shadow-2xl transition-all duration-300 select-none animate-slide-up"
  >
    <div class="max-w-4xl mx-auto relative px-3 py-2 flex flex-col items-center justify-center">
      <!-- Close Pill Button -->
      <button 
        @click="closeAd"
        class="absolute -top-3.5 right-4 px-2.5 py-0.5 rounded-full bg-gray-900 border border-white/20 text-gray-400 hover:text-white text-[10px] font-bold shadow-lg transition flex items-center gap-1.5"
        :title="$t('common.close')"
      >
        <span>{{ $t('common.close') }}</span>
        <i class="fas fa-times text-[9px]"></i>
      </button>

      <!-- Slot Container -->
      <div class="w-full flex justify-center overflow-hidden">
        <AdSlot placement="sticky_footer" custom-class="!my-0" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdSlot from '@/Components/AdSlot.vue';

const page = usePage();
const adsConfig = computed(() => page.props.ads || {});
const isGlobalEnabled = computed(() => adsConfig.value.enabled !== false);
const placementData = computed(() => adsConfig.value.placements?.sticky_footer || null);

const isClosed = ref(false);

const shouldRender = computed(() => {
  return (
    isGlobalEnabled.value &&
    placementData.value &&
    placementData.value.is_active &&
    !isClosed.value
  );
});

const closeAd = () => {
  isClosed.value = true;
  if (typeof window !== 'undefined') {
    sessionStorage.setItem('movie_sticky_footer_closed', 'true');
  }
};

onMounted(() => {
  if (typeof window !== 'undefined') {
    isClosed.value = sessionStorage.getItem('movie_sticky_footer_closed') === 'true';
  }
});
</script>