<template>
  <AdminLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-white/10">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <span>Duyuru & Bildirim Merkezi</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-500/20 text-sky-400 border border-sky-500/30">
              Canlı İletişim
            </span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">Sitede gösterilecek global duyuru bandını yönetin veya kullanıcılara toplu/bireysel anlık bildirimler gönderin.</p>
        </div>

        <div class="text-xs text-gray-400 font-mono bg-white/5 px-3 py-2 rounded-xl border border-white/10 flex items-center gap-2">
          <i class="fas fa-users text-indigo-400"></i>
          <span>Toplam <strong>{{ totalUsers }}</strong> kayıtlı kullanıcı</span>
        </div>
      </div>

      <!-- Grid: 1. Banner Management, 2. In-App Broadcaster -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- 1. Global Announcement Banner -->
        <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-6 flex flex-col justify-between">
          <div class="space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-white/10">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center text-sm">
                  <i class="fas fa-bullhorn"></i>
                </div>
                <div>
                  <h2 class="text-sm font-bold text-white">Global Duyuru Bandı</h2>
                  <p class="text-[11px] text-gray-400">Tüm ziyaretçilerin sitenin en üstünde göreceği duyuru mesajı</p>
                </div>
              </div>

              <!-- Status Badge -->
              <span 
                :class="bannerForm.enabled ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-gray-500/20 text-gray-400 border-gray-500/30'"
                class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase border"
              >
                {{ bannerForm.enabled ? 'Aktif' : 'Pasif' }}
              </span>
            </div>

            <!-- Live Banner Preview -->
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-gray-400">Canlı Önizleme</label>
              <div 
                v-if="bannerForm.enabled && bannerForm.text"
                :class="[
                  bannerForm.type === 'info' ? 'bg-sky-600 text-white' :
                  bannerForm.type === 'warning' ? 'bg-amber-500 text-black' :
                  bannerForm.type === 'danger' ? 'bg-netflix text-white' :
                  'bg-emerald-600 text-white'
                ]"
                class="px-4 py-2.5 rounded-xl text-xs font-bold shadow-lg flex items-center justify-between gap-3 transition-all"
              >
                <div class="flex items-center gap-2 truncate">
                  <i :class="[
                    bannerForm.type === 'info' ? 'fas fa-info-circle' :
                    bannerForm.type === 'warning' ? 'fas fa-exclamation-triangle' :
                    bannerForm.type === 'danger' ? 'fas fa-shield-alt' :
                    'fas fa-check-circle'
                  ]"></i>
                  <span class="truncate">{{ bannerForm.text }}</span>
                </div>
                <span v-if="bannerForm.link" class="text-[10px] underline font-black uppercase tracking-wider flex-shrink-0">
                  İncele &rarr;
                </span>
              </div>
              <div v-else class="p-3 rounded-xl bg-black/40 border border-white/5 text-gray-500 text-xs text-center">
                Duyuru bandı şu anda devre dışı veya metin boş.
              </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="saveBanner" class="space-y-4">
              <!-- Active Toggle -->
              <div class="flex items-center justify-between p-3 rounded-xl bg-black/30 border border-white/5">
                <div>
                  <div class="text-xs font-bold text-white">Duyuru Bandını Yayınla</div>
                  <div class="text-[10px] text-gray-400">Aktif olduğunda site üstünde otomatik görünür</div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="bannerForm.enabled" class="sr-only peer" />
                  <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-netflix"></div>
                </label>
              </div>

              <!-- Banner Type -->
              <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400">Duyuru Tipi / Renk</label>
                <div class="grid grid-cols-4 gap-2">
                  <button 
                    type="button"
                    v-for="t in [
                      { id: 'info', label: 'Bilgi', class: 'border-sky-500 text-sky-400' },
                      { id: 'warning', label: 'Uyarı', class: 'border-amber-500 text-amber-400' },
                      { id: 'danger', label: 'Önemli', class: 'border-red-500 text-red-400' },
                      { id: 'success', label: 'Başarı', class: 'border-emerald-500 text-emerald-400' },
                    ]"
                    :key="t.id"
                    @click="bannerForm.type = t.id"
                    :class="[
                      bannerForm.type === t.id ? 'bg-white/10 ' + t.class + ' font-bold ring-1' : 'bg-white/5 border-white/10 text-gray-400'
                    ]"
                    class="py-2 rounded-xl text-xs border transition text-center"
                  >
                    {{ t.label }}
                  </button>
                </div>
              </div>

              <!-- Message -->
              <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400">Duyuru Metni</label>
                <textarea 
                  v-model="bannerForm.text"
                  rows="3"
                  class="w-full px-3.5 py-2.5 rounded-xl bg-black/40 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix placeholder-gray-600 transition"
                  placeholder="Örn: Hafta sonu bakım çalışması saat 03:00'te tamamlanacaktır. Yeni 4K sunucularımız eklendi!"
                ></textarea>
              </div>

              <!-- Link -->
              <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400">Tıklama Yönlendirme Linki (İsteğe Bağlı)</label>
                <input 
                  type="text" 
                  v-model="bannerForm.link"
                  class="w-full px-3.5 py-2 rounded-xl bg-black/40 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix placeholder-gray-600 transition font-mono"
                  placeholder="Örn: /movies veya https://..."
                />
              </div>

              <div class="pt-2">
                <button 
                  type="submit"
                  :disabled="bannerSaving"
                  class="w-full py-2.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg shadow-red-600/30 flex items-center justify-center gap-2"
                >
                  <i :class="bannerSaving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
                  <span>Duyuru Ayarlarını Kaydet</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- 2. Notification Center Broadcaster -->
        <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-6 flex flex-col justify-between">
          <div class="space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-white/10">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-sm">
                  <i class="fas fa-bell"></i>
                </div>
                <div>
                  <h2 class="text-sm font-bold text-white">Uygulama İçi Bildirim Gönder</h2>
                  <p class="text-[11px] text-gray-400">Kullanıcıların bildirim çanında belirecek anlık bildirim oluşturun</p>
                </div>
              </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="sendNotification" class="space-y-4">
              <!-- Recipient Selector -->
              <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400">Hedef Kitle</label>
                <div class="grid grid-cols-2 gap-2">
                  <button 
                    type="button"
                    @click="broadcastForm.target = 'all'"
                    :class="broadcastForm.target === 'all' ? 'bg-netflix text-white font-bold shadow-md shadow-red-600/30 border-transparent' : 'bg-white/5 text-gray-400 border-white/10'"
                    class="py-2 rounded-xl text-xs border transition flex items-center justify-center gap-2"
                  >
                    <i class="fas fa-globe"></i>
                    <span>Tüm Kullanıcılar ({{ totalUsers }})</span>
                  </button>

                  <button 
                    type="button"
                    @click="broadcastForm.target = users[0]?.id || '1'"
                    :class="broadcastForm.target !== 'all' ? 'bg-netflix text-white font-bold shadow-md shadow-red-600/30 border-transparent' : 'bg-white/5 text-gray-400 border-white/10'"
                    class="py-2 rounded-xl text-xs border transition flex items-center justify-center gap-2"
                  >
                    <i class="fas fa-user"></i>
                    <span>Belirli Bir Kullanıcı</span>
                  </button>
                </div>

                <!-- Single User Picker -->
                <div v-if="broadcastForm.target !== 'all'" class="pt-2">
                  <select 
                    v-model="broadcastForm.target"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-black/40 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix"
                  >
                    <option v-for="u in users" :key="u.id" :value="u.id">
                      {{ u.name }} ({{ u.email }})
                    </option>
                  </select>
                </div>
              </div>

              <!-- Notification Type -->
              <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400">Bildirim Kategorisi</label>
                <select 
                  v-model="broadcastForm.type"
                  class="w-full px-3.5 py-2.5 rounded-xl bg-black/40 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix"
                >
                  <option value="system">Sistem & Güvenlik Duyurusu</option>
                  <option value="release">Yeni Film / Dizi / Sezon Yayını</option>
                  <option value="review">İnceleme & Topluluk Etkileşimi</option>
                  <option value="like">Beğeni / Öneri</option>
                </select>
              </div>

              <!-- Title -->
              <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400">Bildirim Başlığı</label>
                <input 
                  type="text" 
                  v-model="broadcastForm.title"
                  class="w-full px-3.5 py-2 rounded-xl bg-black/40 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix placeholder-gray-600 transition"
                  placeholder="Örn: Yeni 4K Film Eklendi: Dune: Part Two"
                  required
                />
              </div>

              <!-- Body -->
              <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400">Bildirim İçeriği</label>
                <textarea 
                  v-model="broadcastForm.body"
                  rows="3"
                  class="w-full px-3.5 py-2.5 rounded-xl bg-black/40 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix placeholder-gray-600 transition"
                  placeholder="Örn: Beklediğiniz film şimdi Türkçe Dublaj ve Altyazı seçenekleriyle yayında!"
                  required
                ></textarea>
              </div>

              <!-- Action Link -->
              <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400">Tıklama Yolu (URL)</label>
                <input 
                  type="text" 
                  v-model="broadcastForm.link"
                  class="w-full px-3.5 py-2 rounded-xl bg-black/40 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix placeholder-gray-600 transition font-mono"
                  placeholder="Örn: /movie/dune-part-two"
                />
              </div>

              <div class="pt-2">
                <button 
                  type="submit"
                  :disabled="broadcastSending"
                  class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-black text-xs font-bold transition shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2"
                >
                  <i :class="broadcastSending ? 'fas fa-spinner fa-spin' : 'fas fa-paper-plane'"></i>
                  <span>Bildirimi Gönder</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Recent Notifications Log -->
      <div class="p-6 rounded-2xl bg-white/5 border border-white/10 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-white/10">
          <h2 class="text-sm font-bold text-white flex items-center gap-2">
            <i class="fas fa-history text-gray-400"></i>
            <span>Son Gönderilen Uygulama İçi Bildirimler (Son 15)</span>
          </h2>
        </div>

        <div v-if="recentNotifications.length === 0" class="py-8 text-center text-xs text-gray-500">
          Henüz gönderilmiş bir bildirim bulunmuyor.
        </div>

        <div v-else class="space-y-2">
          <div 
            v-for="item in recentNotifications" 
            :key="item.id"
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-xl bg-white/[0.02] border border-white/5 hover:border-white/15 transition"
          >
            <div class="flex items-center gap-3 min-w-0">
              <div 
                :class="[
                  item.type === 'system' ? 'bg-sky-500/10 text-sky-400' :
                  item.type === 'release' ? 'bg-emerald-500/10 text-emerald-400' :
                  'bg-purple-500/10 text-purple-400'
                ]"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-xs flex-shrink-0"
              >
                <i :class="item.type === 'system' ? 'fas fa-shield-alt' : item.type === 'release' ? 'fas fa-film' : 'fas fa-bell'"></i>
              </div>
              <div class="min-w-0">
                <div class="text-xs font-bold text-white truncate">{{ item.title }}</div>
                <div class="text-[11px] text-gray-400 truncate">{{ item.body }}</div>
              </div>
            </div>

            <div class="flex items-center gap-4 text-[10px] text-gray-400 flex-shrink-0 self-end sm:self-auto">
              <span class="flex items-center gap-1">
                <i class="fas fa-user text-gray-500"></i>
                <span class="text-gray-300">{{ item.user ? item.user.name : 'Tüm Kullanıcılar' }}</span>
              </span>
              <span>{{ new Date(item.created_at).toLocaleDateString('tr-TR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '../Layout.vue';

const props = defineProps({
  announcement: {
    type: Object,
    required: true,
  },
  recentNotifications: {
    type: Array,
    default: () => [],
  },
  totalUsers: {
    type: Number,
    default: 0,
  },
  users: {
    type: Array,
    default: () => [],
  },
});

const bannerForm = reactive({
  enabled: props.announcement.enabled,
  text: props.announcement.text,
  type: props.announcement.type || 'info',
  link: props.announcement.link || '',
});

const bannerSaving = ref(false);

const saveBanner = () => {
  bannerSaving.value = true;
  router.post('/admin/announcements/banner', bannerForm, {
    preserveScroll: true,
    onFinish: () => {
      bannerSaving.value = false;
    },
  });
};

const broadcastForm = reactive({
  target: 'all',
  type: 'system',
  title: '',
  body: '',
  link: '',
});

const broadcastSending = ref(false);

const sendNotification = () => {
  broadcastSending.value = true;
  router.post('/admin/announcements/broadcast', broadcastForm, {
    preserveScroll: true,
    onFinish: () => {
      broadcastSending.value = false;
      broadcastForm.title = '';
      broadcastForm.body = '';
      broadcastForm.link = '';
    },
  });
};
</script>
