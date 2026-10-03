<template>
  <div 
    v-if="shouldRender"
    class="group relative flex flex-col rounded-xl overflow-hidden bg-gradient-to-b from-gray-900 via-black to-red-950/20 border border-amber-500/20 hover:border-amber-500/40 transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-black/80 select-none"
  >
    <!-- Ad Card Body (Poster aspect ratio) -->
    <div class="relative aspect-[2/3] w-full overflow-hidden flex flex-col justify-between p-4 bg-gradient-to-b from-neutral-900/90 to-black/95">
      <!-- Top Badges -->
      <div class="flex items-center justify-between w-full z-10">
        <span class="bg-amber-500/20 border border-amber-500/40 text-amber-400 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full flex items-center gap-1 shadow-sm">
          <i class="fas fa-ad text-[9px]"></i>
          <span>SPONSOR</span>
        </span>

        <span class="text-[9px] text-gray-500 font-mono">Movie® Ad</span>
      </div>

      <!-- Center Graphic & Info -->
      <div class="my-auto text-center space-y-3 z-10">
        <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-amber-500/20 to-red-600/20 border border-amber-500/30 flex items-center justify-center text-amber-400 text-xl shadow-lg shadow-amber-500/10 group-hover:scale-110 transition-transform">
          <i class="fas fa-crown"></i>
        </div>

        <div>
          <h4 class="text-xs font-black text-white tracking-wide uppercase group-hover:text-amber-400 transition-colors">
            {{ placementData.name || $t('adblock.featured_sponsor') }}
          </h4>
          <p class="text-[10px] text-gray-400 mt-1 line-clamp-3 leading-relaxed">
            {{ placementData.description || $t('adblock.featured_sponsor_desc') }}
          </p>
        </div>
      </div>

      <!-- Bottom Action Button -->
      <div class="z-10 pt-2 border-t border-white/10 text-center">
        <a 
          v-if="placementData.target_url" 
          :href="placementData.target_url" 
          target="_blank" 
          rel="noopener noreferrer"
          class="block w-full py-2 rounded-lg bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black font-bold text-[11px] shadow-md transition-all"
        >
          {{ $t('adblock.explore_ad') }} <i class="fas fa-external-link-alt text-[9px] ml-1"></i>
        </a>
        <Link 
          v-else 
          href="/contact" 
          class="block w-full py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white font-bold text-[11px] border border-white/10 transition"
        >
          {{ $t('adblock.advertise_btn') }} <i class="fas fa-arrow-right text-[9px] ml-1"></i>
        </Link>
      </div>

      <!-- Background Subtle Pattern -->
      <div class="absolute inset-0 bg-[radial-gradient(#ffffff08_1px,transparent_1px)] [background-size:12px_12px] opacity-40"></div>
    </div>

    <!-- Title & Category Bar (matches MediaCard bottom section) -->
    <div class="p-2.5 bg-black/80 flex flex-col justify-between flex-1">
      <div class="text-xs font-bold text-gray-300 truncate">
        {{ $t('adblock.sponsored_space') }}
      </div>
      <div class="flex items-center justify-between text-[10px] text-gray-500 mt-1">
        <span>{{ $t('adblock.in_feed_promo') }}</span>
        <span class="text-amber-400 font-semibold">{{ $t('adblock.recommended') }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

const page = usePage();
const adsConfig = computed(() => page.props.ads || {});
const isGlobalEnabled = computed(() => adsConfig.value.enabled !== false);
const placementData = computed(() => adsConfig.value.placements?.in_feed_catalog || null);

const shouldRender = computed(() => {
  return isGlobalEnabled.value && placementData.value && placementData.value.is_active;
});
</script>