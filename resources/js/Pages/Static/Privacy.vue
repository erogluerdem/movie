<template>
  <AppLayout>
    <Head :title="`${$t('static.privacy_title')} - Movie®`" />

    <div class="relative min-h-[80vh] bg-gray-900">
      <div class="relative z-10 pt-16 pb-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <!-- Breadcrumb -->
          <div class="mb-8">
            <nav class="flex items-center space-x-2 text-sm text-gray-400">
              <Link href="/" class="hover:text-netflix transition-colors">{{ $t('nav.home') }}</Link>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              <span class="text-white">{{ $t('static.privacy_title') }}</span>
            </nav>
          </div>

          <!-- Page Header -->
          <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600/20 rounded-2xl mb-6">
              <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <h1 class="text-4xl sm:text-5xl font-bold text-white mb-4 bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
              {{ $t('static.privacy_title') }}
            </h1>
            <p class="text-gray-400 text-base sm:text-lg">{{ $t('static.last_updated') }}</p>
          </div>

          <!-- Content Card -->
          <div class="bg-black/40 backdrop-blur-sm rounded-2xl border border-gray-700/50 shadow-2xl p-8 sm:p-12 space-y-6 text-gray-300 text-sm leading-relaxed">
            <section>
              <h2 class="text-xl font-bold text-white mb-2">{{ $t('static.privacy_h1') }}</h2>
              <p>{{ $t('static.privacy_p1') }}</p>
            </section>

            <section>
              <h2 class="text-xl font-bold text-white mb-2">{{ $t('static.privacy_h2') }}</h2>
              <p>{{ $t('static.privacy_p2') }}</p>
            </section>

            <section>
              <h2 class="text-xl font-bold text-white mb-2">{{ $t('static.privacy_h3') }}</h2>
              <p>{{ $t('static.privacy_p3') }}</p>
            </section>

            <section>
              <h2 class="text-xl font-bold text-white mb-2">{{ $t('static.privacy_h4') }}</h2>
              <p>{{ $t('static.privacy_p4') }}</p>
            </section>
          </div>

          <!-- Navigation -->
          <div class="flex justify-center mt-12">
            <Link 
              href="/" 
              class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
            >
              <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              {{ $t('static.back_home') }}
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>
