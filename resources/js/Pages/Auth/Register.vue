<template>
  <Head :title="$t('auth.register_page_title')" />

  <div class="min-h-screen flex flex-col lg:flex-row bg-[#050607] text-white">
    <!-- Left: form -->
    <main class="flex-1 lg:w-1/2 flex items-center justify-center p-6 sm:p-10">
      <div class="w-full max-w-lg">
        <Link href="/" class="text-gray-400 hover:text-netflix text-sm inline-flex items-center gap-2 mb-6 transition">
          <i class="fas fa-arrow-left"></i> {{ $t('auth.back_to_site') }}
        </Link>

        <div class="bg-black/60 backdrop-blur-md p-8 rounded-2xl border border-white/10 shadow-2xl">
          <div class="text-center mb-5">
            <h2 class="text-xl font-bold text-white">{{ $t('auth.join_title') }}</h2>
            <p class="text-gray-400 text-sm">{{ $t('auth.join_subtitle') }}</p>
          </div>

          <form @submit.prevent="submit" class="space-y-3">
            <div>
              <label for="email" class="block text-sm text-white mb-1">{{ $t('auth.email') }}</label>
              <input 
                id="email" 
                type="email" 
                v-model="form.email" 
                required 
                autocomplete="username"
                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-netflix/50 focus:border-netflix/50 transition"
                :placeholder="$t('auth.email_placeholder')"
              />
              <span v-if="form.errors.email" class="text-xs text-red-500 mt-1 block">{{ form.errors.email }}</span>
            </div>

            <div>
              <label for="password" class="block text-sm text-white mb-1">{{ $t('auth.password') }}</label>
              <input 
                id="password" 
                type="password" 
                v-model="form.password" 
                required 
                autocomplete="new-password"
                class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-netflix/50 focus:border-netflix/50 transition"
                :placeholder="$t('auth.create_password')"
              />
              <span v-if="form.errors.password" class="text-xs text-red-500 mt-1 block">{{ form.errors.password }}</span>
            </div>

            <div class="flex items-center space-x-2 py-1">
              <input 
                id="terms" 
                type="checkbox" 
                v-model="form.terms" 
                required
                class="w-4 h-4 rounded border-white/20 bg-white/10 text-netflix focus:ring-netflix/50"
              />
              <label for="terms" class="text-xs text-gray-300 select-none">
                {{ $t('auth.terms_agree_lead') }} <Link href="/terms" class="text-netflix hover:underline">{{ $t('auth.terms') }}</Link> 
                {{ $t('auth.and') }} <Link href="/privacy" class="text-netflix hover:underline">{{ $t('auth.privacy') }}</Link>
              </label>
            </div>

            <button 
              type="submit" 
              :disabled="form.processing"
              class="w-full bg-gradient-to-r from-netflix to-netflix-light px-3 py-2.5 rounded text-white font-semibold hover:opacity-90 transition disabled:opacity-50 cursor-pointer"
            >
              {{ form.processing ? $t('auth.creating_account') : $t('auth.create_account') }}
            </button>

            <div class="text-center pt-2">
              <p class="text-gray-400 text-sm mb-2">{{ $t('auth.already_have_account') }}</p>
              <Link 
                href="/login" 
                class="inline-flex items-center justify-center w-full px-3 py-2 bg-white/5 border border-white/20 rounded text-white hover:bg-white/10 transition"
              >
                {{ $t('auth.sign_in') }}
              </Link>
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

const form = useForm({
  email: '',
  password: '',
  terms: true,
});

const submit = () => {
  form.post('/register', {
    onFinish: () => form.reset('password'),
  });
};
</script>
