<template>
  <div v-if="shouldShowWarning" class="select-none">
    <!-- 1. Strict Modal Overlay or Standard Warning Modal -->
    <div 
      v-if="modalOpen" 
      class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/85 backdrop-blur-xl animate-fade-in"
    >
      <div 
        class="relative w-full max-w-lg rounded-3xl bg-gradient-to-b from-gray-900 via-black to-gray-950 border border-red-500/30 shadow-2xl shadow-red-950/50 p-6 sm:p-8 text-center overflow-hidden"
      >
        <!-- Background Ambient Glow -->
        <div class="absolute -top-24 -left-24 w-48 h-48 rounded-full bg-red-600/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 rounded-full bg-netflix/20 blur-3xl pointer-events-none"></div>

        <!-- Warning Icon -->
        <div class="mx-auto w-16 h-16 rounded-2xl bg-gradient-to-tr from-red-600 to-rose-600 flex items-center justify-center text-white text-2xl shadow-xl shadow-red-600/40 border border-white/20 mb-5 animate-bounce">
          <i class="fas fa-shield-virus"></i>
        </div>

        <!-- Header Titles -->
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-bold uppercase tracking-wider mb-2">
          <i class="fas fa-exclamation-triangle text-[11px]"></i>
          <span>{{ adblockConfig.title || $t('adblock.warning_badge') }}</span>
        </div>

        <h3 class="text-xl sm:text-2xl font-black text-white tracking-tight mt-2">
          {{ $t('adblock.heading') }}
        </h3>

        <p class="text-xs sm:text-sm text-gray-300 leading-relaxed mt-3">
          {{ adblockConfig.message || $t('adblock.message') }}
        </p>

        <!-- How to Disable Quick Guide (Toggleable) -->
        <div class="mt-5 text-left">
          <button 
            type="button" 
            @click="showGuide = !showGuide"
            class="w-full flex items-center justify-between p-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs font-semibold text-gray-300 transition"
          >
            <span class="flex items-center gap-2">
              <i class="fas fa-question-circle text-amber-400"></i>
              <span>{{ $t('adblock.how_to_disable') }}</span>
            </span>
            <i :class="showGuide ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="text-[10px] text-gray-400"></i>
          </button>

          <div v-if="showGuide" class="mt-2 p-3.5 rounded-xl bg-black/60 border border-white/5 space-y-2 text-[11px] text-gray-400 animate-fade-in">
            <div class="flex items-start gap-2">
              <span class="w-5 h-5 rounded-full bg-red-500/20 text-red-400 font-bold flex items-center justify-center shrink-0 text-[10px]">1</span>
              <span>{{ $t('adblock.step_1_lead') }} <strong>{{ $t('adblock.step_1_strong') }}</strong> {{ $t('adblock.step_1_tail') }}</span>
            </div>
            <div class="flex items-start gap-2">
              <span class="w-5 h-5 rounded-full bg-red-500/20 text-red-400 font-bold flex items-center justify-center shrink-0 text-[10px]">2</span>
              <span>{{ $t('adblock.step_2_lead') }} <strong>{{ $t('adblock.step_2_strong') }}</strong></span>
            </div>
            <div class="flex items-start gap-2">
              <span class="w-5 h-5 rounded-full bg-red-500/20 text-red-400 font-bold flex items-center justify-center shrink-0 text-[10px]">3</span>
              <span>{{ $t('adblock.step_3_lead') }} <strong>{{ $t('adblock.step_3_strong') }}</strong></span>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
          <button 
            type="button" 
            @click="reloadPage" 
            class="w-full sm:w-auto flex-1 px-5 py-3 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold text-xs shadow-lg shadow-red-600/30 transition-all flex items-center justify-center gap-2"
          >
            <i class="fas fa-redo-alt"></i>
            <span>{{ $t('adblock.refresh_btn') }}</span>
          </button>

          <button 
            v-if="!isStrictMode"
            type="button" 
            @click="dismissModal" 
            class="w-full sm:w-auto px-4 py-3 rounded-xl bg-white/10 hover:bg-white/15 text-gray-300 hover:text-white font-semibold text-xs border border-white/10 transition"
          >
            {{ $t('adblock.dismiss_btn') }}
          </button>
        </div>

        <p v-if="isStrictMode" class="mt-3 text-[10px] text-red-400 font-medium">
          {{ $t('adblock.strict_mode_note') }}
        </p>
      </div>
    </div>

    <!-- 2. Non-Intrusive Slim Top Warning Bar (If modal was dismissed in non-strict mode) -->
    <div 
      v-else-if="isDetected && isDismissed && !isStrictMode && bannerOpen" 
      class="fixed top-16 inset-x-0 z-40 bg-gradient-to-r from-red-950 via-gray-900 to-red-950 border-b border-red-500/30 px-4 py-2 text-white shadow-xl flex items-center justify-between gap-3 text-xs"
    >
      <div class="flex items-center gap-2 truncate">
        <i class="fas fa-shield-virus text-red-400"></i>
        <span class="truncate">
          <strong>{{ $t('adblock.top_banner_title') }}</strong> {{ $t('adblock.top_banner_msg') }}
        </span>
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <button 
          @click="modalOpen = true" 
          class="px-2.5 py-1 rounded-md bg-red-600 hover:bg-red-500 text-[10px] font-bold text-white transition"
        >
          {{ $t('adblock.guide_btn') }}
        </button>
        <button 
          @click="bannerOpen = false" 
          class="text-gray-400 hover:text-white p-1"
          :title="$t('common.close')"
        >
          <i class="fas fa-times text-xs"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const adsConfig = computed(() => page.props.ads || {});
const adblockConfig = computed(() => adsConfig.value.adblock || {});

const isGlobalEnabled = computed(() => adsConfig.value.enabled !== false);
const isAdblockEnabled = computed(() => isGlobalEnabled.value && adblockConfig.value.enabled !== false);
const isStrictMode = computed(() => Boolean(adblockConfig.value.strict_mode));

const isDetected = ref(false);
const isDismissed = ref(false);
const modalOpen = ref(false);
const bannerOpen = ref(true);
const showGuide = ref(false);

const shouldShowWarning = computed(() => {
  return isAdblockEnabled.value && isDetected.value;
});

const reloadPage = () => {
  if (typeof window !== 'undefined') {
    window.location.reload();
  }
};

const dismissModal = () => {
  modalOpen.value = false;
  isDismissed.value = true;
  if (typeof window !== 'undefined') {
    sessionStorage.setItem('movie_adblock_dismissed', 'true');
  }
};

// Dual Detection Routine
const runAdBlockDetection = async () => {
  if (typeof window === 'undefined' || typeof document === 'undefined') return;

  let detected = false;

  // Check 1: Bait DOM Element
  try {
    const bait = document.createElement('div');
    bait.className = 'ad-banner adsbox ad-placement pub_300x250 pub_728x90 text-ad google-ad';
    bait.setAttribute('style', 'position: absolute !important; left: -9999px !important; top: -9999px !important; width: 1px !important; height: 1px !important; pointer-events: none !important;');
    bait.innerHTML = '&nbsp;';
    document.body.appendChild(bait);

    // Wait a brief tick
    await new Promise(r => setTimeout(r, 100));

    const styles = window.getComputedStyle(bait);
    if (
      bait.offsetParent === null ||
      bait.offsetHeight === 0 ||
      bait.offsetWidth === 0 ||
      styles.display === 'none' ||
      styles.visibility === 'hidden'
    ) {
      detected = true;
    }

    if (bait.parentNode) {
      bait.parentNode.removeChild(bait);
    }
  } catch (e) {
    // If bait creation failed or blocked, flag detected
    detected = true;
  }

  // Check 2: Bait Network Fetch
  if (!detected) {
    try {
      await fetch('https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js', {
        method: 'HEAD',
        mode: 'no-cors',
        cache: 'no-store',
      });
    } catch (err) {
      // Network fetch blocked by browser ad blocker rule
      detected = true;
    }
  }

  if (detected) {
    isDetected.value = true;
    const dismissedInSession = sessionStorage.getItem('movie_adblock_dismissed') === 'true';
    isDismissed.value = dismissedInSession;

    if (isStrictMode.value || !dismissedInSession) {
      modalOpen.value = true;
    }
  }
};

onMounted(() => {
  if (isAdblockEnabled.value) {
    // Give extensions 500ms to inject cosmetic filters
    setTimeout(() => {
      runAdBlockDetection();
    }, 500);
  }
});
</script>