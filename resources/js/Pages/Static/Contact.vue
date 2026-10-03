<template>
  <AppLayout>
    <Head :title="$t('contact.page_title')" />

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
              <span class="text-white">{{ $t('contact.title') }}</span>
            </nav>
          </div>

          <!-- Page Header -->
          <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600/20 rounded-2xl mb-6">
              <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </div>
            <h1 class="text-4xl sm:text-5xl font-bold text-white mb-4 bg-gradient-to-r from-white to-gray-300 bg-clip-text text-transparent">
              {{ $t('contact.title') }}
            </h1>
            <p class="text-gray-400 text-base sm:text-lg">{{ $t('contact.subtitle') }}</p>
          </div>

          <!-- Content Card with Message Form -->
          <div class="bg-black/40 backdrop-blur-sm rounded-2xl border border-gray-700/50 shadow-2xl p-8 sm:p-12 space-y-6">
            <div v-if="submitted" class="p-6 rounded-xl bg-green-500/10 border border-green-500/30 text-center space-y-2">
              <i class="fas fa-check-circle text-3xl text-green-400"></i>
              <h3 class="text-lg font-bold text-white">{{ $t('contact.thank_you') }}</h3>
              <p class="text-xs text-gray-400">{{ $t('contact.thank_you_desc') }}</p>
            </div>

            <form v-else @submit.prevent="submitContact" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-300 mb-1">{{ $t('contact.your_name') }}</label>
                  <input 
                    type="text" 
                    v-model="form.name" 
                    required 
                    :placeholder="$t('contact.name_placeholder')"
                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-netflix text-sm"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-300 mb-1">{{ $t('contact.email_address') }}</label>
                  <input 
                    type="email" 
                    v-model="form.email" 
                    required 
                    :placeholder="$t('contact.email_placeholder')"
                    class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-netflix text-sm"
                  />
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">{{ $t('contact.subject') }}</label>
                <input 
                  type="text" 
                  v-model="form.subject" 
                  required 
                  :placeholder="$t('contact.subject_placeholder')"
                  class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-netflix text-sm"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">{{ $t('contact.message') }}</label>
                <textarea 
                  v-model="form.message" 
                  rows="4" 
                  required 
                  :placeholder="$t('contact.message_placeholder')"
                  class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-netflix text-sm resize-none"
                ></textarea>
              </div>

              <button 
                type="submit" 
                class="w-full py-3.5 bg-netflix hover:bg-red-700 text-white font-bold rounded-xl text-sm transition shadow-lg flex items-center justify-center gap-2"
              >
                <i class="far fa-paper-plane"></i>
                <span>{{ $t('contact.send_message') }}</span>
              </button>
            </form>
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
              {{ $t('contact.back_to_site') }}
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const submitted = ref(false);
const form = ref({
  name: '',
  email: '',
  subject: '',
  message: '',
});

const submitContact = () => {
  submitted.value = true;
};
</script>
