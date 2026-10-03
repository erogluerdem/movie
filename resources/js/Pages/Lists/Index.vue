<template>
  <AppLayout>
    <Head :title="$t('lists.page_title')" />

    <main class="min-h-screen py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-12">
      <!-- Hero Header -->
      <section class="relative rounded-3xl overflow-hidden bg-gradient-to-r from-gray-950 via-gray-900 to-black border border-white/10 p-8 sm:p-12">
        <div class="absolute -right-10 -bottom-10 w-96 h-96 bg-netflix/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 max-w-2xl space-y-4">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-netflix/20 border border-netflix/30 text-netflix text-xs font-semibold">
            <i class="fas fa-layer-group"></i>
            <span>{{ $t('lists.community_kicker') }}</span>
          </div>
          <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight">
            {{ $t('lists.hero_title_1') }} <span class="text-netflix">{{ $t('lists.hero_title_2') }}</span>
          </h1>
          <p class="text-sm sm:text-base text-gray-300">
            {{ $t('lists.hero_desc') }}
          </p>
          <div class="pt-2 flex flex-wrap items-center gap-3">
            <button 
              type="button" 
              @click="openCreateModal"
              class="px-5 py-2.5 rounded-xl bg-netflix hover:bg-red-700 text-white font-bold text-xs sm:text-sm shadow-xl shadow-netflix/20 transition flex items-center gap-2"
            >
              <i class="fas fa-plus"></i>
              <span>{{ $t('lists.create_new') }}</span>
            </button>
            <a 
              href="#public-lists"
              class="px-5 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-gray-300 hover:text-white font-semibold text-xs sm:text-sm transition flex items-center gap-2"
            >
              <i class="fas fa-compass"></i>
              <span>{{ $t('lists.explore_lists') }}</span>
            </a>
          </div>
        </div>
      </section>

      <!-- My Lists Section (If Logged In) -->
      <section v-if="$page.props.auth?.user && myLists && myLists.length > 0" class="space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-6 bg-netflix rounded-full inline-block"></span>
            <h2 class="text-xl font-bold text-white">{{ $t('lists.my_lists') }}</h2>
            <span class="text-xs text-gray-400">({{ myLists.length }})</span>
          </div>
          <button 
            @click="openCreateModal"
            class="text-xs text-netflix hover:underline font-semibold flex items-center gap-1"
          >
            <i class="fas fa-plus"></i> {{ $t('lists.add_list') }}
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <Link 
            v-for="list in myLists" 
            :key="'my-' + list.id"
            :href="`/lists/${list.slug}`"
            class="group block bg-gray-950/80 border border-white/10 hover:border-netflix/50 rounded-2xl p-4 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-netflix/10"
          >
            <!-- 4-Poster Collage -->
            <div class="aspect-[16/10] rounded-xl overflow-hidden bg-black/60 grid grid-cols-2 grid-rows-2 gap-1 p-1 border border-white/5 mb-3.5">
              <template v-if="list.items && list.items.length > 0">
                <div 
                  v-for="(item, idx) in list.items.slice(0, 4)" 
                  :key="idx"
                  class="relative overflow-hidden rounded bg-gray-900"
                >
                  <img 
                    :src="getPoster(item)" 
                    alt="" 
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500" 
                    loading="lazy"
                  />
                </div>
                <!-- Fill remaining slots if less than 4 -->
                <div 
                  v-for="i in Math.max(0, 4 - list.items.length)" 
                  :key="'empty-' + i"
                  class="bg-white/[0.02] flex items-center justify-center text-gray-700 text-xs"
                >
                  <i class="fas fa-film"></i>
                </div>
              </template>
              <div v-else class="col-span-2 row-span-2 flex items-center justify-center text-gray-600 text-sm">
                <i class="fas fa-folder-open text-2xl mr-2"></i>
                <span>{{ $t('lists.empty_list') }}</span>
              </div>
            </div>

            <!-- List Meta -->
            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white group-hover:text-netflix transition truncate flex-1 mr-2">
                  {{ list.title }}
                </h3>
                <span v-if="!list.is_public" class="text-[10px] text-gray-500 bg-white/5 px-2 py-0.5 rounded">
                  <i class="fas fa-lock mr-1"></i> {{ $t('lists.private') }}
                </span>
              </div>
              <p class="text-xs text-gray-400 line-clamp-1">
                {{ list.description || $t('lists.items_count_badge', { count: list.items_count || 0 }) }}
              </p>
              <div class="text-[11px] text-gray-500 pt-1 flex items-center gap-2">
                <span><i class="fas fa-film mr-1"></i> {{ $t('lists.items_count', { count: list.items_count || 0 }) }}</span>
              </div>
            </div>
          </Link>
        </div>
      </section>

      <!-- Public Lists Section -->
      <section id="public-lists" class="space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <div class="flex items-center gap-2.5">
              <span class="w-2.5 h-6 bg-netflix rounded-full inline-block"></span>
              <h2 class="text-xl font-bold text-white">{{ $t('lists.community_lists') }}</h2>
            </div>
            <p class="text-xs text-gray-400 mt-1">{{ $t('lists.community_subtitle') }}</p>
          </div>
        </div>

        <div v-if="publicLists.data && publicLists.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <Link 
            v-for="list in publicLists.data" 
            :key="'public-' + list.id"
            :href="`/lists/${list.slug}`"
            class="group block bg-gray-950/80 border border-white/10 hover:border-white/20 rounded-2xl p-4 transition duration-300 hover:-translate-y-1 hover:shadow-2xl"
          >
            <!-- 4-Poster Collage -->
            <div class="aspect-[16/10] rounded-xl overflow-hidden bg-black/60 grid grid-cols-2 grid-rows-2 gap-1 p-1 border border-white/5 mb-3.5">
              <template v-if="list.items && list.items.length > 0">
                <div 
                  v-for="(item, idx) in list.items.slice(0, 4)" 
                  :key="idx"
                  class="relative overflow-hidden rounded bg-gray-900"
                >
                  <img 
                    :src="getPoster(item)" 
                    alt="" 
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500" 
                    loading="lazy"
                  />
                </div>
                <!-- Fill remaining slots if less than 4 -->
                <div 
                  v-for="i in Math.max(0, 4 - list.items.length)" 
                  :key="'pub-empty-' + i"
                  class="bg-white/[0.02] flex items-center justify-center text-gray-700 text-xs"
                >
                  <i class="fas fa-film"></i>
                </div>
              </template>
              <div v-else class="col-span-2 row-span-2 flex items-center justify-center text-gray-600 text-sm">
                <i class="fas fa-folder-open text-2xl mr-2"></i>
                <span>{{ $t('lists.empty_list') }}</span>
              </div>
            </div>

            <!-- List Title & Author -->
            <div class="space-y-2">
              <h3 class="text-base font-bold text-white group-hover:text-netflix transition line-clamp-1">
                {{ list.title }}
              </h3>
              <p class="text-xs text-gray-400 line-clamp-2 min-h-[32px]">
                {{ list.description || $t('lists.no_desc') }}
              </p>

              <div class="pt-2 border-t border-white/5 flex items-center justify-between text-xs text-gray-400">
                <div class="flex items-center gap-2">
                  <div class="w-5 h-5 rounded-full bg-netflix/30 flex items-center justify-center text-[10px] text-white font-bold">
                    {{ (list.user?.name || 'K')[0].toUpperCase() }}
                  </div>
                  <span class="truncate max-w-[120px]">{{ list.user?.name || $t('lists.default_user') }}</span>
                </div>
                <span class="font-semibold text-gray-300">
                  <i class="fas fa-film mr-1 text-netflix"></i> {{ $t('lists.items_count', { count: list.items_count || 0 }) }}
                </span>
              </div>
            </div>
          </Link>
        </div>

        <div v-else class="text-center py-16 bg-white/[0.02] border border-dashed border-white/10 rounded-2xl">
          <i class="fas fa-folder-open text-4xl text-gray-600 mb-3 block"></i>
          <p class="text-gray-300 font-semibold">{{ $t('lists.no_community_lists') }}</p>
          <p class="text-xs text-gray-500 mt-1">{{ $t('lists.be_the_first') }}</p>
          <button 
            type="button" 
            @click="openCreateModal" 
            class="mt-4 px-4 py-2 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition"
          >
            {{ $t('lists.create_now') }}
          </button>
        </div>

        <!-- Pagination -->
        <div v-if="publicLists.links && publicLists.links.length > 3" class="flex justify-center gap-2 pt-6">
          <template v-for="(link, i) in publicLists.links" :key="i">
            <Link 
              v-if="link.url"
              :href="link.url"
              v-html="link.label"
              :class="[
                link.active ? 'bg-netflix text-white font-bold' : 'bg-white/5 text-gray-300 hover:bg-white/10',
                'px-3.5 py-1.5 rounded-lg text-xs transition'
              ]"
            />
          </template>
        </div>
      </section>
    </main>

    <!-- Create List Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>
      <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-gray-950 border border-white/10 rounded-2xl p-6 shadow-2xl space-y-4" @click.stop>
          <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-xl bg-netflix/20 text-netflix flex items-center justify-center text-sm">
                <i class="fas fa-plus"></i>
              </div>
              <h3 class="text-base font-bold text-white">{{ $t('lists.modal_create_title') }}</h3>
            </div>
            <button @click="showModal = false" class="text-gray-400 hover:text-white"><i class="fas fa-times"></i></button>
          </div>

          <form @submit.prevent="submitCreateList" class="space-y-4">
            <div class="space-y-1">
              <label class="text-xs font-semibold text-gray-300">{{ $t('lists.list_title') }}</label>
              <input 
                type="text" 
                v-model="createForm.title" 
                :placeholder="$t('lists.title_placeholder')"
                required
                maxlength="100"
                class="w-full px-3 py-2 text-xs rounded-xl bg-black/60 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-netflix"
              />
            </div>

            <div class="space-y-1">
              <label class="text-xs font-semibold text-gray-300">{{ $t('lists.list_desc') }}</label>
              <textarea 
                v-model="createForm.description" 
                rows="3"
                :placeholder="$t('lists.desc_placeholder')"
                maxlength="500"
                class="w-full px-3 py-2 text-xs rounded-xl bg-black/60 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-netflix resize-none"
              ></textarea>
            </div>

            <label class="flex items-center gap-2.5 text-xs text-gray-300 cursor-pointer pt-1">
              <input type="checkbox" v-model="createForm.is_public" class="rounded bg-gray-800 border-gray-700 text-netflix focus:ring-0" />
              <span>{{ $t('lists.is_public_label') }}</span>
            </label>

            <div class="pt-3 flex items-center justify-end gap-2">
              <button 
                type="button" 
                @click="showModal = false"
                class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white text-xs transition"
              >
                {{ $t('lists.cancel') }}
              </button>
              <button 
                type="submit" 
                :disabled="submitting || !createForm.title.trim()"
                class="px-5 py-2 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition flex items-center gap-2 disabled:opacity-50"
              >
                <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
                <span>{{ submitting ? $t('lists.creating') : $t('lists.create_button') }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  publicLists: { type: Object, required: true },
  myLists: { type: Array, default: () => [] },
});

const page = usePage();
const showModal = ref(false);
const submitting = ref(false);

const createForm = reactive({
  title: '',
  description: '',
  is_public: true,
});

const openCreateModal = () => {
  if (!page.props.auth?.user) {
    router.visit('/login');
    return;
  }
  showModal.value = true;
};

const submitCreateList = () => {
  if (!createForm.title.trim() || submitting.value) return;
  submitting.value = true;

  router.post('/lists', createForm, {
    onSuccess: () => {
      showModal.value = false;
      createForm.title = '';
      createForm.description = '';
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
};

const getPoster = (item) => {
  const media = item?.movie || item?.tv_show;
  return media?.poster_path || media?.poster_url || 'https://image.tmdb.org/t/p/w342/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg';
};
</script>
