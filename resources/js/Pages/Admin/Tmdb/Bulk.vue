<template>
  <AdminLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-white/10">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <span>Toplu TMDB İçe Aktarma</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
              TMDB Feed Explorer
            </span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">TMDB keşif akışlarından (Popüler, Trend, En Çok Oy Alan) tek seferde toplu içerik içe aktarın.</p>
        </div>

        <div class="flex items-center gap-2">
          <Link 
            href="/admin/tmdb" 
            class="px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 text-xs font-semibold transition border border-white/10 flex items-center gap-2"
          >
            <i class="fas fa-robot text-indigo-400"></i>
            <span>Otomatik Bot</span>
          </Link>
          <Link 
            href="/admin/settings" 
            class="px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 text-xs font-semibold transition border border-white/10 flex items-center gap-2"
          >
            <i class="fas fa-key text-amber-400"></i>
            <span>API Anahtarı</span>
          </Link>
        </div>
      </div>

      <!-- Warning if TMDB API is missing -->
      <div v-if="!configured" class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <i class="fas fa-exclamation-triangle text-amber-400 text-base flex-shrink-0"></i>
          <div>
            <span class="font-bold">TMDB API Anahtarı Tanımlı Değil!</span>
            <p class="text-amber-300/80 text-[11px] mt-0.5">Toplu içerik çekebilmek için lütfen TMDB API anahtarınızı girin.</p>
          </div>
        </div>
        <Link href="/admin/settings" class="px-3 py-1.5 rounded-xl bg-amber-500 text-black font-bold text-xs hover:bg-amber-400 transition flex-shrink-0">
          Ayarları Aç
        </Link>
      </div>

      <div v-if="error" class="p-4 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-300 text-xs flex items-center gap-3">
        <i class="fas fa-times-circle text-red-400 text-base"></i>
        <span>{{ error }}</span>
      </div>

      <!-- Filter & Controls Bar -->
      <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Content Type & Feed Tabs -->
        <div class="flex flex-wrap items-center gap-2">
          <!-- Type Toggle -->
          <div class="flex items-center p-1 rounded-xl bg-black/40 border border-white/10">
            <button 
              @click="setParams({ type: 'movie', page: 1 })"
              :class="type === 'movie' ? 'bg-netflix text-white font-bold shadow-md shadow-red-600/30' : 'text-gray-400 hover:text-white'"
              class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5"
            >
              <i class="fas fa-film"></i>
              <span>Film</span>
            </button>
            <button 
              @click="setParams({ type: 'tv', page: 1 })"
              :class="type === 'tv' ? 'bg-netflix text-white font-bold shadow-md shadow-red-600/30' : 'text-gray-400 hover:text-white'"
              class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5"
            >
              <i class="fas fa-tv"></i>
              <span>Dizi</span>
            </button>
          </div>

          <!-- Feed Toggle -->
          <div class="flex items-center p-1 rounded-xl bg-black/40 border border-white/10">
            <button 
              @click="setParams({ feed: 'popular', page: 1 })"
              :class="feed === 'popular' ? 'bg-white/15 text-white font-bold' : 'text-gray-400 hover:text-white'"
              class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5"
            >
              <i class="fas fa-fire text-amber-400"></i>
              <span>Popüler</span>
            </button>
            <button 
              @click="setParams({ feed: 'trending', page: 1 })"
              :class="feed === 'trending' ? 'bg-white/15 text-white font-bold' : 'text-gray-400 hover:text-white'"
              class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5"
            >
              <i class="fas fa-bolt text-indigo-400"></i>
              <span>Trend</span>
            </button>
            <button 
              @click="setParams({ feed: 'top_rated', page: 1 })"
              :class="feed === 'top_rated' ? 'bg-white/15 text-white font-bold' : 'text-gray-400 hover:text-white'"
              class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5"
            >
              <i class="fas fa-star text-amber-300"></i>
              <span>En Çok Oy Alan</span>
            </button>
          </div>
        </div>

        <!-- Bulk Action Controls -->
        <div class="flex items-center gap-2 flex-wrap">
          <button 
            @click="toggleSelectAll"
            class="px-3 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white text-xs font-semibold transition border border-white/10 flex items-center gap-2"
          >
            <i :class="isAllSelected ? 'fas fa-check-square text-emerald-400' : 'far fa-square text-gray-400'"></i>
            <span>{{ isAllSelected ? 'Seçimi Kaldır' : 'İçe Aktarılmayanları Seç' }}</span>
          </button>

          <button 
            @click="runBulkImport"
            :disabled="selectedIds.length === 0 || importing"
            :class="selectedIds.length > 0 && !importing ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30' : 'bg-white/5 text-gray-500 cursor-not-allowed border border-white/5'"
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2"
          >
            <i :class="importing ? 'fas fa-spinner fa-spin' : 'fas fa-cloud-download-alt'"></i>
            <span>Seçilenleri İçe Aktar ({{ selectedIds.length }})</span>
          </button>
        </div>
      </div>

      <!-- Feed Grid -->
      <div v-if="items.length === 0" class="p-12 text-center rounded-2xl bg-white/5 border border-white/10 text-gray-400 text-sm">
        <i class="fas fa-film text-3xl mb-3 text-gray-600"></i>
        <div>Bu kriterde içerik bulunamadı veya TMDB API'ye ulaşılamıyor.</div>
      </div>

      <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4">
        <div 
          v-for="item in items" 
          :key="item.tmdb_id"
          :class="[
            item.is_imported ? 'border-white/10 opacity-75' : 
            selectedIds.includes(item.tmdb_id) ? 'border-emerald-500 ring-2 ring-emerald-500/30' : 'border-white/10 hover:border-white/30',
          ]"
          class="p-2.5 rounded-2xl bg-white/5 border transition flex flex-col justify-between group relative overflow-hidden"
        >
          <!-- Card Top: Image + Badges -->
          <div class="relative aspect-[2/3] rounded-xl overflow-hidden bg-gray-900 mb-2">
            <img 
              :src="item.poster_path ? 'https://image.tmdb.org/t/p/w342' + item.poster_path : '/images/placeholders/poster.png'" 
              :alt="item.title"
              class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
              loading="lazy"
            />

            <!-- Top Overlay: Rating & Selection -->
            <div class="absolute inset-x-0 top-0 p-2 flex items-center justify-between bg-gradient-to-b from-black/80 to-transparent">
              <!-- Rating -->
              <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-black/70 backdrop-blur-md text-amber-400 flex items-center gap-1 border border-white/10">
                <i class="fas fa-star text-[9px]"></i> {{ item.vote_average ? item.vote_average.toFixed(1) : 'N/A' }}
              </span>

              <!-- Imported or Checkbox -->
              <div v-if="item.is_imported">
                <span class="px-2 py-0.5 rounded-md text-[9px] font-black bg-emerald-500 text-black shadow">
                  Mevcut
                </span>
              </div>
              <div v-else>
                <input 
                  type="checkbox" 
                  :value="item.tmdb_id" 
                  v-model="selectedIds"
                  class="w-4 h-4 rounded text-emerald-500 focus:ring-emerald-500 bg-black/60 border-white/30 cursor-pointer"
                />
              </div>
            </div>

            <!-- Bottom Year -->
            <div class="absolute inset-x-0 bottom-0 p-2 bg-gradient-to-t from-black/90 to-transparent text-[10px] text-gray-300 flex items-center justify-between">
              <span>{{ item.release_date || item.first_air_date ? (item.release_date || item.first_air_date).substring(0, 4) : '' }}</span>
              <span class="text-[9px] text-gray-400 font-mono">#{{ item.tmdb_id }}</span>
            </div>
          </div>

          <!-- Title & Actions -->
          <div class="flex-1 flex flex-col justify-between">
            <h3 class="text-xs font-bold text-white line-clamp-1 group-hover:text-netflix transition" :title="item.title">
              {{ item.title }}
            </h3>

            <div class="mt-2 pt-2 border-t border-white/5 flex items-center justify-between gap-1">
              <span v-if="item.is_imported" class="text-[10px] text-emerald-400 font-semibold flex items-center gap-1">
                <i class="fas fa-check text-[9px]"></i> İçe Aktarıldı
              </span>
              <button 
                v-else
                @click="importSingle(item.tmdb_id)"
                :disabled="importing"
                class="w-full py-1.5 rounded-lg bg-white/10 hover:bg-emerald-600 hover:text-white text-gray-300 text-[11px] font-bold transition flex items-center justify-center gap-1"
              >
                <i class="fas fa-download text-[10px]"></i>
                <span>Aktar</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex items-center justify-center gap-3 pt-4 border-t border-white/10">
        <button 
          @click="setParams({ page: page - 1 })"
          :disabled="page <= 1"
          class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 disabled:opacity-30 disabled:hover:bg-white/5 text-gray-300 text-xs font-bold transition border border-white/10 flex items-center gap-2"
        >
          <i class="fas fa-chevron-left"></i>
          <span>Önceki Sayfa</span>
        </button>
        <span class="text-xs text-gray-400 font-mono">
          Sayfa <strong class="text-white">{{ page }}</strong> / {{ totalPages }}
        </span>
        <button 
          @click="setParams({ page: page + 1 })"
          :disabled="page >= totalPages"
          class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 disabled:opacity-30 disabled:hover:bg-white/5 text-gray-300 text-xs font-bold transition border border-white/10 flex items-center gap-2"
        >
          <span>Sonraki Sayfa</span>
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../Layout.vue';

const props = defineProps({
  feed: {
    type: String,
    default: 'popular',
  },
  type: {
    type: String,
    default: 'movie',
  },
  page: {
    type: Number,
    default: 1,
  },
  totalPages: {
    type: Number,
    default: 1,
  },
  items: {
    type: Array,
    default: () => [],
  },
  configured: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: null,
  },
});

const selectedIds = ref([]);
const importing = ref(false);

const setParams = (newParams) => {
  selectedIds.value = [];
  router.get('/admin/tmdb/bulk', {
    feed: newParams.feed !== undefined ? newParams.feed : props.feed,
    type: newParams.type !== undefined ? newParams.type : props.type,
    page: newParams.page !== undefined ? newParams.page : props.page,
  }, { preserveState: true, preserveScroll: true });
};

const importableItems = computed(() => {
  return props.items.filter(i => !i.is_imported);
});

const isAllSelected = computed(() => {
  if (importableItems.value.length === 0) return false;
  return importableItems.value.every(i => selectedIds.value.includes(i.tmdb_id));
});

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedIds.value = [];
  } else {
    selectedIds.value = importableItems.value.map(i => i.tmdb_id);
  }
};

const runBulkImport = () => {
  if (selectedIds.value.length === 0 || importing.value) return;
  importing.value = true;
  router.post('/admin/tmdb/bulk-import', {
    type: props.type,
    tmdb_ids: selectedIds.value,
  }, {
    preserveScroll: true,
    onFinish: () => {
      importing.value = false;
      selectedIds.value = [];
    },
  });
};

const importSingle = (tmdbId) => {
  if (importing.value) return;
  importing.value = true;
  router.post('/admin/tmdb/bulk-import', {
    type: props.type,
    tmdb_ids: [tmdbId],
  }, {
    preserveScroll: true,
    onFinish: () => {
      importing.value = false;
    },
  });
};
</script>
