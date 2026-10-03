<template>
  <Head :title="$t('auth.forgot_page_title')" />

  <div class="min-h-screen flex flex-col lg:flex-row bg-[#050607] text-white">
    <!-- Left: form -->
    <main class="flex-1 lg:w-1/2 flex items-center justify-center p-6 sm:p-10">
      <div class="w-full max-w-lg">
        <Link href="/" class="text-gray-400 hover:text-netflix text-sm inline-flex items-center gap-2 mb-6 transition">
          <i class="fas fa-arrow-left"></i> {{ $t('auth.back_to_site') }}
        </Link>

        <div class="bg-black/60 backdrop-blur-md p-8 rounded-2xl border border-white/10 shadow-2xl">
          <div class="mb-4 text-sm text-gray-400 leading-relaxed">
            {{ $t('auth.forgot_instruction') }}
          </div>

          <div v-if="status" class="mb-4 font-medium text-sm text-green-400 bg-green-500/10 border border-green-500/20 p-3 rounded-lg">
            {{ status }}
          </div>

          <form @submit.prevent="submit" class="space-y-4">
            <div>
              <label for="email" class="block text-sm text-white mb-1 font-medium">{{ $t('auth.email') }}</label>
              <input 
                id="email" 
                type="email" 
                v-model="form.email" 
                required 
                autofocus
                autocomplete="username"
                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-netflix/50 focus:border-netflix/50 transition"
                :placeholder="$t('auth.email_placeholder')"
              />
              <span v-if="form.errors.email" class="text-xs text-red-500 mt-1 block">{{ form.errors.email }}</span>
            </div>

            <div class="flex items-center justify-between mt-4">
              <Link href="/login" class="text-sm text-gray-400 hover:text-white transition">
                {{ $t('auth.back_to_sign_in') }}
              </Link>

              <button 
                type="submit" 
                :disabled="form.processing"
                class="bg-gradient-to-r from-netflix to-netflix-light px-4 py-2.5 rounded text-white font-semibold text-sm hover:opacity-90 transition disabled:opacity-50 cursor-pointer"
              >
                {{ form.processing ? $t('auth.sending') : $t('auth.send_reset_link') }}
              </button>
            </div>
          </form>
        </div>

        <p class="text-gray-500 text-xs text-center mt-6">
          {{ $t('auth.copyright') }}
        </p>
      </div>
    </main>

    <!-- Right: hero image -->
    <div class="lg:w-1/2 relative hidden lg:flex">
      <img src="/images/background.jpg" class="absolute inset-0 w-full h-full object-cover" alt="Background" />
      <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-black/70"></div>
      <div class="relative z-10 flex flex-col justify-center p-10 text-white space-y-4">
        <p class="uppercase tracking-[0.2em] text-sm text-white/80">{{ $t('auth.welcome_badge') }}</p>
        <h1 class="text-4xl font-bold leading-tight max-w-xl">
          {{ $t('auth.welcome_quote') }}
        </h1>
        <p class="text-white/80 max-w-2xl">
          {{ $t('auth.welcome_lead') }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
  status: {
    type: String,
    default: null,
  },
});

const form = useForm({
  email: '',
});

const submit = () => {
  form.post('/forgot-password');
};
</script>
