<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-white/10">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <i class="fas fa-star-half-alt text-amber-400"></i>
            <span>Reviews Moderation</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/10 text-gray-300">
              {{ stats.total_reviews || 0 }}
            </span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">Audit community opinions, remove toxic content or spoilers, and inspect rating distributions.</p>
        </div>

        <div class="flex items-center gap-3">
          <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold">
            <i class="fas fa-star"></i>
            <span>Global Avg: {{ stats.avg_rating || '0.0' }} / 10</span>
          </div>
        </div>
      </div>

      <!-- Quick Stats KPI Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-between">
          <div>
            <div class="text-xs text-gray-400 font-medium">Total Reviews</div>
            <div class="text-xl sm:text-2xl font-black text-white mt-0.5">{{ stats.total_reviews || 0 }}</div>
          </div>
          <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-sm">
            <i class="fas fa-comments"></i>
          </div>
        </div>

        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-between">
          <div>
            <div class="text-xs text-gray-400 font-medium">Movie Reviews</div>
            <div class="text-xl sm:text-2xl font-black text-white mt-0.5">{{ stats.movie_reviews || 0 }}</div>
          </div>
          <div class="w-9 h-9 rounded-xl bg-red-500/10 text-netflix flex items-center justify-center text-sm">
            <i class="fas fa-film"></i>
          </div>
        </div>

        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-between">
          <div>
            <div class="text-xs text-gray-400 font-medium">TV Show Reviews</div>
            <div class="text-xl sm:text-2xl font-black text-white mt-0.5">{{ stats.tv_reviews || 0 }}</div>
          </div>
          <div class="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-sm">
            <i class="fas fa-tv"></i>
          </div>
        </div>

        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-between">
          <div>
            <div class="text-xs text-gray-400 font-medium">Spoiler Warnings</div>
            <div class="text-xl sm:text-2xl font-black text-white mt-0.5">{{ stats.spoiler_reviews || 0 }}</div>
          </div>
          <div class="w-9 h-9 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-sm">
            <i class="fas fa-exclamation-triangle"></i>
          </div>
        </div>
      </div>

      <!-- Filters & Search Toolbar -->
      <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0 scrollbar-none w-full md:w-auto">
          <!-- Type Filter -->
          <button 
            @click="setFilter('type', '')" 
            :class="[!filters.type ? 'bg-netflix text-white font-bold' : 'bg-white/5 text-gray-400 hover:text-white']"
            class="px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 flex-shrink-0"
          >
            All Media
          </button>
          <button 
            @click="setFilter('type', 'movie')" 
            :class="[filters.type === 'movie' ? 'bg-netflix text-white font-bold' : 'bg-white/5 text-gray-400 hover:text-white']"
            class="px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 flex-shrink-0"
          >
            <i class="fas fa-film text-[10px]"></i> Movies
          </button>
          <button 
            @click="setFilter('type', 'tv')" 
            :class="[filters.type === 'tv' ? 'bg-netflix text-white font-bold' : 'bg-white/5 text-gray-400 hover:text-white']"
            class="px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 flex-shrink-0"
          >
            <i class="fas fa-tv text-[10px]"></i> TV Shows
          </button>

          <!-- Spoiler Filter -->
          <button 
            @click="setFilter('has_spoiler', filters.has_spoiler === '1' ? '' : '1')" 
            :class="[filters.has_spoiler === '1' ? 'bg-rose-600 text-white font-bold' : 'bg-white/5 text-gray-400 hover:text-white']"
            class="px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 flex-shrink-0"
          >
            <i class="fas fa-mask text-[10px]"></i> Spoilers Only
          </button>
        </div>

        <!-- Search Bar -->
        <div class="relative w-full md:w-72">
          <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
          <input 
            v-model="searchQuery" 
            @keyup.enter="performSearch"
            type="text" 
            placeholder="Search review or author..." 
            class="w-full pl-9 pr-8 py-2 rounded-xl bg-black/40 border border-white/10 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix"
          />
          <button 
            v-if="searchQuery" 
            @click="clearSearch" 
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-white text-xs"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <!-- Reviews Table / List -->
      <div v-if="reviews.data && reviews.data.length > 0" class="rounded-2xl border border-white/10 bg-gray-900/60 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-gray-300">
            <thead class="bg-white/5 text-[11px] font-black uppercase text-gray-400 tracking-wider border-b border-white/10">
              <tr>
                <th class="p-4">User</th>
                <th class="p-4">Media</th>
                <th class="p-4">Score</th>
                <th class="p-4 min-w-[300px]">Review Content</th>
                <th class="p-4 text-center">Likes</th>
                <th class="p-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr 
                v-for="review in reviews.data" 
                :key="review.id" 
                class="hover:bg-white/5 transition duration-150"
              >
                <!-- User Info -->
                <td class="p-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <img 
                      :src="review.user?.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop'" 
                      :alt="review.user?.name"
                      class="w-8 h-8 rounded-full object-cover ring-1 ring-white/20" 
                    />
                    <div>
                      <div class="font-bold text-white flex items-center gap-1.5">
                        <span>{{ review.user?.name || 'Anonymous' }}</span>
                        <span v-if="review.user?.role === 'admin'" class="px-1.5 py-0.2 rounded text-[9px] font-black bg-red-600 text-white">ADMIN</span>
                      </div>
                      <div class="text-[10px] text-gray-500">{{ review.user?.email }}</div>
                      <div class="text-[10px] text-gray-500">{{ formatDate(review.created_at) }}</div>
                    </div>
                  </div>
                </td>

                <!-- Media Title -->
                <td class="p-4 whitespace-nowrap">
                  <div class="space-y-1">
                    <div class="flex items-center gap-2">
                      <span 
                        :class="review.reviewable_type?.includes('TvShow') ? 'bg-purple-500/20 text-purple-400 border-purple-500/30' : 'bg-blue-500/20 text-blue-400 border-blue-500/30'"
                        class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase border"
                      >
                        {{ review.reviewable_type?.includes('TvShow') ? 'TV Show' : 'Movie' }}
                      </span>
                    </div>
                    <div class="font-bold text-white truncate max-w-[180px]" :title="review.reviewable?.title">
                      {{ review.reviewable?.title || 'Unknown Content' }}
                    </div>
                  </div>
                </td>

                <!-- Rating Badge -->
                <td class="p-4 whitespace-nowrap">
                  <div 
                    :class="ratingColor(review.rating)"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-black border"
                  >
                    <i class="fas fa-star text-[10px]"></i>
                    <span>{{ review.rating }}</span>
                    <span class="text-[10px] opacity-70">/10</span>
                  </div>
                </td>

                <!-- Review Content -->
                <td class="p-4">
                  <div class="space-y-1.5">
                    <div v-if="review.has_spoiler" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-500/20 text-rose-400 text-[10px] font-extrabold border border-rose-500/30">
                      <i class="fas fa-exclamation-triangle"></i>
                      <span>SPOILER ALERT</span>
                    </div>
                    <p class="text-xs text-gray-200 line-clamp-3 leading-relaxed whitespace-pre-wrap">
                      {{ review.content }}
                    </p>
                  </div>
                </td>

                <!-- Likes Count -->
                <td class="p-4 text-center whitespace-nowrap">
                  <div class="inline-flex items-center gap-1.5 text-xs text-gray-400">
                    <i class="fas fa-thumbs-up text-amber-400/80"></i>
                    <span class="font-bold">{{ review.likes_count || 0 }}</span>
                  </div>
                </td>

                <!-- Actions -->
                <td class="p-4 text-right whitespace-nowrap">
                  <button 
                    @click="confirmDelete(review)"
                    class="p-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 border border-red-500/20 transition"
                    title="Delete Review"
                  >
                    <i class="fas fa-trash-alt text-xs"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="reviews.links && reviews.links.length > 3" class="p-4 bg-white/5 border-t border-white/10 flex items-center justify-between">
          <div class="text-xs text-gray-400">
            Showing <strong class="text-white">{{ reviews.from || 0 }}</strong> to <strong class="text-white">{{ reviews.to || 0 }}</strong> of <strong class="text-white">{{ reviews.total }}</strong> reviews
          </div>
          <div class="flex items-center gap-1">
            <Link 
              v-for="(link, i) in reviews.links" 
              :key="i"
              :href="link.url || '#'"
              :class="[
                link.active ? 'bg-netflix text-white font-bold' : 'bg-white/5 text-gray-400 hover:text-white',
                !link.url ? 'opacity-40 cursor-not-allowed' : ''
              ]"
              class="px-3 py-1.5 rounded-lg text-xs transition"
              v-html="link.label"
            />
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="p-12 rounded-2xl bg-white/5 border border-white/10 text-center space-y-3">
        <div class="w-12 h-12 rounded-full bg-white/5 text-gray-400 flex items-center justify-center mx-auto text-xl">
          <i class="fas fa-comment-slash"></i>
        </div>
        <div class="text-base font-bold text-white">No reviews found</div>
        <p class="text-xs text-gray-400 max-w-sm mx-auto">No community reviews match the selected filter criteria or search query.</p>
        <button 
          v-if="filters.search || filters.type || filters.has_spoiler" 
          @click="resetAllFilters"
          class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold transition"
        >
          Reset Filters
        </button>
      </div>

      <!-- Delete Confirmation Modal -->
      <div 
        v-if="deletingReview" 
        class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
        @click.self="deletingReview = null"
      >
        <div class="w-full max-w-md bg-gray-950 border border-white/10 rounded-2xl p-6 shadow-2xl space-y-4 animate-scale-up">
          <div class="w-12 h-12 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-400 flex items-center justify-center text-xl">
            <i class="fas fa-trash-alt"></i>
          </div>

          <div>
            <h3 class="text-lg font-black text-white">Delete Review?</h3>
            <p class="text-xs text-gray-400 mt-1">
              Are you sure you want to remove this review by <strong class="text-white">{{ deletingReview.user?.name || 'User' }}</strong>? This will also remove all likes and recalculate rating averages.
            </p>
          </div>

          <div class="p-3 rounded-xl bg-white/5 border border-white/5 text-xs text-gray-300 italic line-clamp-2">
            "{{ deletingReview.content }}"
          </div>

          <div class="flex items-center justify-end gap-3 pt-2">
            <button 
              @click="deletingReview = null"
              class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 text-xs font-semibold transition"
            >
              Cancel
            </button>
            <button 
              @click="submitDelete"
              :disabled="submitting"
              class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition flex items-center gap-2"
            >
              <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
              <span>Confirm Delete</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../Layout.vue';

const props = defineProps({
  reviews: {
    type: Object,
    default: () => ({ data: [] }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
});

const searchQuery = ref(props.filters.search || '');
const deletingReview = ref(null);
const submitting = ref(false);

const setFilter = (key, value) => {
  const newFilters = { ...props.filters, [key]: value };
  if (!value) delete newFilters[key];
  router.get('/admin/reviews', newFilters, { preserveState: true, preserveScroll: true });
};

const performSearch = () => {
  const newFilters = { ...props.filters, search: searchQuery.value };
  if (!searchQuery.value) delete newFilters.search;
  router.get('/admin/reviews', newFilters, { preserveState: true, preserveScroll: true });
};

const clearSearch = () => {
  searchQuery.value = '';
  performSearch();
};

const resetAllFilters = () => {
  searchQuery.value = '';
  router.get('/admin/reviews', {}, { preserveState: true, preserveScroll: true });
};

const confirmDelete = (review) => {
  deletingReview.value = review;
};

const submitDelete = () => {
  if (!deletingReview.value) return;
  submitting.value = true;
  router.delete(`/admin/reviews/${deletingReview.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      deletingReview.value = null;
      submitting.value = false;
    },
    onError: () => {
      submitting.value = false;
    },
  });
};

const ratingColor = (score) => {
  if (score >= 8) return 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30';
  if (score >= 5) return 'bg-amber-500/15 text-amber-400 border-amber-500/30';
  return 'bg-red-500/15 text-red-400 border-red-500/30';
};

const formatDate = (str) => {
  if (!str) return '';
  return new Date(str).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};
</script>
