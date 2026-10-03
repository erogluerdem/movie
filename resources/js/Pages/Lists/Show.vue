<template>
  <AppLayout>
    <Head :title="`${customList.title} - ${$t('lists.hero_title_1')} ${$t('lists.hero_title_2')}`" />

    <main class="min-h-screen py-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-10">
      <!-- List Header -->
      <section class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-gray-950 via-gray-900 to-black border border-white/10 p-6 sm:p-10 shadow-2xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="space-y-3 max-w-3xl">
            <!-- Breadcrumbs / Kicker -->
            <div class="flex items-center gap-2 text-xs text-gray-400">
              <Link href="/lists" class="hover:text-white transition flex items-center gap-1">
                <i class="fas fa-chevron-left text-[10px]"></i> {{ $t('lists.all_lists') }}
              </Link>
              <span>•</span>
              <span v-if="!customList.is_public" class="text-amber-400 flex items-center gap-1">
                <i class="fas fa-lock text-[10px]"></i> {{ $t('lists.private_badge') }}
              </span>
              <span v-else class="text-emerald-400 flex items-center gap-1">
                <i class="fas fa-globe text-[10px]"></i> {{ $t('lists.public_badge') }}
              </span>
            </div>

            <!-- Title -->
            <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
              {{ customList.title }}
            </h1>

            <!-- Description -->
            <p v-if="customList.description" class="text-sm sm:text-base text-gray-300 whitespace-pre-line">
              {{ customList.description }}
            </p>

            <!-- Creator & Stats -->
            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-400 pt-2 border-t border-white/5">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-netflix/30 flex items-center justify-center text-xs text-white font-bold">
                  {{ (customList.user?.name || 'K')[0].toUpperCase() }}
                </div>
                <span class="text-gray-200 font-medium">{{ customList.user?.name || $t('lists.default_user') }}</span>
              </div>
              <span>•</span>
              <span><i class="fas fa-film mr-1 text-netflix"></i> {{ $t('lists.items_count', { count: customList.items_count || 0 }) }}</span>
              <span>•</span>
              <span><i class="far fa-calendar mr-1"></i> {{ formatDate(customList.created_at) }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-wrap md:flex-col items-center md:items-end gap-2.5">
            <!-- Share Button -->
            <button 
              type="button" 
              @click="shareList"
              class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-gray-200 text-xs font-semibold flex items-center gap-2 transition"
            >
              <i :class="copied ? 'fas fa-check text-green-400' : 'fas fa-share-alt'"></i>
              <span>{{ copied ? $t('lists.link_copied') : $t('lists.share_list') }}</span>
            </button>

            <!-- Owner Controls -->
            <template v-if="isOwner">
              <button 
                type="button" 
                @click="openEditModal"
                class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-gray-200 text-xs font-semibold flex items-center gap-2 transition"
              >
                <i class="fas fa-edit"></i>
                <span>{{ $t('lists.edit_list') }}</span>
              </button>

              <button 
                type="button" 
                @click="deleteList"
                class="px-4 py-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 text-red-400 text-xs font-semibold flex items-center gap-2 transition"
              >
                <i class="fas fa-trash-alt"></i>
                <span>{{ $t('lists.delete_list') }}</span>
              </button>
            </template>
          </div>
        </div>
      </section>

      <!-- Items Grid -->
      <section class="space-y-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold text-white flex items-center gap-2">
            <i class="fas fa-th-large text-netflix text-sm"></i>
            <span>{{ $t('lists.items_in_list') }}</span>
          </h2>
          <span class="text-xs text-gray-400">{{ $t('lists.content_count', { count: itemsList.length }) }}</span>
        </div>

        <div v-if="itemsList.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-5">
          <div 
            v-for="item in itemsList" 
            :key="item.id"
            class="group relative bg-gray-950 rounded-2xl overflow-hidden border border-white/10 hover:border-white/20 transition duration-300 flex flex-col"
          >
            <!-- Poster -->
            <Link :href="getItemUrl(item)" class="relative aspect-[2/3] overflow-hidden block bg-gray-900">
              <img 
                :src="getItemPoster(item)" 
                :alt="getItemTitle(item)"
                loading="lazy" 
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center">
                <div class="w-10 h-10 rounded-full bg-netflix text-white flex items-center justify-center text-sm shadow-xl transform scale-75 group-hover:scale-100 transition">
                  <i class="fas fa-play ml-0.5"></i>
                </div>
              </div>

              <!-- Media Type Badge -->
              <span class="absolute top-2 left-2 px-1.5 py-0.5 rounded text-[10px] font-bold bg-black/70 text-gray-300 backdrop-blur-sm uppercase">
                {{ item.media_type === 'movie' ? $t('home.movie') : $t('home.tv_show') }}
              </span>

              <!-- Rating Badge -->
              <span class="absolute top-2 right-2 px-1.5 py-0.5 rounded text-[10px] font-bold bg-black/70 text-amber-400 backdrop-blur-sm flex items-center gap-1">
                <i class="fas fa-star text-[9px]"></i> {{ getItemRating(item) }}
              </span>
            </Link>

            <!-- Card Info -->
            <div class="p-3 flex-1 flex flex-col justify-between">
              <div>
                <Link :href="getItemUrl(item)" class="text-xs font-bold text-white hover:text-netflix transition line-clamp-1">
                  {{ getItemTitle(item) }}
                </Link>
                <span class="text-[11px] text-gray-400">{{ getItemYear(item) }}</span>
              </div>

              <!-- Owner Remove Button -->
              <div v-if="isOwner" class="pt-2 mt-2 border-t border-white/5 flex justify-end">
                <button 
                  type="button" 
                  @click="removeItem(item)"
                  class="text-[11px] text-red-400/80 hover:text-red-400 hover:bg-red-500/10 px-2 py-0.5 rounded transition flex items-center gap-1"
                  :title="$t('lists.remove_from_list')"
                >
                  <i class="fas fa-times text-[10px]"></i> {{ $t('lists.remove') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16 bg-white/[0.02] border border-dashed border-white/10 rounded-2xl">
          <i class="fas fa-film text-4xl text-gray-600 mb-3 block"></i>
          <p class="text-gray-300 font-semibold">{{ $t('lists.empty_list_title') }}</p>
          <p class="text-xs text-gray-500 mt-1">{{ $t('lists.empty_list_lead') }}</p>
          <Link 
            href="/movies" 
            class="inline-block mt-4 px-4 py-2 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition"
          >
            {{ $t('lists.explore_movies') }}
          </Link>
        </div>
      </section>
    </main>

    <!-- Edit List Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="showEditModal = false"></div>
      <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-gray-950 border border-white/10 rounded-2xl p-6 shadow-2xl space-y-4" @click.stop>
          <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <h3 class="text-base font-bold text-white">{{ $t('lists.modal_edit_title') }}</h3>
            <button @click="showEditModal = false" class="text-gray-400 hover:text-white"><i class="fas fa-times"></i></button>
          </div>

          <form @submit.prevent="submitEditList" class="space-y-4">
            <div class="space-y-1">
              <label class="text-xs font-semibold text-gray-300">{{ $t('lists.list_title') }}</label>
              <input 
                type="text" 
                v-model="editForm.title" 
                required 
                maxlength="100"
                class="w-full px-3 py-2 text-xs rounded-xl bg-black/60 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-netflix"
              />
            </div>

            <div class="space-y-1">
              <label class="text-xs font-semibold text-gray-300">{{ $t('lists.list_desc') }}</label>
              <textarea 
                v-model="editForm.description" 
                rows="3"
                maxlength="500"
                class="w-full px-3 py-2 text-xs rounded-xl bg-black/60 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-netflix resize-none"
              ></textarea>
            </div>

            <label class="flex items-center gap-2.5 text-xs text-gray-300 cursor-pointer pt-1">
              <input type="checkbox" v-model="editForm.is_public" class="rounded bg-gray-800 border-gray-700 text-netflix focus:ring-0" />
              <span>{{ $t('lists.is_public_label') }}</span>
            </label>

            <div class="pt-3 flex items-center justify-end gap-2">
              <button 
                type="button" 
                @click="showEditModal = false"
                class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white text-xs transition"
              >
                {{ $t('lists.cancel') }}
              </button>
              <button 
                type="submit" 
                class="px-5 py-2 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition"
              >
                {{ $t('lists.save_changes') }}
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
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from '@/Composables/useI18n';
import axios from 'axios';

const props = defineProps({
  customList: { type: Object, required: true },
  isOwner: { type: Boolean, default: false },
});

const { t } = useI18n();

const itemsList = ref([...(props.customList.items || [])]);
const copied = ref(false);
const showEditModal = ref(false);

const editForm = reactive({
  title: props.customList.title || '',
  description: props.customList.description || '',
  is_public: Boolean(props.customList.is_public),
});

const openEditModal = () => {
  editForm.title = props.customList.title;
  editForm.description = props.customList.description;
  editForm.is_public = Boolean(props.customList.is_public);
  showEditModal.value = true;
};

const submitEditList = () => {
  router.put(`/lists/${props.customList.id}`, editForm, {
    onSuccess: () => {
      showEditModal.value = false;
    },
  });
};

const deleteList = () => {
  if (confirm(t('lists.confirm_delete') || 'Bu listeyi tamamen silmek istediğinizden emin misiniz?')) {
    router.delete(`/lists/${props.customList.id}`);
  }
};

const removeItem = async (item) => {
  try {
    await axios.post(`/lists/${props.customList.id}/items/remove`, {
      item_id: item.id,
    });
    itemsList.value = itemsList.value.filter(i => i.id !== item.id);
  } catch (err) {
    console.error('Error removing item from list:', err);
  }
};

const shareList = () => {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(window.location.href);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2500);
  }
};

const getItemMedia = (item) => {
  return item.movie || item.tv_show || {};
};

const getItemTitle = (item) => {
  return getItemMedia(item).title || t('lists.unnamed_content') || 'İsimsiz İçerik';
};

const getItemPoster = (item) => {
  const media = getItemMedia(item);
  return media.poster_path || media.poster_url || 'https://image.tmdb.org/t/p/w342/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg';
};

const getItemRating = (item) => {
  const media = getItemMedia(item);
  return Number(media.vote_average || 7.5).toFixed(1);
};

const getItemYear = (item) => {
  const media = getItemMedia(item);
  const dateStr = media.release_date || media.first_air_date;
  return dateStr ? String(dateStr).substring(0, 4) : '2024';
};

const getItemUrl = (item) => {
  const media = getItemMedia(item);
  const rawSlug = media.slug || '';
  if (item.media_type === 'movie') {
    const slug = rawSlug.startsWith('watch-') ? rawSlug : `watch-${rawSlug}`;
    return `/movie/${slug}`;
  }
  return `/tv-show/${rawSlug}`;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>
