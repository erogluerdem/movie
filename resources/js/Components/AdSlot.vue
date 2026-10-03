<template>
  <div 
    v-if="shouldRender" 
    :class="['ad-slot-wrapper relative overflow-hidden transition-all duration-300 my-4 select-none', customClass]"
    :data-placement="placement"
  >
    <!-- Ad Label -->
    <div class="flex items-center justify-between text-[9px] uppercase tracking-widest text-gray-500 font-semibold mb-1 px-1">
      <span class="flex items-center gap-1">
        <i class="fas fa-ad text-[10px] text-amber-500/80"></i>
        <span>{{ $t('adblock.ad_label') }}</span>
      </span>
      <span class="text-[8px] text-gray-600">{{ $t('adblock.ad_engine') }}</span>
    </div>

    <!-- Ad Unit Box -->
    <div 
      class="ad-container rounded-xl bg-black/40 border border-white/10 flex items-center justify-center min-h-[90px] w-full p-2 relative overflow-hidden group"
      :style="boxStyle"
    >
      <!-- 1. Custom Image Banner -->
      <a 
        v-if="placementData.type === 'banner_image' && placementData.image_url" 
        :href="placementData.target_url || '#'" 
        target="_blank" 
        rel="noopener noreferrer"
        class="block w-full h-full text-center transition-transform hover:scale-[1.01]"
      >
        <img 
          :src="placementData.image_url" 
          :alt="placementData.name || 'Sponsorlu Reklam'" 
          class="w-full max-h-[120px] object-contain rounded-lg mx-auto shadow-md"
        />
      </a>

      <!-- 2. Custom Script / HTML Code -->
      <div 
        v-else-if="placementData.type === 'custom_code' && placementData.code" 
        ref="customCodeContainer" 
        class="w-full overflow-hidden text-center"
      ></div>

      <!-- 3. Google AdSense Unit (Live or Preview Mockup) -->
      <div 
        v-else-if="placementData.type === 'adsense' && hasValidAdSense"
        class="w-full flex justify-center overflow-hidden"
      >
        <ins 
          class="adsbygoogle"
          style="display:block; width:100%; min-height:90px;"
          :data-ad-client="resolvedAdClient"
          :data-ad-slot="placementData.ad_slot"
          data-ad-format="auto"
          data-full-width-responsive="true"
        ></ins>
      </div>

      <!-- 4. Sleek Movie® Ad Placeholder Mockup (When no custom ad code provided or preview mode) -->
      <div 
        v-else 
        class="w-full flex flex-col sm:flex-row items-center justify-between gap-3 p-4 rounded-xl bg-gradient-to-r from-red-950/20 via-black/40 to-neutral-900/40 border border-red-500/20 text-center sm:text-left"
      >
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-netflix/20 border border-netflix/40 flex items-center justify-center text-netflix shrink-0 shadow-lg shadow-netflix/20">
            <i class="fas fa-bullhorn text-sm"></i>
          </div>
          <div>
            <div class="text-xs font-bold text-white flex items-center gap-2 justify-center sm:justify-start">
              <span>{{ placementData.name || $t('adblock.ad_space') }}</span>
              <span class="px-1.5 py-0.2 rounded text-[8px] font-mono bg-amber-500/20 text-amber-400 border border-amber-500/30">
                {{ placementData.type === 'adsense' ? 'Google AdSense' : 'Sponsor' }}
              </span>
            </div>
            <p class="text-[10px] text-gray-400 mt-0.5">
              {{ $t('adblock.contact_sponsor') }}
            </p>
          </div>
        </div>

        <Link 
          href="/contact" 
          class="shrink-0 px-3 py-1.5 rounded-lg text-[10px] font-bold bg-white/10 hover:bg-white/20 text-white border border-white/10 transition shadow-sm"
        >
          {{ $t('adblock.advertise_btn') }}
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, nextTick, watch } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';

const props = defineProps({
  placement: {
    type: String,
    required: true,
  },
  customClass: {
    type: String,
    default: '',
  },
  boxStyle: {
    type: Object,
    default: () => ({}),
  },
});

const page = usePage();
const customCodeContainer = ref(null);
const isClient = ref(false);
const screenWidth = ref(1024);

const adsConfig = computed(() => page.props.ads || {});
const isGlobalEnabled = computed(() => adsConfig.value.enabled !== false);
const placementData = computed(() => adsConfig.value.placements?.[props.placement] || null);

// Check if placement is active
const isPlacementActive = computed(() => {
  return isGlobalEnabled.value && placementData.value && placementData.value.is_active;
});

// Device target filtering
const shouldRender = computed(() => {
  if (!isPlacementActive.value) return false;
  if (!isClient.value) return true;

  const target = placementData.value.device_target || 'all';
  if (target === 'mobile' && screenWidth.value >= 768) return false;
  if (target === 'desktop' && screenWidth.value < 768) return false;

  return true;
});

// AdSense Resolution
const resolvedAdClient = computed(() => {
  return placementData.value?.ad_client || adsConfig.value.global_ad_client || '';
});

const hasValidAdSense = computed(() => {
  return Boolean(resolvedAdClient.value && resolvedAdClient.value.startsWith('ca-pub-') && placementData.value?.ad_slot);
});

// Execute Custom Scripts safely
const mountCustomCode = () => {
  if (!customCodeContainer.value || !placementData.value?.code) return;

  const html = placementData.value.code;
  customCodeContainer.value.innerHTML = '';

  const tempDiv = document.createElement('div');
  tempDiv.innerHTML = html;

  Array.from(tempDiv.childNodes).forEach(node => {
    if (node.tagName === 'SCRIPT') {
      const script = document.createElement('script');
      Array.from(node.attributes).forEach(attr => script.setAttribute(attr.name, attr.value));
      script.innerHTML = node.innerHTML;
      customCodeContainer.value.appendChild(script);
    } else {
      customCodeContainer.value.appendChild(node.cloneNode(true));
    }
  });
};

// Push Google AdSense in SPA
const pushAdSense = () => {
  if (typeof window === 'undefined') return;
  try {
    if (hasValidAdSense.value) {
      if (!document.querySelector('script[src*="adsbygoogle.js"]')) {
        const s = document.createElement('script');
        s.async = true;
        s.src = `https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${resolvedAdClient.value}`;
        s.crossOrigin = 'anonymous';
        document.head.appendChild(s);
      }
      (window.adsbygoogle = window.adsbygoogle || []).push({});
    }
  } catch (e) {
    // AdSense push might fail gracefully if already filled
  }
};

onMounted(() => {
  isClient.value = true;
  screenWidth.value = window.innerWidth;

  window.addEventListener('resize', () => {
    screenWidth.value = window.innerWidth;
  });

  nextTick(() => {
    if (placementData.value?.type === 'custom_code') {
      mountCustomCode();
    } else if (hasValidAdSense.value) {
      pushAdSense();
    }
  });

  // Re-trigger on SPA navigation
  router.on('navigate', () => {
    nextTick(() => {
      if (hasValidAdSense.value) {
        pushAdSense();
      }
    });
  });
});

watch(() => placementData.value, () => {
  nextTick(() => {
    if (placementData.value?.type === 'custom_code') {
      mountCustomCode();
    }
  });
}, { deep: true });
</script>