<template>
  <AdminLayout title="Reklam & Monetizasyon Merkezi">
    <div class="space-y-8 max-w-7xl mx-auto pb-12">
      <!-- 1. Header Banner -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 rounded-3xl bg-gradient-to-r from-gray-900 via-black to-red-950/40 border border-white/10 shadow-2xl relative overflow-hidden">
        <div class="space-y-1 z-10">
          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-400 border border-amber-500/30">
              AD ENGINE v2.0
            </span>
            <span :class="settingsForm.ads_enabled ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-red-500/20 text-red-400 border-red-500/30'" class="px-2 py-0.5 rounded-full text-[10px] font-bold border">
              {{ settingsForm.ads_enabled ? 'Sistem Aktif' : 'Reklamlar Durduruldu' }}
            </span>
          </div>
          <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
            <i class="fas fa-ad text-amber-400"></i>
            <span>Reklam & Monetizasyon Yönetimi</span>
          </h1>
          <p class="text-xs text-gray-400 max-w-2xl">
            Google AdSense, doğrudan sponsorluk banner'ları, VAST video reklamları ve gelişmiş AdBlock algılayıcı sistemini tek merkezden yapılandırın.
          </p>
        </div>

        <!-- Reset Button -->
        <div class="flex items-center gap-2 z-10 shrink-0">
          <button 
            @click="resetDefaults" 
            :disabled="resetting"
            class="px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white text-xs font-bold border border-white/10 transition flex items-center gap-2"
          >
            <i :class="resetting ? 'fas fa-spinner fa-spin' : 'fas fa-undo'"></i>
            <span>Varsayılanları Geri Yükle</span>
          </button>
        </div>

        <div class="absolute -right-16 -top-16 w-56 h-56 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
      </div>

      <!-- 2. Global Engine & AdBlock Controls Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card A: Global Ad Engine Settings -->
        <div class="p-6 rounded-3xl bg-white/[0.03] border border-white/10 space-y-5">
          <div class="flex items-center justify-between border-b border-white/5 pb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center font-bold">
                <i class="fas fa-globe"></i>
              </div>
              <div>
                <h3 class="text-sm font-bold text-white">Genel Reklam Ayarları</h3>
                <p class="text-[11px] text-gray-400">Tüm platform çapında reklam yayını ve Google kimliği</p>
              </div>
            </div>

            <!-- Global Master Switch -->
            <button 
              type="button" 
              @click="settingsForm.ads_enabled = !settingsForm.ads_enabled"
              :class="settingsForm.ads_enabled ? 'bg-emerald-600' : 'bg-gray-700'"
              class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
            >
              <span 
                :class="settingsForm.ads_enabled ? 'translate-x-5' : 'translate-x-0'" 
                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
              />
            </button>
          </div>

          <div class="space-y-4 text-xs">
            <div>
              <label class="block text-gray-300 font-bold mb-1">Varsayılan Google AdSense Client ID</label>
              <input 
                v-model="settingsForm.global_ad_client" 
                type="text" 
                placeholder="ca-pub-XXXXXXXXXXXXXXXX" 
                class="w-full px-3 py-2.5 rounded-xl bg-black/60 border border-white/10 text-white placeholder-gray-500 font-mono text-xs focus:outline-none focus:border-amber-500"
              />
              <p class="text-[10px] text-gray-500 mt-1">Her reklam birimi için ayrıca girilmezse bu ID kullanılır.</p>
            </div>

            <div class="pt-2">
              <button 
                type="button" 
                @click="saveSettings" 
                :disabled="savingSettings"
                class="w-full py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-black font-extrabold text-xs shadow-lg transition flex items-center justify-center gap-2"
              >
                <i :class="savingSettings ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
                <span>Ayarları Kaydet</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Card B: AdBlock Detector Control -->
        <div class="p-6 rounded-3xl bg-white/[0.03] border border-white/10 space-y-5">
          <div class="flex items-center justify-between border-b border-white/5 pb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-red-600/10 text-red-400 border border-red-500/20 flex items-center justify-center font-bold">
                <i class="fas fa-shield-virus"></i>
              </div>
              <div>
                <h3 class="text-sm font-bold text-white">AdBlock (Reklam Engelleyici) Algılayıcı</h3>
                <p class="text-[11px] text-gray-400">uBlock, AdBlock, Brave vb. için çift doğrulamalı tespit</p>
              </div>
            </div>

            <!-- Adblock Switch -->
            <button 
              type="button" 
              @click="settingsForm.adblock_detector_enabled = !settingsForm.adblock_detector_enabled"
              :class="settingsForm.adblock_detector_enabled ? 'bg-red-600' : 'bg-gray-700'"
              class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
            >
              <span 
                :class="settingsForm.adblock_detector_enabled ? 'translate-x-5' : 'translate-x-0'" 
                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
              />
            </button>
          </div>

          <div class="space-y-4 text-xs">
            <!-- Strict Mode Toggle -->
            <div class="flex items-center justify-between p-3 rounded-xl bg-black/40 border border-white/5">
              <div>
                <div class="text-xs font-bold text-white">Katı Mod (Strict Enforcement)</div>
                <div class="text-[10px] text-gray-400">Açıkken reklam engelleyici kapatılana kadar izleme durdurulur.</div>
              </div>
              <button 
                type="button" 
                @click="settingsForm.adblock_strict_mode = !settingsForm.adblock_strict_mode"
                :class="settingsForm.adblock_strict_mode ? 'bg-red-600' : 'bg-gray-700'"
                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out"
              >
                <span 
                  :class="settingsForm.adblock_strict_mode ? 'translate-x-4' : 'translate-x-0'" 
                  class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                />
              </button>
            </div>

            <div>
              <label class="block text-gray-300 font-bold mb-1">Uyarı Başlığı</label>
              <input 
                v-model="settingsForm.adblock_title" 
                type="text" 
                class="w-full px-3 py-2 rounded-xl bg-black/60 border border-white/10 text-white text-xs focus:outline-none focus:border-red-500"
              />
            </div>

            <div>
              <label class="block text-gray-300 font-bold mb-1">Uyarı Mesajı</label>
              <textarea 
                v-model="settingsForm.adblock_message" 
                rows="2"
                class="w-full px-3 py-2 rounded-xl bg-black/60 border border-white/10 text-white text-xs focus:outline-none focus:border-red-500"
              ></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Placement Tabs & Management -->
      <div class="p-6 rounded-3xl bg-white/[0.02] border border-white/10 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-4">
          <div>
            <h2 class="text-base font-bold text-white flex items-center gap-2">
              <i class="fas fa-layer-group text-red-500"></i>
              <span>Reklam Yerleşimleri (7 Stratejik Bölge)</span>
            </h2>
            <p class="text-xs text-gray-400">Her bölgeyi tek tek açıp kapatabilir, kod ve cihaz hedeflemesini belirleyebilirsiniz.</p>
          </div>

          <!-- Placement Quick Filter / Pills -->
          <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
            <button 
              v-for="(p, idx) in placements" 
              :key="p.id"
              @click="activePlacementIdx = idx"
              :class="activePlacementIdx === idx ? 'bg-red-600 text-white font-bold shadow-md shadow-red-600/30' : 'bg-white/5 text-gray-400 hover:text-white'"
              class="px-3 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5 shrink-0"
            >
              <span :class="p.is_active ? 'bg-emerald-400' : 'bg-gray-500'" class="w-1.5 h-1.5 rounded-full"></span>
              <span>{{ p.name.split('(')[0].trim() }}</span>
            </button>
          </div>
        </div>

        <!-- Selected Placement Editor Form -->
        <div v-if="currentPlacement" class="space-y-6">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 rounded-2xl bg-black/40 border border-white/5">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-white">{{ currentPlacement.name }}</h3>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-white/10 text-gray-300">
                  {{ currentPlacement.slug }}
                </span>
              </div>
              <p class="text-xs text-gray-400">{{ currentPlacement.description }}</p>
            </div>

            <!-- Active Toggle -->
            <div class="flex items-center gap-3 shrink-0">
              <span class="text-xs font-semibold" :class="placementForm.is_active ? 'text-emerald-400' : 'text-gray-500'">
                {{ placementForm.is_active ? 'Bu Alan Yayında' : 'Bu Alan Kapalı' }}
              </span>
              <button 
                type="button" 
                @click="placementForm.is_active = !placementForm.is_active"
                :class="placementForm.is_active ? 'bg-emerald-600' : 'bg-gray-700'"
                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out"
              >
                <span 
                  :class="placementForm.is_active ? 'translate-x-5' : 'translate-x-0'" 
                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
                />
              </button>
            </div>
          </div>

          <!-- Placement Configuration Grid -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <!-- Reklam Türü -->
            <div>
              <label class="block text-gray-300 font-bold mb-1">Reklam Türü</label>
              <select 
                v-model="placementForm.type"
                class="w-full px-3 py-2.5 rounded-xl bg-black/60 border border-white/10 text-white text-xs focus:outline-none focus:border-red-500"
              >
                <option value="adsense">Google AdSense (Responsive)</option>
                <option value="custom_code">Özel HTML / JavaScript (Adsterra, PropellerAds vb.)</option>
                <option value="banner_image">Görsel Sponsorluk (Doğrudan Banner)</option>
              </select>
            </div>

            <!-- Cihaz Hedefleme -->
            <div>
              <label class="block text-gray-300 font-bold mb-1">Cihaz Hedefleme</label>
              <select 
                v-model="placementForm.device_target"
                class="w-full px-3 py-2.5 rounded-xl bg-black/60 border border-white/10 text-white text-xs focus:outline-none focus:border-red-500"
              >
                <option value="all">Tüm Cihazlar (Masaüstü + Mobil)</option>
                <option value="desktop">Yalnızca Masaüstü & Geniş Ekran</option>
                <option value="mobile">Yalnızca Mobil Cihazlar</option>
              </select>
            </div>

            <!-- Popunder / Sıklık -->
            <div>
              <label class="block text-gray-300 font-bold mb-1">Frekans Sınırı (Dakika)</label>
              <input 
                v-model.number="placementForm.frequency_minutes"
                type="number"
                min="0"
                max="1440"
                placeholder="0 = Sürekli göster" 
                class="w-full px-3 py-2.5 rounded-xl bg-black/60 border border-white/10 text-white text-xs focus:outline-none focus:border-red-500"
              />
            </div>
          </div>

          <!-- Conditional Fields Based on Type -->
          <!-- 1. Google AdSense Inputs -->
          <div v-if="placementForm.type === 'adsense'" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs p-4 rounded-2xl bg-white/[0.02] border border-white/5">
            <div>
              <label class="block text-gray-300 font-bold mb-1">Özel AdSense Client ID (Opsiyonel)</label>
              <input 
                v-model="placementForm.ad_client"
                type="text"
                placeholder="Boş bırakılırsa genel ID kullanılır" 
                class="w-full px-3 py-2.5 rounded-xl bg-black/60 border border-white/10 text-white font-mono text-xs focus:outline-none focus:border-amber-500"
              />
            </div>
            <div>
              <label class="block text-gray-300 font-bold mb-1">AdSense Ad Slot ID (data-ad-slot)</label>
              <input 
                v-model="placementForm.ad_slot"
                type="text"
                placeholder="Örn: 8492049182" 
                class="w-full px-3 py-2.5 rounded-xl bg-black/60 border border-white/10 text-white font-mono text-xs focus:outline-none focus:border-amber-500"
              />
            </div>
          </div>

          <!-- 2. Custom Code Input -->
          <div v-else-if="placementForm.type === 'custom_code'" class="text-xs p-4 rounded-2xl bg-white/[0.02] border border-white/5 space-y-2">
            <label class="block text-gray-300 font-bold">Özel Reklam Kodu (HTML / Script / iframe)</label>
            <textarea 
              v-model="placementForm.code"
              rows="4"
              placeholder="<script async src='...'></script><ins ...></ins>"
              class="w-full px-3 py-2 rounded-xl bg-black/80 border border-white/10 text-emerald-400 font-mono text-xs focus:outline-none focus:border-emerald-500"
            ></textarea>
            <p class="text-[10px] text-gray-500">
              * Script etiketleri SPA sayfa geçişlerinde dinamik ve güvenli olarak yeniden enjekte edilir.
            </p>
          </div>

          <!-- 3. Banner Image Inputs -->
          <div v-else-if="placementForm.type === 'banner_image'" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs p-4 rounded-2xl bg-white/[0.02] border border-white/5">
            <div>
              <label class="block text-gray-300 font-bold mb-1">Banner Görsel URL'si</label>
              <input 
                v-model="placementForm.image_url"
                type="text"
                placeholder="https://example.com/banner.png veya /images/..." 
                class="w-full px-3 py-2.5 rounded-xl bg-black/60 border border-white/10 text-white text-xs focus:outline-none focus:border-red-500"
              />
            </div>
            <div>
              <label class="block text-gray-300 font-bold mb-1">Tıklama Yönlendirme Linki (Target URL)</label>
              <input 
                v-model="placementForm.target_url"
                type="text"
                placeholder="https://sponsor-site.com" 
                class="w-full px-3 py-2.5 rounded-xl bg-black/60 border border-white/10 text-white text-xs focus:outline-none focus:border-red-500"
              />
            </div>
          </div>

          <!-- Live Preview Box -->
          <div class="space-y-2">
            <div class="text-xs font-bold text-gray-400 flex items-center gap-1.5">
              <i class="fas fa-eye text-blue-400"></i>
              <span>Canlı Yerleşim Önizlemesi</span>
            </div>

            <div class="p-4 rounded-2xl bg-black/80 border border-white/10 flex flex-col items-center justify-center min-h-[110px] text-center">
              <div v-if="placementForm.type === 'banner_image' && placementForm.image_url" class="max-w-md mx-auto">
                <img :src="placementForm.image_url" alt="Banner Önizleme" class="max-h-24 rounded-lg object-contain" />
              </div>
              <div v-else class="flex flex-col items-center gap-1.5 text-gray-400">
                <div class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-xs text-amber-400">
                  <i class="fas fa-ad"></i>
                </div>
                <div class="text-xs font-bold text-white">
                  {{ placementForm.name }}
                </div>
                <div class="text-[10px] text-gray-500">
                  {{ placementForm.is_active ? 'Yayında (Aktif)' : 'Devre Dışı (Pasif)' }} • {{ placementForm.type }} • {{ placementForm.device_target }}
                </div>
              </div>
            </div>
          </div>

          <!-- Save Placement Button -->
          <div class="flex justify-end pt-2">
            <button 
              type="button" 
              @click="savePlacement" 
              :disabled="savingPlacement"
              class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-bold text-xs shadow-lg shadow-red-600/30 transition flex items-center gap-2"
            >
              <i :class="savingPlacement ? 'fas fa-spinner fa-spin' : 'fas fa-check'"></i>
              <span>Bu Alanı Kaydet & Güncelle</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Pages/Admin/Layout.vue';

const props = defineProps({
  placements: {
    type: Array,
    default: () => [],
  },
  settings: {
    type: Object,
    default: () => ({}),
  },
});

const activePlacementIdx = ref(0);
const savingSettings = ref(false);
const savingPlacement = ref(false);
const resetting = ref(false);

const settingsForm = ref({
  ads_enabled: Boolean(props.settings.ads_enabled),
  global_ad_client: props.settings.global_ad_client || '',
  adblock_detector_enabled: Boolean(props.settings.adblock_detector_enabled),
  adblock_strict_mode: Boolean(props.settings.adblock_strict_mode),
  adblock_title: props.settings.adblock_title || 'Reklam Engelleyici Algılandı',
  adblock_message: props.settings.adblock_message || '',
});

const currentPlacement = computed(() => {
  return props.placements[activePlacementIdx.value] || null;
});

const placementForm = ref({
  name: '',
  description: '',
  type: 'adsense',
  is_active: false,
  ad_client: '',
  ad_slot: '',
  code: '',
  image_url: '',
  target_url: '',
  device_target: 'all',
  frequency_minutes: 0,
});

const syncCurrentPlacement = () => {
  if (currentPlacement.value) {
    placementForm.value = {
      name: currentPlacement.value.name,
      description: currentPlacement.value.description,
      type: currentPlacement.value.type || 'adsense',
      is_active: Boolean(currentPlacement.value.is_active),
      ad_client: currentPlacement.value.ad_client || '',
      ad_slot: currentPlacement.value.ad_slot || '',
      code: currentPlacement.value.code || '',
      image_url: currentPlacement.value.image_url || '',
      target_url: currentPlacement.value.target_url || '',
      device_target: currentPlacement.value.device_target || 'all',
      frequency_minutes: currentPlacement.value.frequency_minutes || 0,
    };
  }
};

watch(activePlacementIdx, () => {
  syncCurrentPlacement();
}, { immediate: true });

const saveSettings = () => {
  savingSettings.value = true;
  router.post('/admin/ads/settings', settingsForm.value, {
    preserveScroll: true,
    onFinish: () => {
      savingSettings.value = false;
    },
  });
};

const savePlacement = () => {
  if (!currentPlacement.value) return;
  savingPlacement.value = true;

  router.post(`/admin/ads/placements/${currentPlacement.value.id}`, placementForm.value, {
    preserveScroll: true,
    onFinish: () => {
      savingPlacement.value = false;
    },
  });
};

const resetDefaults = () => {
  if (!confirm('Varsayılan reklam yerleşimlerini geri yüklemek istediğinize emin misiniz?')) return;
  resetting.value = true;
  router.post('/admin/ads/reset', {}, {
    preserveScroll: true,
    onFinish: () => {
      resetting.value = false;
    },
  });
};
</script>