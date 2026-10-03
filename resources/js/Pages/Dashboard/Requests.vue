<template>
  <DashboardLayout>
    <div class="space-y-8">
      <!-- Header Banner & Action -->
      <div class="p-6 rounded-2xl bg-gradient-to-r from-white/10 via-white/5 to-transparent border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="space-y-1">
          <div class="flex items-center gap-2 text-netflix text-xs font-bold uppercase tracking-wider">
            <i class="fas fa-film"></i>
            <span>{{ $t('dashboard.content_on_demand') }}</span>
          </div>
          <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">{{ $t('dashboard.request_heading') }}</h2>
          <p class="text-xs text-gray-400 max-w-xl">
            {{ $t('dashboard.request_lead') }}
          </p>
        </div>

        <button 
          @click="showModal = true" 
          class="px-5 py-3 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-xl shadow-red-600/30 flex items-center justify-center gap-2 flex-shrink-0 cursor-pointer"
        >
          <i class="fas fa-plus"></i>
          <span>{{ $t('dashboard.submit_request') }}</span>
        </button>
      </div>

      <!-- Status Legend Overview -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-3.5 rounded-xl bg-white/5 border border-white/5 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs flex-shrink-0">
            <i class="fas fa-clock"></i>
          </div>
          <div>
            <div class="text-xs font-bold text-white">{{ $t('dashboard.status_pending') }}</div>
            <div class="text-[10px] text-gray-400">{{ $t('dashboard.status_pending_desc') }}</div>
          </div>
        </div>

        <div class="p-3.5 rounded-xl bg-white/5 border border-white/5 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs flex-shrink-0">
            <i class="fas fa-search"></i>
          </div>
          <div>
            <div class="text-xs font-bold text-white">{{ $t('dashboard.status_in_review') }}</div>
            <div class="text-[10px] text-gray-400">{{ $t('dashboard.status_in_review_desc') }}</div>
          </div>
        </div>

        <div class="p-3.5 rounded-xl bg-white/5 border border-white/5 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-green-500/20 text-green-400 flex items-center justify-center text-xs flex-shrink-0">
            <i class="fas fa-check-circle"></i>
          </div>
          <div>
            <div class="text-xs font-bold text-white">{{ $t('dashboard.status_available') }}</div>
            <div class="text-[10px] text-gray-400">{{ $t('dashboard.status_available_desc') }}</div>
          </div>
        </div>

        <div class="p-3.5 rounded-xl bg-white/5 border border-white/5 flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-red-500/20 text-red-400 flex items-center justify-center text-xs flex-shrink-0">
            <i class="fas fa-ban"></i>
          </div>
          <div>
            <div class="text-xs font-bold text-white">{{ $t('dashboard.status_unavailable') }}</div>
            <div class="text-[10px] text-gray-400">{{ $t('dashboard.status_unavailable_desc') }}</div>
          </div>
        </div>
      </div>

      <!-- Requests Table / List -->
      <div v-if="requestsList.length > 0" class="space-y-4">
        <div class="flex items-center justify-between px-1">
          <h3 class="text-sm font-bold text-white uppercase tracking-wider">
            {{ $t('dashboard.your_submissions', { count: requests.total || requestsList.length }) }}
          </h3>
        </div>

        <div class="space-y-3">
          <div 
            v-for="req in requestsList" 
            :key="'req-' + req.id"
            class="p-4 sm:p-5 rounded-2xl bg-white/5 border border-white/10 hover:border-white/20 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4"
          >
            <!-- Request Info -->
            <div class="space-y-2 min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <span 
                  :class="req.type === 'tv' ? 'bg-purple-500/20 text-purple-400 border-purple-500/30' : 'bg-blue-500/20 text-blue-400 border-blue-500/30'"
                  class="text-[10px] font-black uppercase px-2 py-0.5 rounded border"
                >
                  {{ req.type === 'tv' ? $t('home.tv_show') : $t('home.movie') }}
                </span>

                <span 
                  :class="statusBadgeClass(req.status)"
                  class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full border flex items-center gap-1.5"
                >
                  <i :class="statusIcon(req.status)"></i>
                  <span>{{ formatStatus(req.status) }}</span>
                </span>

                <span v-if="req.release_year" class="text-xs text-gray-400">
                  ({{ req.release_year }})
                </span>
              </div>

              <h4 class="text-base sm:text-lg font-bold text-white truncate">
                {{ req.title }}
              </h4>

              <!-- User Notes & Admin Feedback -->
              <p v-if="req.user_notes" class="text-xs text-gray-400 italic">
                "{{ req.user_notes }}"
              </p>

              <div v-if="req.admin_notes" class="p-2.5 rounded-xl bg-netflix/10 border border-netflix/20 text-xs text-red-300 flex items-start gap-2">
                <i class="fas fa-comment-dots text-netflix mt-0.5"></i>
                <div>
                  <strong class="text-white">{{ $t('dashboard.admin_note') }}</strong> {{ req.admin_notes }}
                </div>
              </div>

              <div class="text-[11px] text-gray-500 flex items-center gap-2 pt-1">
                <i class="far fa-calendar-alt"></i>
                <span>{{ $t('dashboard.requested_on', { date: formatDate(req.created_at) }) }}</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2 flex-shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-white/5">
              <button 
                @click="deleteRequest(req.id)"
                class="px-3 py-2 rounded-xl bg-white/5 hover:bg-red-500/20 text-gray-400 hover:text-red-400 text-xs font-semibold transition border border-white/5 flex items-center gap-1.5 cursor-pointer"
                :title="$t('dashboard.delete_request')"
              >
                <i class="fas fa-trash-alt text-[10px]"></i>
                <span>{{ $t('dashboard.delete_request') }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="requests.links && requests.links.length > 3" class="flex justify-center gap-1 pt-6">
          <Link 
            v-for="(link, i) in requests.links" 
            :key="i"
            :href="link.url || '#'"
            v-html="link.label"
            :class="[
              link.active ? 'bg-netflix text-white font-bold' : 'bg-white/5 text-gray-400 hover:text-white',
              !link.url ? 'opacity-40 pointer-events-none' : ''
            ]"
            class="px-3 py-1.5 rounded-lg text-xs transition"
          />
        </div>
      </div>

      <!-- Empty State -->
      <div 
        v-else 
        class="py-20 px-4 rounded-3xl bg-white/5 border border-white/10 text-center space-y-4 max-w-lg mx-auto"
      >
        <div class="w-16 h-16 rounded-3xl bg-netflix/10 text-netflix flex items-center justify-center mx-auto text-2xl shadow-inner">
          <i class="fas fa-film"></i>
        </div>
        <div>
          <h3 class="text-lg font-bold text-white">{{ $t('dashboard.no_requests_title') }}</h3>
          <p class="text-xs text-gray-400 mt-1.5 max-w-sm mx-auto leading-relaxed">
            {{ $t('dashboard.no_requests_desc') }}
          </p>
        </div>
        <div class="pt-2">
          <button 
            @click="showModal = true" 
            class="px-5 py-2.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg flex items-center gap-2 mx-auto cursor-pointer"
          >
            <i class="fas fa-plus"></i>
            <span>{{ $t('dashboard.submit_request') }}</span>
          </button>
        </div>
      </div>

      <!-- Request Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/80 backdrop-blur-md" @click="showModal = false"></div>

        <!-- Modal Box -->
        <div class="relative w-full max-w-lg rounded-3xl bg-gray-900 border border-white/15 p-6 sm:p-8 shadow-2xl z-10 space-y-6">
          <div class="flex items-center justify-between pb-4 border-b border-white/10">
            <div>
              <h3 class="text-lg font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-film text-netflix text-sm"></i>
                <span>{{ $t('dashboard.modal_request_title') }}</span>
              </h3>
              <p class="text-xs text-gray-400 mt-0.5">{{ $t('dashboard.request_lead') }}</p>
            </div>
            <button 
              @click="showModal = false" 
              class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white flex items-center justify-center transition text-sm cursor-pointer"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="submitForm" class="space-y-4">
            <!-- Media Type Switcher -->
            <div>
              <label class="block text-xs font-bold text-gray-300 mb-2">{{ $t('dashboard.request_type') }}</label>
              <div class="grid grid-cols-2 gap-3">
                <button 
                  type="button"
                  @click="form.type = 'movie'"
                  :class="[form.type === 'movie' ? 'bg-netflix text-white border-netflix' : 'bg-white/5 text-gray-400 border-white/10 hover:bg-white/10']"
                  class="py-2.5 px-4 rounded-xl text-xs font-bold transition border flex items-center justify-center gap-2 cursor-pointer"
                >
                  <i class="fas fa-film"></i>
                  <span>{{ $t('home.movie') }}</span>
                </button>
                <button 
                  type="button"
                  @click="form.type = 'tv'"
                  :class="[form.type === 'tv' ? 'bg-netflix text-white border-netflix' : 'bg-white/5 text-gray-400 border-white/10 hover:bg-white/10']"
                  class="py-2.5 px-4 rounded-xl text-xs font-bold transition border flex items-center justify-center gap-2 cursor-pointer"
                >
                  <i class="fas fa-tv"></i>
                  <span>{{ $t('home.tv_show') }}</span>
                </button>
              </div>
            </div>

            <!-- Title -->
            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1.5">
                {{ $t('dashboard.request_title_label') }} <span class="text-netflix">*</span>
              </label>
              <input 
                type="text" 
                v-model="form.title" 
                required 
                :placeholder="$t('dashboard.request_title_placeholder')"
                class="w-full h-11 bg-black/60 border border-white/10 rounded-xl px-4 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
              />
              <span v-if="form.errors.title" class="text-[11px] text-red-400 mt-1 block">
                {{ form.errors.title }}
              </span>
            </div>

            <!-- Release Year -->
            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1.5">
                {{ $t('dashboard.request_year') }}
              </label>
              <input 
                type="text" 
                v-model="form.release_year" 
                maxlength="4"
                placeholder="2026"
                class="w-full h-11 bg-black/60 border border-white/10 rounded-xl px-4 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
              />
            </div>

            <!-- Notes / TMDB URL -->
            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1.5">
                {{ $t('dashboard.request_notes') }}
              </label>
              <textarea 
                v-model="form.user_notes" 
                rows="3"
                :placeholder="$t('dashboard.request_notes_placeholder')"
                class="w-full bg-black/60 border border-white/10 rounded-xl p-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition resize-none"
              ></textarea>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-white/10">
              <button 
                type="button" 
                @click="showModal = false" 
                class="px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 text-xs font-bold transition cursor-pointer"
              >
                {{ $t('common.cancel') }}
              </button>
              <button 
                type="submit" 
                :disabled="form.processing"
                class="px-5 py-2.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg flex items-center gap-2 disabled:opacity-50 cursor-pointer"
              >
                <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                <span>{{ form.processing ? $t('dashboard.saving') : $t('dashboard.request_submit') }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import DashboardLayout from './Layout.vue';

const props = defineProps({
  requests: {
    type: Object,
    default: () => ({ data: [], links: [], total: 0 }),
  },
});

const requestsList = computed(() => {
  return props.requests?.data || [];
});

const showModal = ref(false);

const form = useForm({
  title: '',
  type: 'movie',
  release_year: '',
  user_notes: '',
});

const submitForm = () => {
  form.post('/dashboard/requests', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      showModal.value = false;
    },
  });
};

const deleteRequest = (id) => {
  if (confirm('Are you sure you want to delete this content request?')) {
    router.delete(`/dashboard/requests/${id}`, {
      preserveScroll: true,
    });
  }
};

const statusBadgeClass = (status) => {
  switch (status) {
    case 'available':
      return 'bg-green-500/20 text-green-400 border-green-500/30';
    case 'in_review':
      return 'bg-blue-500/20 text-blue-400 border-blue-500/30';
    case 'rejected':
      return 'bg-red-500/20 text-red-400 border-red-500/30';
    default:
      return 'bg-amber-500/20 text-amber-400 border-amber-500/30';
  }
};

const statusIcon = (status) => {
  switch (status) {
    case 'available':
      return 'fas fa-check-circle';
    case 'in_review':
      return 'fas fa-spinner fa-spin';
    case 'rejected':
      return 'fas fa-times-circle';
    default:
      return 'fas fa-clock';
  }
};

const formatStatus = (status) => {
  switch (status) {
    case 'available':
      return 'Available in Library';
    case 'in_review':
      return 'In Review / Sourcing';
    case 'rejected':
      return 'Declined / Unavailable';
    default:
      return 'Pending Review';
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
