<template>
  <AdminLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-white/10">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <span>Video & Kırık Link Bildirimleri</span>
            <span v-if="statusCounts.pending > 0" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
              {{ statusCounts.pending }} Bekleyen Bildirim
            </span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">İzleyicilerin video oynatıcı üzerinden bildirdiği ölü linkler, ses kaymaları ve video donma şikayetleri.</p>
        </div>
      </div>

      <!-- Status Filter Tabs -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <button 
          @click="setStatusFilter(null)"
          :class="!filters.status ? 'bg-netflix text-white font-bold shadow-md shadow-red-600/30' : 'bg-white/5 text-gray-400 hover:text-white border border-white/10'"
          class="px-3.5 py-2 rounded-xl text-xs transition flex items-center gap-2 whitespace-nowrap"
        >
          <span>Tümü</span>
          <span class="px-1.5 py-0.5 rounded-md text-[10px] bg-black/40">{{ statusCounts.all }}</span>
        </button>

        <button 
          @click="setStatusFilter('pending')"
          :class="filters.status === 'pending' ? 'bg-amber-500 text-black font-bold shadow-md shadow-amber-500/30' : 'bg-white/5 text-gray-400 hover:text-white border border-white/10'"
          class="px-3.5 py-2 rounded-xl text-xs transition flex items-center gap-2 whitespace-nowrap"
        >
          <i class="fas fa-clock"></i>
          <span>Bekleyenler</span>
          <span class="px-1.5 py-0.5 rounded-md text-[10px] bg-black/40">{{ statusCounts.pending }}</span>
        </button>

        <button 
          @click="setStatusFilter('investigating')"
          :class="filters.status === 'investigating' ? 'bg-sky-500 text-white font-bold shadow-md shadow-sky-500/30' : 'bg-white/5 text-gray-400 hover:text-white border border-white/10'"
          class="px-3.5 py-2 rounded-xl text-xs transition flex items-center gap-2 whitespace-nowrap"
        >
          <i class="fas fa-spinner"></i>
          <span>İnceleniyor</span>
          <span class="px-1.5 py-0.5 rounded-md text-[10px] bg-black/40">{{ statusCounts.investigating }}</span>
        </button>

        <button 
          @click="setStatusFilter('resolved')"
          :class="filters.status === 'resolved' ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/30' : 'bg-white/5 text-gray-400 hover:text-white border border-white/10'"
          class="px-3.5 py-2 rounded-xl text-xs transition flex items-center gap-2 whitespace-nowrap"
        >
          <i class="fas fa-check-circle"></i>
          <span>Çözüldü</span>
          <span class="px-1.5 py-0.5 rounded-md text-[10px] bg-black/40">{{ statusCounts.resolved }}</span>
        </button>

        <button 
          @click="setStatusFilter('dismissed')"
          :class="filters.status === 'dismissed' ? 'bg-gray-600 text-white font-bold' : 'bg-white/5 text-gray-400 hover:text-white border border-white/10'"
          class="px-3.5 py-2 rounded-xl text-xs transition flex items-center gap-2 whitespace-nowrap"
        >
          <i class="fas fa-times"></i>
          <span>Gözardı Edildi</span>
          <span class="px-1.5 py-0.5 rounded-md text-[10px] bg-black/40">{{ statusCounts.dismissed }}</span>
        </button>
      </div>

      <!-- Reports List -->
      <div class="rounded-2xl bg-white/5 border border-white/10 overflow-hidden">
        <div v-if="reports.data.length === 0" class="py-16 text-center text-xs text-gray-500">
          <i class="fas fa-flag text-3xl mb-3 text-gray-600"></i>
          <div>Seçili filtrelere uygun bildirim kaydı bulunamadı.</div>
        </div>

        <div v-else class="divide-y divide-white/5">
          <div 
            v-for="rep in reports.data" 
            :key="rep.id"
            class="p-4 hover:bg-white/[0.02] transition flex flex-col md:flex-row md:items-center justify-between gap-4"
          >
            <!-- Left: Media Thumbnail & Report Details -->
            <div class="flex items-start gap-4 min-w-0">
              <!-- Thumbnail -->
              <img 
                :src="rep.media?.poster_url || '/images/placeholders/poster.png'" 
                :alt="rep.media?.title || 'Media'"
                class="w-12 h-16 rounded-xl object-cover bg-gray-900 border border-white/10 flex-shrink-0"
              />

              <!-- Info -->
              <div class="space-y-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <!-- Issue Type Badge -->
                  <span 
                    :class="getIssueBadgeClass(rep.issue_type)"
                    class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider"
                  >
                    {{ getIssueLabel(rep.issue_type) }}
                  </span>

                  <!-- Status Badge -->
                  <span 
                    :class="getStatusBadgeClass(rep.status)"
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border"
                  >
                    {{ getStatusLabel(rep.status) }}
                  </span>

                  <!-- Server Tag -->
                  <span v-if="rep.server_name" class="text-[10px] font-mono text-gray-400 bg-white/5 px-2 py-0.5 rounded">
                    Sunucu: <strong class="text-white">{{ rep.server_name }}</strong>
                  </span>
                </div>

                <!-- Media Title -->
                <div class="text-sm font-bold text-white truncate">
                  <span class="text-netflix uppercase text-[10px] font-black mr-1">[{{ rep.media_type }}]</span>
                  <a 
                    v-if="rep.media" 
                    :href="rep.media_type === 'movie' ? '/movie/' + rep.media.slug : '/tv-show/' + rep.media.slug" 
                    target="_blank" 
                    class="hover:underline hover:text-netflix transition"
                  >
                    {{ rep.media.title }}
                  </a>
                  <span v-else class="text-gray-500">Silinmiş İçerik #{{ rep.media_id }}</span>
                </div>

                <!-- User Note -->
                <p v-if="rep.notes" class="text-xs text-gray-300 bg-black/30 p-2 rounded-lg border border-white/5 max-w-xl">
                  <i class="fas fa-comment-dots text-gray-500 text-[10px] mr-1"></i>
                  <span>"{{ rep.notes }}"</span>
                </p>

                <!-- Reporter & Date -->
                <div class="text-[10px] text-gray-400 flex items-center gap-3 pt-0.5">
                  <span v-if="rep.user" class="flex items-center gap-1">
                    <i class="fas fa-user text-gray-500"></i>
                    <strong class="text-gray-300">{{ rep.user.name }}</strong> ({{ rep.user.email }})
                  </span>
                  <span v-else class="italic text-gray-500">Anonim Ziyaretçi</span>
                  <span>•</span>
                  <span>{{ formatDate(rep.created_at) }}</span>
                </div>
              </div>
            </div>

            <!-- Right: Status Actions -->
            <div class="flex items-center gap-1.5 flex-shrink-0 self-end md:self-center">
              <button 
                v-if="rep.status !== 'resolved'"
                @click="updateStatus(rep.id, 'resolved')"
                class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition flex items-center gap-1.5"
                title="Sorun giderildi olarak işaretle"
              >
                <i class="fas fa-check"></i>
                <span>Çözüldü</span>
              </button>

              <button 
                v-if="rep.status !== 'investigating'"
                @click="updateStatus(rep.id, 'investigating')"
                class="px-3 py-1.5 rounded-xl bg-sky-600/30 hover:bg-sky-600 text-sky-300 hover:text-white border border-sky-500/30 text-xs font-bold transition flex items-center gap-1.5"
                title="İncelemeye al"
              >
                <i class="fas fa-search"></i>
                <span>İncele</span>
              </button>

              <button 
                v-if="rep.status !== 'dismissed'"
                @click="updateStatus(rep.id, 'dismissed')"
                class="px-2.5 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white text-xs transition"
                title="Gözardı et"
              >
                <i class="fas fa-eye-slash"></i>
              </button>

              <button 
                @click="deleteReport(rep.id)"
                class="px-2.5 py-1.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs transition"
                title="Raporu sil"
              >
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="reports.links && reports.links.length > 3" class="p-4 border-t border-white/10 flex items-center justify-between flex-wrap gap-2">
          <div class="text-xs text-gray-400">
            Gösterilen: <strong class="text-white">{{ reports.from || 0 }}</strong> - <strong class="text-white">{{ reports.to || 0 }}</strong> / {{ reports.total }}
          </div>
          <div class="flex items-center gap-1">
            <template v-for="(link, idx) in reports.links" :key="idx">
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
import { router } from '@inertiajs/vue3';
import AdminLayout from '../Layout.vue';

const props = defineProps({
  reports: {
    type: Object,
    required: true,
  },
  statusCounts: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ status: '', issue_type: '' }),
  },
});

const setStatusFilter = (status) => {
  router.get('/admin/reports', {
    status: status || undefined,
    issue_type: props.filters.issue_type || undefined,
  }, { preserveState: true, preserveScroll: true });
};

const updateStatus = (id, status) => {
  router.post(`/admin/reports/${id}/status`, { status }, { preserveScroll: true });
};

const deleteReport = (id) => {
  if (confirm('Bu bildirim kaydını silmek istediğinize emin misiniz?')) {
    router.delete(`/admin/reports/${id}`, { preserveScroll: true });
  }
};

const getIssueLabel = (type) => {
  const map = {
    dead_link: 'Kırık Link / Açılmıyor',
    audio_desync: 'Ses / Görüntü Senkron Hatası',
    wrong_video: 'Yanlış İçerik / Bölüm',
    buffering: 'Aşırı Donma / Yavaş Yükleme',
    other: 'Diğer Problem',
  };
  return map[type] || type;
};

const getIssueBadgeClass = (type) => {
  if (type === 'dead_link') return 'bg-red-500/20 text-red-400 border border-red-500/30';
  if (type === 'audio_desync') return 'bg-amber-500/20 text-amber-400 border border-amber-500/30';
  if (type === 'wrong_video') return 'bg-purple-500/20 text-purple-400 border border-purple-500/30';
  return 'bg-blue-500/20 text-blue-400 border border-blue-500/30';
};

const getStatusLabel = (status) => {
  const map = {
    pending: 'Bekliyor',
    investigating: 'İnceleniyor',
    resolved: 'Çözüldü',
    dismissed: 'Gözardı Edildi',
  };
  return map[status] || status;
};

const getStatusBadgeClass = (status) => {
  if (status === 'resolved') return 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30';
  if (status === 'investigating') return 'bg-sky-500/20 text-sky-400 border-sky-500/30';
  if (status === 'pending') return 'bg-amber-500/20 text-amber-400 border-amber-500/30';
  return 'bg-gray-500/20 text-gray-400 border-gray-500/30';
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
