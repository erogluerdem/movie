<template>
  <AdminLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-white/10">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <span>Güvenlik & Denetim Logları</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-500/20 text-purple-400 border border-purple-500/30">
              Audit Trail
            </span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">Yönetici eylemleri, içerik değişiklikleri, toplu işlemler ve hesap güvenlik hareketlerinin kaydı.</p>
        </div>

        <div class="text-xs text-gray-400 font-mono bg-white/5 px-3 py-2 rounded-xl border border-white/10">
          Toplam <strong>{{ logs.total }}</strong> log kaydı
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Search -->
        <div class="relative w-full sm:w-80">
          <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
          <input 
            type="text" 
            v-model="search"
            @keyup.enter="applyFilters"
            placeholder="Açıklama, IP veya kullanıcı ara..."
            class="w-full pl-9 pr-4 py-2 rounded-xl bg-black/40 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix placeholder-gray-500"
          />
        </div>

        <!-- Action Filter & Reset -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <select 
            v-model="actionFilter"
            @change="applyFilters"
            class="px-3 py-2 rounded-xl bg-black/40 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix"
          >
            <option value="">Tüm Eylemler</option>
            <option v-for="act in actions" :key="act" :value="act">{{ act }}</option>
          </select>

          <button 
            @click="applyFilters" 
            class="px-3 py-2 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition"
          >
            Filtrele
          </button>

          <button 
            v-if="search || actionFilter"
            @click="resetFilters" 
            class="px-3 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white text-xs transition"
          >
            Sıfırla
          </button>
        </div>
      </div>

      <!-- Logs Table -->
      <div class="rounded-2xl bg-white/5 border border-white/10 overflow-hidden">
        <div v-if="logs.data.length === 0" class="py-16 text-center text-xs text-gray-500">
          <i class="fas fa-shield-alt text-3xl mb-3 text-gray-600"></i>
          <div>Kayıtlı denetim logu bulunamadı.</div>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-white/10 bg-black/30 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                <th class="py-3 px-4">Eylem</th>
                <th class="py-3 px-4">Açıklama</th>
                <th class="py-3 px-4">Kullanıcı</th>
                <th class="py-3 px-4">IP & Cihaz</th>
                <th class="py-3 px-4 text-right">Tarih</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-xs">
              <tr 
                v-for="log in logs.data" 
                :key="log.id"
                class="hover:bg-white/[0.02] transition"
              >
                <!-- Action Badge -->
                <td class="py-3 px-4 whitespace-nowrap">
                  <span 
                    :class="getActionBadgeClass(log.action)"
                    class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider"
                  >
                    {{ log.action }}
                  </span>
                </td>

                <!-- Description -->
                <td class="py-3 px-4 text-gray-200">
                  <div class="font-medium line-clamp-2 max-w-md">{{ log.description }}</div>
                  <div v-if="log.model_type" class="text-[10px] text-gray-500 font-mono mt-0.5">
                    {{ log.model_type.split('\\').pop() }} #{{ log.model_id }}
                  </div>
                </td>

                <!-- User -->
                <td class="py-3 px-4 whitespace-nowrap">
                  <div v-if="log.user" class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-red-600/30 text-red-400 flex items-center justify-center font-bold text-[10px]">
                      {{ log.user.name.charAt(0) }}
                    </div>
                    <div>
                      <div class="font-bold text-white text-xs">{{ log.user.name }}</div>
                      <div class="text-[10px] text-gray-400">{{ log.user.email }}</div>
                    </div>
                  </div>
                  <span v-else class="text-gray-500 italic text-[11px]">Sistem / Ziyaretçi</span>
                </td>

                <!-- IP & User Agent -->
                <td class="py-3 px-4 whitespace-nowrap">
                  <div class="font-mono text-[11px] text-gray-300 flex items-center gap-1.5">
                    <i class="fas fa-network-wired text-[10px] text-gray-500"></i>
                    <span>{{ log.ip_address || '127.0.0.1' }}</span>
                  </div>
                  <div class="text-[10px] text-gray-500 truncate max-w-[180px]" :title="log.user_agent">
                    {{ formatUserAgent(log.user_agent) }}
                  </div>
                </td>

                <!-- Date -->
                <td class="py-3 px-4 text-right whitespace-nowrap text-gray-400 font-mono text-[11px]">
                  {{ formatDate(log.created_at) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="logs.links && logs.links.length > 3" class="p-4 border-t border-white/10 flex items-center justify-between flex-wrap gap-2">
          <div class="text-xs text-gray-400">
            Gösterilen: <strong class="text-white">{{ logs.from || 0 }}</strong> - <strong class="text-white">{{ logs.to || 0 }}</strong> / {{ logs.total }}
          </div>
          <div class="flex items-center gap-1">
            <template v-for="(link, idx) in logs.links" :key="idx">
              <button 
                v-if="link.url"
                @click="router.get(link.url, {}, { preserveState: true, preserveScroll: true })"
                :class="link.active ? 'bg-netflix text-white font-bold' : 'bg-white/5 text-gray-400 hover:text-white'"
                class="px-3 py-1.5 rounded-lg text-xs transition"
                v-html="link.label"
              ></button>
              <span v-else class="px-2 py-1.5 text-xs text-gray-600" v-html="link.label"></span>
            </template>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '../Layout.vue';

const props = defineProps({
  logs: {
    type: Object,
    required: true,
  },
  actions: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({ search: '', action: '' }),
  },
});

const search = ref(props.filters.search || '');
const actionFilter = ref(props.filters.action || '');

const applyFilters = () => {
  router.get('/admin/logs', {
    search: search.value || undefined,
    action: actionFilter.value || undefined,
  }, { preserveState: true, preserveScroll: true });
};

const resetFilters = () => {
  search.value = '';
  actionFilter.value = '';
  router.get('/admin/logs');
};

const getActionBadgeClass = (action) => {
  const act = (action || '').toLowerCase();
  if (act.includes('delete') || act.includes('ban') || act.includes('remove')) {
    return 'bg-red-500/20 text-red-400 border border-red-500/30';
  }
  if (act.includes('create') || act.includes('add') || act.includes('import') || act.includes('unban')) {
    return 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
  }
  if (act.includes('update') || act.includes('toggle') || act.includes('edit')) {
    return 'bg-amber-500/20 text-amber-400 border border-amber-500/30';
  }
  return 'bg-blue-500/20 text-blue-400 border border-blue-500/30';
};

const formatUserAgent = (ua) => {
  if (!ua) return 'Bilinmiyor';
  if (ua.includes('Chrome')) return 'Chrome Browser';
  if (ua.includes('Firefox')) return 'Firefox';
  if (ua.includes('Safari')) return 'Safari';
  if (ua.includes('Edge')) return 'Microsoft Edge';
  return ua.substring(0, 25) + '...';
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('tr-TR', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>
