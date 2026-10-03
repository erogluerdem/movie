<template>
  <DashboardLayout>
    <div class="space-y-8 max-w-4xl">
      <!-- 1. Profile Information & Avatar -->
      <section class="p-6 sm:p-8 rounded-3xl bg-white/5 border border-white/10 space-y-6">
        <div>
          <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
            <i class="fas fa-user-circle text-netflix"></i>
            <span>{{ $t('dashboard.profile_avatar') }}</span>
          </h2>
          <p class="text-xs text-gray-400 mt-0.5">{{ $t('dashboard.profile_avatar_desc') }}</p>
        </div>

        <!-- Avatar Selection Grid -->
        <div class="space-y-3">
          <label class="block text-xs font-bold text-gray-300">{{ $t('dashboard.choose_avatar') }}</label>
          <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
            <button 
              type="button" 
              v-for="(imgUrl, i) in avatars" 
              :key="i"
              @click="profileForm.avatar = imgUrl"
              class="relative rounded-2xl overflow-hidden aspect-square border-2 transition-all p-0.5"
              :class="profileForm.avatar === imgUrl ? 'border-netflix ring-4 ring-netflix/30 scale-105 shadow-xl' : 'border-white/10 hover:border-white/30 opacity-70 hover:opacity-100'"
            >
              <img :src="imgUrl" class="w-full h-full object-cover rounded-xl" />
              <div 
                v-if="profileForm.avatar === imgUrl" 
                class="absolute bottom-1 right-1 w-5 h-5 rounded-full bg-netflix text-white text-[10px] flex items-center justify-center shadow"
              >
                <i class="fas fa-check"></i>
              </div>
            </button>
          </div>

          <!-- Custom Avatar URL Input -->
          <div class="pt-2">
            <label class="block text-[11px] text-gray-400 mb-1">{{ $t('dashboard.custom_avatar_url') }}</label>
            <input 
              type="url" 
              v-model="profileForm.avatar" 
              placeholder="https://example.com/avatar.jpg"
              class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
            />
          </div>
        </div>

        <!-- Profile Form -->
        <form @submit.prevent="updateProfile" class="space-y-4 pt-2 border-t border-white/5">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1.5">{{ $t('dashboard.display_name') }}</label>
              <input 
                type="text" 
                v-model="profileForm.name" 
                required 
                class="w-full h-11 bg-black/60 border border-white/10 rounded-xl px-4 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
              />
              <span v-if="profileForm.errors.name" class="text-[11px] text-red-400 mt-1 block">
                {{ profileForm.errors.name }}
              </span>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1.5">{{ $t('dashboard.email_address') }}</label>
              <input 
                type="email" 
                :value="user.email" 
                disabled 
                class="w-full h-11 bg-black/30 border border-white/5 rounded-xl px-4 text-xs text-gray-500 cursor-not-allowed"
              />
              <p class="text-[10px] text-gray-500 mt-1">{{ $t('dashboard.email_linked') }}</p>
            </div>
          </div>

          <div class="flex justify-end pt-2">
            <button 
              type="submit" 
              :disabled="profileForm.processing"
              class="px-5 py-2.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg flex items-center gap-2 disabled:opacity-50"
            >
              <i v-if="profileForm.processing" class="fas fa-spinner fa-spin"></i>
              <span>{{ profileForm.processing ? $t('dashboard.saving') : $t('dashboard.save_profile') }}</span>
            </button>
          </div>
        </form>
      </section>

      <!-- 2. Streaming & Playback Preferences -->
      <section class="p-6 sm:p-8 rounded-3xl bg-white/5 border border-white/10 space-y-6">
        <div>
          <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
            <i class="fas fa-sliders-h text-netflix"></i>
            <span>{{ $t('dashboard.streaming_prefs') }}</span>
          </h2>
          <p class="text-xs text-gray-400 mt-0.5">{{ $t('dashboard.streaming_prefs_desc') }}</p>
        </div>

        <form @submit.prevent="updatePreferences" class="space-y-6">
          <!-- Preferred Video Quality -->
          <div class="space-y-2">
            <label class="block text-xs font-bold text-gray-300">{{ $t('dashboard.default_quality') }}</label>
            <div class="grid grid-cols-3 gap-3">
              <button 
                type="button" 
                v-for="quality in ['720p', '1080p', '4K']"
                :key="quality"
                @click="prefForm.preferred_quality = quality"
                :class="[
                  prefForm.preferred_quality === quality 
                    ? 'bg-netflix text-white border-netflix shadow-lg shadow-red-600/30' 
                    : 'bg-black/50 text-gray-400 border-white/10 hover:bg-white/10'
                ]"
                class="py-3 px-4 rounded-2xl border text-center transition flex flex-col items-center justify-center gap-1"
              >
                <span class="text-sm font-black">{{ quality }}</span>
                <span class="text-[10px] opacity-75">
                  {{ quality === '4K' ? $t('dashboard.ultra_hd') : quality === '1080p' ? $t('dashboard.full_hd') : $t('dashboard.standard_hd') }}
                </span>
              </button>
            </div>
          </div>

          <!-- Preferred Audio / Subtitle Language -->
          <div class="space-y-2">
            <label class="block text-xs font-bold text-gray-300">{{ $t('dashboard.default_audio') }}</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
              <button 
                type="button" 
                v-for="lang in languages"
                :key="lang.code"
                @click="prefForm.preferred_language = lang.code"
                :class="[
                  prefForm.preferred_language === lang.code 
                    ? 'bg-netflix text-white border-netflix shadow' 
                    : 'bg-black/50 text-gray-400 border-white/10 hover:bg-white/10'
                ]"
                class="py-2.5 px-3 rounded-xl border text-xs font-bold transition flex items-center justify-between"
              >
                <span>{{ lang.name }}</span>
                <span class="text-[10px] uppercase font-mono opacity-60">{{ lang.code }}</span>
              </button>
            </div>
          </div>

          <!-- Autoplay Next Episode -->
          <div class="flex items-center justify-between p-4 rounded-2xl bg-black/40 border border-white/10">
            <div>
              <div class="text-xs font-bold text-white">{{ $t('dashboard.autoplay_next') }}</div>
              <p class="text-[11px] text-gray-400 mt-0.5">{{ $t('dashboard.autoplay_next_desc') }}</p>
            </div>
            <button 
              type="button" 
              @click="prefForm.autoplay_next = !prefForm.autoplay_next"
              class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
              :class="prefForm.autoplay_next ? 'bg-netflix' : 'bg-gray-700'"
            >
              <span 
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                :class="prefForm.autoplay_next ? 'translate-x-6' : 'translate-x-1'"
              />
            </button>
          </div>

          <div class="flex justify-end pt-2">
            <button 
              type="submit" 
              :disabled="prefForm.processing"
              class="px-5 py-2.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg flex items-center gap-2 disabled:opacity-50"
            >
              <i v-if="prefForm.processing" class="fas fa-spinner fa-spin"></i>
              <span>{{ prefForm.processing ? $t('dashboard.saving') : $t('dashboard.save_preferences') }}</span>
            </button>
          </div>
        </form>
      </section>

      <!-- 3. Security & Password Update -->
      <section class="p-6 sm:p-8 rounded-3xl bg-white/5 border border-white/10 space-y-6">
        <div>
          <h2 class="text-lg font-extrabold text-white flex items-center gap-2">
            <i class="fas fa-shield-alt text-netflix"></i>
            <span>{{ $t('dashboard.security_password') }}</span>
          </h2>
          <p class="text-xs text-gray-400 mt-0.5">{{ $t('dashboard.security_desc') }}</p>
        </div>

        <form @submit.prevent="updatePassword" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-300 mb-1.5">{{ $t('dashboard.current_password') }}</label>
            <input 
              type="password" 
              v-model="pwdForm.current_password" 
              required
              class="w-full h-11 bg-black/60 border border-white/10 rounded-xl px-4 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
            />
            <span v-if="pwdForm.errors.current_password" class="text-[11px] text-red-400 mt-1 block">
              {{ pwdForm.errors.current_password }}
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1.5">{{ $t('dashboard.new_password') }}</label>
              <input 
                type="password" 
                v-model="pwdForm.password" 
                required
                class="w-full h-11 bg-black/60 border border-white/10 rounded-xl px-4 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
              />
              <span v-if="pwdForm.errors.password" class="text-[11px] text-red-400 mt-1 block">
                {{ pwdForm.errors.password }}
              </span>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1.5">{{ $t('dashboard.confirm_new_password') }}</label>
              <input 
                type="password" 
                v-model="pwdForm.password_confirmation" 
                required
                class="w-full h-11 bg-black/60 border border-white/10 rounded-xl px-4 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
              />
            </div>
          </div>

          <div class="flex justify-end pt-2">
            <button 
              type="submit" 
              :disabled="pwdForm.processing"
              class="px-5 py-2.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg flex items-center gap-2 disabled:opacity-50"
            >
              <i v-if="pwdForm.processing" class="fas fa-spinner fa-spin"></i>
              <span>{{ pwdForm.processing ? $t('dashboard.updating') : $t('dashboard.update_password') }}</span>
            </button>
          </div>
        </form>
      </section>

      <!-- 4. Danger Zone -->
      <section class="p-6 rounded-3xl bg-red-950/20 border border-red-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h3 class="text-sm font-bold text-red-400 flex items-center gap-2">
            <i class="fas fa-exclamation-triangle"></i>
            <span>{{ $t('dashboard.session_sign_out') }}</span>
          </h3>
          <p class="text-xs text-gray-400 mt-0.5">{{ $t('dashboard.session_sign_out_desc') }}</p>
        </div>

        <button 
          @click="logout"
          class="px-5 py-2.5 rounded-xl bg-red-500/20 hover:bg-red-500/30 text-red-400 hover:text-red-300 text-xs font-bold transition border border-red-500/30 flex items-center gap-2 flex-shrink-0"
        >
          <i class="fas fa-sign-out-alt"></i>
          <span>{{ $t('dashboard.logout_btn') }}</span>
        </button>
      </section>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import DashboardLayout from './Layout.vue';

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  avatars: {
    type: Array,
    default: () => [],
  },
});

const languages = [
  { name: 'English', code: 'en' },
  { name: 'Turkish', code: 'tr' },
  { name: 'Spanish', code: 'es' },
  { name: 'French', code: 'fr' },
  { name: 'German', code: 'de' },
  { name: 'Japanese', code: 'ja' },
];

const profileForm = useForm({
  name: props.user.name || '',
  avatar: props.user.avatar || (props.avatars.length ? props.avatars[0] : ''),
});

const prefForm = useForm({
  preferred_quality: props.user.preferred_quality || '1080p',
  preferred_language: props.user.preferred_language || 'en',
  autoplay_next: Boolean(props.user.autoplay_next),
});

const pwdForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updateProfile = () => {
  profileForm.post('/dashboard/profile', {
    preserveScroll: true,
  });
};

const updatePreferences = () => {
  prefForm.post('/dashboard/preferences', {
    preserveScroll: true,
  });
};

const updatePassword = () => {
  pwdForm.post('/dashboard/password', {
    preserveScroll: true,
    onSuccess: () => pwdForm.reset(),
  });
};

const logout = () => {
  router.post('/logout');
};
</script>
