<template>
  <div class="space-y-8 pt-8 border-t border-white/10">
    <!-- Header & Score Summary -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-xl sm:text-2xl font-black text-white flex items-center gap-3">
          <i class="fas fa-comments text-netflix"></i>
          <span>{{ $t('reviews.title') }}</span>
          <span class="text-xs px-2.5 py-0.5 rounded-full bg-white/10 text-gray-300 font-normal">
            {{ $t('reviews.count', { count: reviewsList.length }) }}
          </span>
        </h2>
        <p class="text-xs text-gray-400 mt-1">{{ $t('reviews.subtitle') }}</p>
      </div>

      <!-- Average Community Score Pill -->
      <div class="flex items-center gap-3 bg-white/5 border border-white/10 px-4 py-2 rounded-2xl">
        <div class="text-right">
          <div class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">{{ $t('reviews.community_score') }}</div>
          <div class="text-xs text-gray-500">{{ $t('reviews.verified_ratings', { count: reviewsList.length }) }}</div>
        </div>
        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500/20 border border-amber-500/30 text-amber-400 font-black text-lg">
          <i class="fas fa-star text-sm"></i>
          <span>{{ avgRating }}</span>
          <span class="text-[10px] text-amber-400/60 font-normal">/10</span>
        </div>
      </div>
    </div>

    <!-- Review Submission Form -->
    <div class="p-6 rounded-3xl bg-gray-950/80 border border-white/10 space-y-4 shadow-xl">
      <!-- If Guest -->
      <div v-if="!$page.props.auth?.user" class="text-center py-6 space-y-2">
        <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center mx-auto text-gray-400 text-sm">
          <i class="fas fa-user-lock"></i>
        </div>
        <h3 class="text-sm font-bold text-white">{{ $t('reviews.join_discussion') }}</h3>
        <p class="text-xs text-gray-400 max-w-sm mx-auto">
          {{ $t('reviews.join_desc') }}
        </p>
        <div class="pt-2">
          <Link 
            href="/login" 
            class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg"
          >
            <i class="fas fa-sign-in-alt"></i>
            <span>{{ $t('reviews.login_to_review') }}</span>
          </Link>
        </div>
      </div>

      <!-- If Logged In -->
      <form v-else @submit.prevent="submitReview" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-white/5">
          <div class="text-xs font-bold text-white flex items-center gap-2">
            <span>{{ $t('reviews.rate_title') }}</span>
            <span class="text-amber-400 font-extrabold text-sm">{{ form.rating }}/10</span>
            <span class="text-[11px] text-gray-400 font-normal">({{ ratingLabel(form.rating) }})</span>
          </div>

          <!-- Interactive 10-Star Rating Picker -->
          <div class="flex items-center gap-1 cursor-pointer">
            <button 
              type="button" 
              v-for="star in 10" 
              :key="star"
              @click="form.rating = star"
              @mouseenter="hoverStar = star"
              @mouseleave="hoverStar = null"
              class="text-sm transition-transform hover:scale-125 p-0.5 focus:outline-none"
              :class="star <= (hoverStar || form.rating) ? 'text-amber-400' : 'text-gray-600'"
              :title="`${star}/10`"
            >
              <i class="fas fa-star"></i>
            </button>
          </div>
        </div>

        <!-- Textarea -->
        <div>
          <textarea 
            v-model="form.content" 
            required 
            rows="3"
            :placeholder="$t('reviews.placeholder')"
            class="w-full bg-black/60 border border-white/10 rounded-2xl p-4 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
          ></textarea>
        </div>

        <!-- Spoiler Switch & Submit -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
          <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-300">
            <input 
              type="checkbox" 
              v-model="form.has_spoiler" 
              class="rounded bg-black border-white/20 text-netflix focus:ring-0"
            />
            <span class="text-amber-400/90 font-semibold flex items-center gap-1">
              <i class="fas fa-exclamation-triangle text-[11px]"></i>
              <span>{{ $t('reviews.spoiler_alert') }}</span>
            </span>
          </label>

          <button 
            type="submit" 
            :disabled="submitting || !form.content.trim()"
            class="px-5 py-2.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg flex items-center justify-center gap-2 disabled:opacity-50"
          >
            <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
            <span>{{ submitting ? $t('reviews.submitting') : $t('reviews.submit') }}</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Filter Buttons -->
    <div v-if="reviewsList.length > 0" class="flex items-center gap-2">
      <button 
        @click="activeFilter = 'all'"
        :class="activeFilter === 'all' ? 'bg-netflix text-white font-bold' : 'bg-white/5 text-gray-400 hover:text-white'"
        class="px-3.5 py-1.5 rounded-xl text-xs transition"
      >
        {{ $t('reviews.all_reviews', { count: reviewsList.length }) }}
      </button>

      <button 
        @click="activeFilter = 'clean'"
        :class="activeFilter === 'clean' ? 'bg-netflix text-white font-bold' : 'bg-white/5 text-gray-400 hover:text-white'"
        class="px-3.5 py-1.5 rounded-xl text-xs transition"
      >
        {{ $t('reviews.clean_reviews') }}
      </button>

      <button 
        @click="activeFilter = 'spoilers'"
        :class="activeFilter === 'spoilers' ? 'bg-netflix text-white font-bold' : 'bg-white/5 text-gray-400 hover:text-white'"
        class="px-3.5 py-1.5 rounded-xl text-xs transition"
      >
        {{ $t('reviews.spoiler_reviews', { count: spoilerCount }) }}
      </button>
    </div>

    <!-- Reviews Stream -->
    <div v-if="filteredReviews.length > 0" class="space-y-4">
      <div 
        v-for="rev in filteredReviews" 
        :key="rev.id"
        class="p-5 rounded-2xl bg-white/5 border border-white/10 space-y-3 relative group"
      >
        <!-- Review Author Header -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <img 
              :src="rev.user?.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop'" 
              :alt="rev.user?.name"
              class="w-9 h-9 rounded-xl object-cover ring-2 ring-white/10"
            />
            <div>
              <div class="flex items-center gap-2">
                <span class="font-bold text-white text-xs sm:text-sm">{{ rev.user?.name || 'Anonymous Fan' }}</span>
                <span 
                  v-if="rev.user?.role === 'admin'" 
                  class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-netflix text-white"
                >
                  Admin
                </span>
                
                <template v-if="rev.user?.badges">
                  <span 
                    v-for="badge in parseBadges(rev.user.badges)"
                    :key="badge"
                    class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-amber-500 text-black shadow-[0_0_8px_rgba(245,158,11,0.5)]"
                  >
                    <i class="fas fa-medal mr-0.5"></i> {{ badge }}
                  </span>
                </template>

                <span 
                  v-if="!rev.user?.badges || parseBadges(rev.user?.badges).length === 0" 
                  class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase bg-white/10 text-gray-400"
                >
                  Verified
                </span>
              </div>
              <div class="text-[11px] text-gray-500 mt-0.5">{{ formatDate(rev.created_at) }}</div>
            </div>
          </div>

          <!-- Rating Badge & Delete -->
          <div class="flex items-center gap-2">
            <div class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-400 font-extrabold text-xs border border-amber-500/30">
              <i class="fas fa-star text-[10px]"></i>
              <span>{{ rev.rating }}/10</span>
            </div>

            <button 
              v-if="canDelete(rev)" 
              @click="deleteReview(rev.id)"
              class="w-7 h-7 rounded-lg bg-white/5 hover:bg-red-500/20 text-gray-400 hover:text-red-400 flex items-center justify-center text-xs transition"
              :title="$t('reviews.delete_review')"
            >
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </div>

        <!-- Review Body with Spoiler Blur Protection -->
        <div class="relative">
          <!-- If it has spoiler and not yet revealed -->
          <div 
            v-if="rev.has_spoiler && !revealedSpoilers[rev.id]"
            class="p-4 rounded-xl bg-black/70 border border-amber-500/30 backdrop-blur-md flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left"
          >
            <div class="flex items-center gap-2.5 text-xs text-amber-400 font-bold">
              <i class="fas fa-shield-alt text-base"></i>
              <span>{{ $t('reviews.spoiler_warning') }}</span>
            </div>
            <button 
              type="button" 
              @click="revealedSpoilers[rev.id] = true"
              class="px-3 py-1.5 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 text-xs font-bold transition flex items-center gap-1.5 flex-shrink-0"
            >
              <i class="fas fa-eye text-[11px]"></i>
              <span>{{ $t('reviews.reveal_spoiler') }}</span>
            </button>
          </div>

          <!-- Revealed or Clean Content -->
          <div 
            v-else 
            class="text-xs sm:text-sm text-gray-200 leading-relaxed whitespace-pre-line"
          >
            <span v-if="rev.has_spoiler" class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold uppercase bg-amber-500/20 text-amber-400 border border-amber-500/30 mr-2 mb-1">
              Spoiler
            </span>
            {{ rev.content }}
          </div>
        </div>

        <!-- Likes & Engagement Footer -->
        <div class="pt-2 flex items-center justify-between text-xs text-gray-400 border-t border-white/5">
          <button 
            type="button" 
            @click="likeReview(rev)"
            :class="likedReviews[rev.id] ? 'text-netflix font-bold' : 'text-gray-400 hover:text-white'"
            class="flex items-center gap-1.5 transition"
          >
            <i :class="likedReviews[rev.id] ? 'fas fa-heart text-netflix' : 'far fa-heart'"></i>
            <span>{{ rev.likes_count || 0 }} {{ $t('reviews.helpful') }}</span>
          </button>

          <span class="text-[10px] text-gray-500">{{ $t('reviews.verified_review') }}</span>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div 
      v-else 
      class="p-10 rounded-2xl bg-white/5 border border-dashed border-white/10 text-center space-y-2"
    >
      <div class="text-gray-500 text-2xl"><i class="far fa-comment-dots"></i></div>
      <h3 class="text-sm font-bold text-white">{{ $t('reviews.no_reviews') }}</h3>
      <p class="text-xs text-gray-400 max-w-sm mx-auto">
        {{ $t('reviews.no_reviews_desc') }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import axios from 'axios';

const parseBadges = (badges) => {
  if (!badges) return [];
  if (Array.isArray(badges)) return badges;
  try {
    return JSON.parse(badges) || [];
  } catch (e) {
    return [];
  }
};

const props = defineProps({
  reviews: {
    type: Array,
    default: () => [],
  },
  mediaType: {
    type: String,
    required: true, // 'movie' or 'tv'
  },
  mediaId: {
    type: Number,
    required: true,
  },
  communityRating: {
    type: [Number, String],
    default: 8.0,
  },
});

const page = usePage();
const reviewsList = ref([...props.reviews]);
const hoverStar = ref(null);
const submitting = ref(false);
const activeFilter = ref('all'); // 'all', 'clean', 'spoilers'
const revealedSpoilers = ref({});
const likedReviews = ref({});

const form = ref({
  rating: 10,
  content: '',
  has_spoiler: false,
});

const avgRating = computed(() => {
  if (!reviewsList.value.length) return Number(props.communityRating || 8.0).toFixed(1);
  const sum = reviewsList.value.reduce((acc, r) => acc + (r.rating || 10), 0);
  return (sum / reviewsList.value.length).toFixed(1);
});

const spoilerCount = computed(() => {
  return reviewsList.value.filter(r => r.has_spoiler).length;
});

const filteredReviews = computed(() => {
  if (activeFilter.value === 'clean') {
    return reviewsList.value.filter(r => !r.has_spoiler);
  }
  if (activeFilter.value === 'spoilers') {
    return reviewsList.value.filter(r => r.has_spoiler);
  }
  return reviewsList.value;
});

const ratingLabel = (num) => {
  if (num === 10) return 'Masterpiece 🔥';
  if (num >= 8) return 'Great';
  if (num >= 6) return 'Good';
  if (num >= 4) return 'Mediocre';
  return 'Not Recommended';
};

const submitReview = () => {
  submitting.value = true;
  router.post('/api/reviews', {
    media_type: props.mediaType,
    media_id: props.mediaId,
    rating: form.value.rating,
    content: form.value.content,
    has_spoiler: form.value.has_spoiler,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      form.value.content = '';
      form.value.has_spoiler = false;
      submitting.value = false;
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
};

const likeReview = async (review) => {
  try {
    const res = await axios.post(`/api/reviews/${review.id}/like`);
    likedReviews.value[review.id] = res.data.liked;
    review.likes_count = res.data.likes_count;
  } catch (e) {
    console.error(e);
  }
};

const canDelete = (review) => {
  const user = page.props.auth?.user;
  if (!user) return false;
  return user.id === review.user_id || user.role === 'admin';
};

const deleteReview = (id) => {
  if (confirm('Delete this review?')) {
    router.delete(`/api/reviews/${id}`, {
      preserveScroll: true,
      onSuccess: () => {
        reviewsList.value = reviewsList.value.filter(r => r.id !== id);
      },
    });
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'Recently';
  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};
</script>
