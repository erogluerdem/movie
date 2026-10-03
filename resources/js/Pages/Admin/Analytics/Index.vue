<template>
  <AdminLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-white/10">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <span>Yayın & İzlenme Analitiği</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
              Gerçek Zamanlı
            </span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">Platform genelindeki izleme istatistikleri, video tamamlama oranları ve en popüler içerikler.</p>
        </div>

        <!-- Period Toggle -->
        <div class="flex items-center gap-1.5 p-1 rounded-xl bg-white/5 border border-white/10 self-start sm:self-auto">
          <button 
            v-for="d in [7, 14, 30]" 
            :key="d"
            @click="changePeriod(d)"
            :class="days === d ? 'bg-netflix text-white font-bold shadow-md shadow-red-600/30' : 'text-gray-400 hover:text-white'"
            class="px-3 py-1.5 rounded-lg text-xs transition"
          >
            Son {{ d }} Gün
          </button>
        </div>
      </div>

      <!-- KPI Metrics -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Toplam İzleme -->
        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex flex-col justify-between">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400">Toplam Oynatma</span>
            <div class="w-8 h-8 rounded-xl bg-red-500/10 text-netflix flex items-center justify-center text-sm">
              <i class="fas fa-play"></i>
            </div>
          </div>
          <div>
            <div class="text-2xl sm:text-3xl font-black text-white">{{ stats.totalViews.toLocaleString() }}</div>
            <div class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
              <span class="text-emerald-400 font-semibold"><i class="fas fa-arrow-up"></i> Aktif</span>
              <span>tüm zamanlar</span>
            </div>
          </div>
        </div>

        <!-- Tamamlanma Oranı -->
        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex flex-col justify-between">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400">Tamamlama Oranı</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-sm">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
          <div>
            <div class="text-2xl sm:text-3xl font-black text-white">{{ stats.completionRate }}%</div>
            <div class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
              <span class="font-semibold text-white">{{ stats.completedViews.toLocaleString() }}</span>
              <span>tamamlanan video</span>
            </div>
          </div>
        </div>

        <!-- Ortalama İlerleme -->
        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex flex-col justify-between">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400">Ortalama İzleme Süresi</span>
            <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-sm">
              <i class="fas fa-clock"></i>
            </div>
          </div>
          <div>
            <div class="text-2xl sm:text-3xl font-black text-white">%{{ stats.avgProgress }}</div>
            <div class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
              <span>Ortalama izlenen bölüm oranı</span>
            </div>
          </div>
        </div>

        <!-- Tekil İzleyici -->
        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex flex-col justify-between">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-400">Tekil İzleyici</span>
            <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-sm">
              <i class="fas fa-users"></i>
            </div>
          </div>
          <div>
            <div class="text-2xl sm:text-3xl font-black text-white">{{ stats.uniqueViewers.toLocaleString() }}</div>
            <div class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
              <span class="text-blue-400 font-semibold">Aktif hesap</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Trend Chart Section -->
      <div class="p-5 sm:p-6 rounded-2xl bg-white/5 border border-white/10 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div>
            <h2 class="text-base font-bold text-white flex items-center gap-2">
              <i class="fas fa-chart-area text-netflix"></i>
              <span>Günlük İzlenme Eğilimi (Son {{ days }} Gün)</span>
            </h2>
            <p class="text-xs text-gray-400 mt-0.5">Platformda başlatılan video izleme oturumlarının günlük dağılımı</p>
          </div>
          <div class="text-xs text-gray-400">
            En Yüksek Gün: <span class="font-bold text-white">{{ Math.max(...chart.data, 0) }} izleme</span>
          </div>
        </div>

        <!-- SVG Line Chart with Gradient -->
        <div class="relative w-full h-56 pt-4">
          <svg class="w-full h-full overflow-visible" viewBox="0 0 800 200" preserveAspectRatio="none">
            <defs>
              <linearGradient id="areaGradient" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#e50914" stop-opacity="0.35" />
                <stop offset="100%" stop-color="#e50914" stop-opacity="0.0" />
              </linearGradient>
            </defs>

            <!-- Background Grid Lines -->
            <line x1="0" y1="40" x2="800" y2="40" stroke="rgba(255,255,255,0.05)" stroke-dasharray="3 3" />
            <line x1="0" y1="100" x2="800" y2="100" stroke="rgba(255,255,255,0.05)" stroke-dasharray="3 3" />
            <line x1="0" y1="160" x2="800" y2="160" stroke="rgba(255,255,255,0.05)" stroke-dasharray="3 3" />

            <!-- Filled Area -->
            <polygon :points="svgAreaPoints" fill="url(#areaGradient)" />

            <!-- Stroke Path -->
            <polyline :points="svgPolylinePoints" fill="none" stroke="#e50914" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

            <!-- Data Dots -->
            <circle 
              v-for="(pt, idx) in chartPoints" 
              :key="idx" 
              :cx="pt.x" 
              :cy="pt.y" 
              r="4" 
              class="fill-white stroke-netflix stroke-[2] cursor-pointer hover:r-6 transition-all"
            >
              <title>{{ chart.labels[idx] }}: {{ chart.data[idx] }} izleme</title>
            </circle>
          </svg>
        </div>

        <!-- Chart Labels -->
        <div class="flex justify-between text-[10px] text-gray-400 pt-2 border-t border-white/5 overflow-x-auto">
          <span v-for="(lbl, idx) in chart.labels" :key="idx" class="truncate px-1">
            {{ lbl }}
          </span>
        </div>
      </div>

      <!-- Leaderboards Grid (Movies & TV Shows) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top 10 Filmler -->
        <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
              <i class="fas fa-trophy text-amber-400"></i>
              <span>En Çok İzlenen 10 Film</span>
            </h2>
            <span class="text-xs text-gray-400">Tüm Zamanlar</span>
          </div>

          <div v-if="topMovies.length === 0" class="py-12 text-center text-gray-500 text-xs">
            Henüz film izleme kaydı bulunmuyor.
          </div>

          <div v-else class="space-y-2">
            <div 
              v-for="(item, idx) in topMovies" 
              :key="item.id"
              class="flex items-center gap-3 p-2.5 rounded-xl bg-white/[0.02] hover:bg-white/5 border border-white/5 transition"
            >
              <!-- Rank -->
              <div 
                :class="[
                  idx === 0 ? 'bg-amber-400 text-black font-black' :
                  idx === 1 ? 'bg-slate-300 text-black font-black' :
                  idx === 2 ? 'bg-amber-700 text-white font-black' : 'bg-white/10 text-gray-400 font-bold'
                ]"
                class="w-6 h-6 rounded-lg flex items-center justify-center text-xs flex-shrink-0"
              >
                {{ idx + 1 }}
              </div>

              <!-- Poster -->
              <img 
                :src="item.poster_url || '/images/placeholders/poster.png'" 
                :alt="item.title"
                class="w-9 h-12 rounded-md object-cover bg-gray-900 flex-shrink-0"
                loading="lazy"
              />

              <!-- Details -->
              <div class="flex-1 min-w-0">
                <div class="text-xs font-bold text-white truncate">{{ item.title }}</div>
                <div class="text-[10px] text-gray-400 flex items-center gap-2 mt-0.5">
                  <span v-if="item.rating" class="text-amber-400 flex items-center gap-1 font-semibold">
                    <i class="fas fa-star text-[9px]"></i> {{ item.rating.toFixed(1) }}
                  </span>
                  <span>ID #{{ item.id }}</span>
                </div>
              </div>

              <!-- Views Badge -->
              <div class="text-right flex-shrink-0">
                <div class="text-xs font-black text-netflix flex items-center gap-1 justify-end">
                  <i class="fas fa-eye text-[10px]"></i> {{ item.views }}
                </div>
                <div class="text-[9px] text-gray-500">izlenme</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Top 10 Diziler -->
        <div class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <h2 class="text-sm font-bold text-white flex items-center gap-2">
              <i class="fas fa-fire text-netflix"></i>
              <span>En Çok İzlenen 10 Dizi</span>
            </h2>
            <span class="text-xs text-gray-400">Tüm Zamanlar</span>
          </div>

          <div v-if="topShows.length === 0" class="py-12 text-center text-gray-500 text-xs">
            Henüz dizi izleme kaydı bulunmuyor.
          </div>

          <div v-else class="space-y-2">
            <div 
              v-for="(item, idx) in topShows" 
              :key="item.id"
              class="flex items-center gap-3 p-2.5 rounded-xl bg-white/[0.02] hover:bg-white/5 border border-white/5 transition"
            >
              <!-- Rank -->
              <div 
                :class="[
                  idx === 0 ? 'bg-amber-400 text-black font-black' :
                  idx === 1 ? 'bg-slate-300 text-black font-black' :
                  idx === 2 ? 'bg-amber-700 text-white font-black' : 'bg-white/10 text-gray-400 font-bold'
                ]"
                class="w-6 h-6 rounded-lg flex items-center justify-center text-xs flex-shrink-0"
              >
                {{ idx + 1 }}
              </div>

              <!-- Poster -->
              <img 
                :src="item.poster_url || '/images/placeholders/poster.png'" 
                :alt="item.title"
                class="w-9 h-12 rounded-md object-cover bg-gray-900 flex-shrink-0"
                loading="lazy"
              />

              <!-- Details -->
              <div class="flex-1 min-w-0">
                <div class="text-xs font-bold text-white truncate">{{ item.title }}</div>
                <div class="text-[10px] text-gray-400 flex items-center gap-2 mt-0.5">
                  <span v-if="item.rating" class="text-amber-400 flex items-center gap-1 font-semibold">
                    <i class="fas fa-star text-[9px]"></i> {{ item.rating.toFixed(1) }}
                  </span>
                  <span>ID #{{ item.id }}</span>
                </div>
              </div>

              <!-- Views Badge -->
              <div class="text-right flex-shrink-0">
                <div class="text-xs font-black text-purple-400 flex items-center gap-1 justify-end">
                  <i class="fas fa-eye text-[10px]"></i> {{ item.views }}
                </div>
                <div class="text-[9px] text-gray-500">izlenme</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '../Layout.vue';

const props = defineProps({
  days: {
    type: Number,
    default: 14,
  },
  chart: {
    type: Object,
    required: true,
  },
  topMovies: {
    type: Array,
    default: () => [],
  },
  topShows: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    required: true,
  },
});

const changePeriod = (d) => {
  router.get('/admin/analytics', { days: d }, { preserveState: true, preserveScroll: true });
};

// SVG Chart Calculations
const maxVal = computed(() => {
  const max = Math.max(...(props.chart.data || [0]), 5);
  return max === 0 ? 5 : max;
});

const chartPoints = computed(() => {
  const data = props.chart.data || [];
  if (data.length === 0) return [];
  const len = data.length;
  const step = len > 1 ? 800 / (len - 1) : 400;

  return data.map((val, idx) => {
    const x = Math.round(idx * step);
    const normalizedY = 180 - Math.round((val / maxVal.value) * 150);
    return { x, y: normalizedY };
  });
});

const svgPolylinePoints = computed(() => {
  return chartPoints.value.map(p => `${p.x},${p.y}`).join(' ');
});

const svgAreaPoints = computed(() => {
  const pts = chartPoints.value;
  if (pts.length === 0) return '';
  const first = pts[0];
  const last = pts[pts.length - 1];
  return `${first.x},200 ${svgPolylinePoints.value} ${last.x},200`;
});
</script>
