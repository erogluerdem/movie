<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- User Profile Header Banner -->
      <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-gray-900 via-black to-gray-950 border border-white/10 p-6 sm:p-8 shadow-2xl">
        <!-- Ambient Red Glow Effect -->
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-netflix/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-netflix/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-center justify-between gap-6">
          <!-- Avatar & User Info -->
          <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
            <div class="relative group">
              <img 
                :src="$page.props.auth?.user?.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop'" 
                :alt="$page.props.auth?.user?.name"
                class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover ring-4 ring-netflix/40 shadow-xl"
              />
              <Link 
                href="/dashboard/settings" 
                class="absolute -bottom-1 -right-1 w-7 h-7 bg-netflix hover:bg-red-700 text-white rounded-full flex items-center justify-center text-xs shadow-lg transition"
                :title="$t('dashboard.change_avatar')"
              >
                <i class="fas fa-camera"></i>
              </Link>
            </div>

            <div>
              <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 mb-1.5">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                  {{ $page.props.auth?.user?.name }}
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-netflix text-white shadow-sm shadow-red-500/50">
                  {{ $page.props.auth?.user?.role === 'admin' ? $t('dashboard.admin_badge') : $t('dashboard.vip_member') }}
                </span>
              </div>
              <p class="text-xs sm:text-sm text-gray-400 flex items-center justify-center sm:justify-start gap-2">
                <i class="far fa-envelope text-gray-500"></i>
                <span>{{ $page.props.auth?.user?.email }}</span>
              </p>
              <div class="flex items-center justify-center sm:justify-start gap-4 mt-2.5 text-[11px] text-gray-400">
                <span class="flex items-center gap-1.5">
                  <i class="far fa-calendar-alt text-netflix"></i>
                  {{ $t('dashboard.member_since', { date: memberSince }) }}
                </span>
                <span class="flex items-center gap-1.5">
                  <i class="fas fa-shield-alt text-green-400"></i>
                  {{ $t('dashboard.account_verified') }}
                </span>
              </div>
            </div>
          </div>

          <!-- Quick Action Button -->
          <div class="flex items-center gap-2.5 flex-wrap justify-center sm:justify-end">
            <Link 
              v-if="$page.props.auth?.user?.role === 'admin'"
              href="/admin" 
              class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition shadow-lg flex items-center gap-2"
            >
              <i class="fas fa-shield-alt"></i>
              <span>{{ $t('dashboard.admin_panel') }}</span>
            </Link>
            <Link 
              href="/dashboard/requests" 
              class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-semibold transition border border-white/10 flex items-center gap-2"
            >
              <i class="fas fa-plus text-netflix"></i>
              <span>{{ $t('dashboard.request_movie_show') }}</span>
            </Link>
            <Link 
              href="/dashboard/settings" 
              class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-semibold transition border border-white/10 flex items-center gap-2"
            >
              <i class="fas fa-cog"></i>
              <span>{{ $t('dashboard.settings_btn') }}</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="border-b border-white/10">
        <nav class="flex items-center space-x-1 sm:space-x-2 overflow-x-auto pb-3 scrollbar-none" aria-label="Dashboard Tabs">
          <Link 
            v-for="tab in tabs" 
            :key="tab.route"
            :href="tab.href"
            :class="[
              currentRoute.startsWith(tab.href) 
                ? 'bg-netflix text-white shadow-lg shadow-red-600/30' 
                : 'text-gray-400 hover:text-white hover:bg-white/5'
            ]"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition flex-shrink-0"
          >
            <i :class="tab.icon"></i>
            <span>{{ tab.name }}</span>
          </Link>
        </nav>
      </div>

      <!-- Flash Message Notification -->
      <div 
        v-if="$page.props.flash?.success" 
        class="p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-xs sm:text-sm flex items-center gap-3 animate-fade-in"
      >
        <i class="fas fa-check-circle text-lg flex-shrink-0"></i>
        <span>{{ $page.props.flash.success }}</span>
      </div>

      <div 
        v-if="$page.props.flash?.error" 
        class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs sm:text-sm flex items-center gap-3 animate-fade-in"
      >
        <i class="fas fa-exclamation-circle text-lg flex-shrink-0"></i>
        <span>{{ $page.props.flash.error }}</span>
      </div>

      <!-- Tab Content Slot -->
      <main class="space-y-8">
        <slot />
      </main>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from '@/Composables/useI18n';

const page = usePage();
const { t, locale } = useI18n();

const currentRoute = computed(() => {
  return page.url;
});

const memberSince = computed(() => {
  const date = page.props.auth?.user?.created_at;
  if (!date) return '2026';
  const loc = locale.value === 'tr' ? 'tr-TR' : 'en-US';
  return new Date(date).toLocaleDateString(loc, { month: 'short', year: 'numeric' });
});

const tabs = computed(() => [
  { name: t('dashboard.overview'), href: '/dashboard', icon: 'fas fa-chart-pie' },
  { name: t('dashboard.watchlist'), href: '/dashboard/watchlist', icon: 'fas fa-bookmark' },
  { name: t('dashboard.history'), href: '/dashboard/history', icon: 'fas fa-history' },
  { name: t('dashboard.requests'), href: '/dashboard/requests', icon: 'fas fa-film' },
  { name: t('dashboard.settings_prefs'), href: '/dashboard/settings', icon: 'fas fa-sliders-h' },
]);
</script>
