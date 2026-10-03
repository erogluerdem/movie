<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useI18n } from '@/Composables/useI18n';
import FlagIcon from '@/Components/FlagIcon.vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'navbar', // 'navbar' | 'minimal' | 'footer'
  },
  dropUp: {
    type: Boolean,
    default: false,
  },
});

const { locale, setLocale, supportedLocales } = useI18n();
const isOpen = ref(false);
const dropdownRef = ref(null);

function selectLocale(code) {
  isOpen.value = false;
  setLocale(code);
}

function handleClickOutside(event) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false;
  }
}

function handleKeydown(event) {
  if (event.key === 'Escape') {
    isOpen.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <div ref="dropdownRef" class="relative inline-block text-left select-none">
    <!-- Navbar Variant -->
    <button
      v-if="variant === 'navbar'"
      type="button"
      @click="isOpen = !isOpen"
      class="h-9 px-2.5 sm:px-3 rounded-xl bg-white/[0.08] hover:bg-white/[0.14] border border-white/15 hover:border-white/25 text-white flex items-center gap-1.5 sm:gap-2 text-xs font-semibold backdrop-blur-md transition-all duration-200 shadow-sm cursor-pointer focus:outline-none focus:ring-1 focus:ring-netflix/50"
      :aria-expanded="isOpen"
      :title="locale === 'tr' ? 'Dili Değiştir (Türkçe)' : 'Change Language (English)'"
    >
      <FlagIcon :code="locale" class="w-4 h-2.5 rounded-[2px] shadow-xs flex-shrink-0" />
      <span class="font-bold tracking-wider text-[11px] uppercase">{{ locale }}</span>
      <i
        class="fas fa-chevron-down text-[9px] text-gray-400 transition-transform duration-200"
        :class="{ 'rotate-180': isOpen }"
      ></i>
    </button>

    <!-- Minimal Variant (Compact for small headers or drawers) -->
    <button
      v-else-if="variant === 'minimal'"
      type="button"
      @click="isOpen = !isOpen"
      class="w-9 h-9 rounded-xl bg-white/[0.08] hover:bg-white/[0.14] border border-white/15 text-white flex items-center justify-center text-sm backdrop-blur-md transition cursor-pointer"
      :title="locale === 'tr' ? 'Türkçe' : 'English'"
    >
      <FlagIcon :code="locale" class="w-4.5 h-3 rounded-[2px] shadow-xs flex-shrink-0" />
    </button>

    <!-- Footer Variant (Inline Toggle Pill) -->
    <div
      v-else-if="variant === 'footer'"
      class="inline-flex items-center bg-white/[0.06] border border-white/10 rounded-xl p-1 gap-1"
    >
      <button
        v-for="item in supportedLocales"
        :key="item.code"
        type="button"
        @click="selectLocale(item.code)"
        :class="[
          locale === item.code
            ? 'bg-netflix text-white font-bold shadow-md shadow-red-600/30'
            : 'text-gray-400 hover:text-white hover:bg-white/5 font-medium'
        ]"
        class="px-2.5 py-1 rounded-lg text-xs flex items-center gap-1.5 transition-all duration-200 cursor-pointer"
      >
        <FlagIcon :code="item.code" class="w-4 h-2.5 rounded-[2px] shadow-xs flex-shrink-0" />
        <span>{{ item.name }}</span>
      </button>
    </div>

    <!-- Dropdown Menu (for navbar and minimal) -->
    <transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div
        v-if="isOpen && variant !== 'footer'"
        :class="[
          dropUp ? 'bottom-full mb-2' : 'top-full mt-2',
          'absolute right-0 w-36 bg-gray-950/95 border border-white/15 rounded-2xl shadow-2xl backdrop-blur-xl p-1.5 z-50 divide-y divide-white/5 overflow-hidden'
        ]"
      >
        <div class="px-2 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
          {{ locale === 'tr' ? 'Dil Seçimi' : 'Language' }}
        </div>
        <div class="pt-1 space-y-1">
          <button
            v-for="item in supportedLocales"
            :key="item.code"
            type="button"
            @click="selectLocale(item.code)"
            :class="[
              locale === item.code
                ? 'bg-netflix/20 text-white font-bold border border-netflix/40'
                : 'text-gray-300 hover:text-white hover:bg-white/10 font-medium'
            ]"
            class="w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-xs transition duration-150 cursor-pointer text-left"
          >
            <div class="flex items-center gap-2">
              <FlagIcon :code="item.code" class="w-4 h-2.5 rounded-[2px] shadow-xs flex-shrink-0" />
              <span>{{ item.name }}</span>
            </div>
            <i
              v-if="locale === item.code"
              class="fas fa-check text-[10px] text-netflix"
            ></i>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>
