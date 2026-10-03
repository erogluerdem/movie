<template>
  <AdminLayout>
    <div class="space-y-8 max-w-4xl">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-white/10">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <i class="fas fa-sliders-h text-netflix"></i>
            <span>Site Settings</span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">Configure global platform branding, top announcements, and API integrations.</p>
        </div>

        <button 
          @click="clearCache" 
          :disabled="clearingCache"
          class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 text-xs font-bold transition border border-white/10 flex items-center gap-2"
        >
          <i :class="clearingCache ? 'fas fa-spinner fa-spin' : 'fas fa-broom'" class="text-amber-400"></i>
          <span>Clear System Cache</span>
        </button>
      </div>

      <form @submit.prevent="submitSettings" class="space-y-6">
        <!-- 1. General Branding -->
        <div class="p-6 rounded-3xl bg-white/5 border border-white/10 space-y-4">
          <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-globe text-netflix text-xs"></i>
            <span>Platform Branding</span>
          </h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1.5">Site Name</label>
              <input 
                type="text" 
                v-model="form.site_name" 
                required 
                class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1.5">Default Player Server</label>
              <input 
                type="text" 
                v-model="form.default_player_server" 
                placeholder="VidCloud HD"
                class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-300 mb-1.5">Site Slogan / Tagline</label>
            <input 
              type="text" 
              v-model="form.site_tagline" 
              class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix"
            />
          </div>
        </div>

        <!-- 2. Global Announcement Banner -->
        <div class="p-6 rounded-3xl bg-white/5 border border-white/10 space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-bullhorn text-amber-400 text-xs"></i>
                <span>Global Site Announcement Bar</span>
              </h2>
              <p class="text-xs text-gray-400 mt-0.5">Displays a top alert message to all visitors across the site.</p>
            </div>

            <button 
              type="button" 
              @click="form.announcement_enabled = !form.announcement_enabled"
              class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
              :class="form.announcement_enabled ? 'bg-netflix' : 'bg-gray-700'"
            >
              <span 
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                :class="form.announcement_enabled ? 'translate-x-6' : 'translate-x-1'"
              />
            </button>
          </div>

          <div v-if="form.announcement_enabled" class="space-y-2 pt-2 border-t border-white/5 animate-fade-in">
            <label class="block text-xs font-bold text-gray-300 mb-1">Announcement Message</label>
            <textarea 
              v-model="form.announcement_text" 
              rows="2"
              placeholder="e.g. Welcome to Movie®! Enjoy 4K HDR streaming with ultra-fast playback."
              class="w-full bg-black/60 border border-white/10 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-netflix"
            ></textarea>
          </div>
        </div>

        <!-- 3. TMDB API Integration -->
        <div class="p-6 rounded-3xl bg-white/5 border border-white/10 space-y-4">
          <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
            <i class="fas fa-key text-blue-400 text-xs"></i>
            <span>TheMovieDatabase (TMDB) Integration</span>
          </h2>
          <p class="text-xs text-gray-400">
            Used to auto-complete movie posters, backdrops, cast, director, and metadata by TMDB ID.
          </p>

          <div>
            <label class="block text-xs font-bold text-gray-300 mb-1.5">TMDB API Key (v3 auth)</label>
            <input 
              type="password" 
              v-model="form.tmdb_api_key" 
              placeholder="Optional: Enter TMDB API key"
              class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix font-mono"
            />
          </div>
        </div>

        <!-- 4. Maintenance Mode Toggle -->
        <div class="p-6 rounded-3xl bg-white/5 border border-white/10 flex items-center justify-between">
          <div>
            <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
              <i class="fas fa-tools text-red-400 text-xs"></i>
              <span>System Maintenance Mode</span>
            </h2>
            <p class="text-xs text-gray-400 mt-0.5">Temporarily show a maintenance notice for non-admin visitors.</p>
          </div>

          <button 
            type="button" 
            @click="form.maintenance_mode = !form.maintenance_mode"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
            :class="form.maintenance_mode ? 'bg-netflix' : 'bg-gray-700'"
          >
            <span 
              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
              :class="form.maintenance_mode ? 'translate-x-6' : 'translate-x-1'"
            />
          </button>
        </div>

        <!-- 5. SQLite Database Backup & Snapshot -->
        <div class="p-6 rounded-3xl bg-white/5 border border-white/10 space-y-3">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <i class="fas fa-database text-emerald-400 text-xs"></i>
                <span>Veritabanı Yedeği & Kurtarma (SQLite Snapshot)</span>
              </h2>
              <p class="text-xs text-gray-400 mt-0.5">Tüm film, dizi, kullanıcı, inceleme ve sistem ayarlarını içeren tek parça veritabanı yedeğini anında indirin.</p>
            </div>

            <a 
              href="/admin/backup/download"
              class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-lg shadow-emerald-600/30 flex items-center gap-2 flex-shrink-0"
            >
              <i class="fas fa-download"></i>
              <span>Yedeği İndir (.sqlite)</span>
            </a>
          </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end pt-2">
          <button 
            type="submit" 
            :disabled="submitting"
            class="px-6 py-3 rounded-2xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-xl shadow-red-600/30 flex items-center gap-2 disabled:opacity-50"
          >
            <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
            <span>{{ submitting ? 'Saving...' : 'Save Site Settings' }}</span>
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '../Layout.vue';

const props = defineProps({
  settings: {
    type: Object,
    default: () => ({}),
  },
});

const form = ref({
  site_name: props.settings.site_name || 'Movie®',
  site_tagline: props.settings.site_tagline || '',
  announcement_enabled: Boolean(props.settings.announcement_enabled),
  announcement_text: props.settings.announcement_text || '',
  tmdb_api_key: props.settings.tmdb_api_key || '',
  default_player_server: props.settings.default_player_server || 'VidCloud HD',
  maintenance_mode: Boolean(props.settings.maintenance_mode),
});

const submitting = ref(false);
const clearingCache = ref(false);

const submitSettings = () => {
  submitting.value = true;
  router.post('/admin/settings', form.value, {
    preserveScroll: true,
    onFinish: () => {
      submitting.value = false;
    },
  });
};

const clearCache = () => {
  clearingCache.value = true;
  router.post('/admin/clear-cache', {}, {
    preserveScroll: true,
    onFinish: () => {
      clearingCache.value = false;
    },
  });
};
</script>
