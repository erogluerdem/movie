<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-white/10">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <i class="fas fa-envelope-open-text text-amber-400"></i>
            <span>Content Requests</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/10 text-gray-300">
              {{ counts.all || 0 }}
            </span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">Review user submissions, update status, and publish curator feedback notes.</p>
        </div>
      </div>

      <!-- Filter Tabs & Search -->
      <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0 scrollbar-none w-full md:w-auto">
          <button 
            @click="setStatusFilter('')"
            :class="[!statusFilter ? 'bg-netflix text-white font-bold' : 'bg-white/5 text-gray-400 hover:text-white']"
            class="px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 flex-shrink-0"
          >
            <span>All</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/40">{{ counts.all || 0 }}</span>
          </button>

          <button 
            @click="setStatusFilter('pending')"
            :class="[statusFilter === 'pending' ? 'bg-amber-500 text-black font-extrabold shadow' : 'bg-white/5 text-amber-400 hover:text-white']"
            class="px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 flex-shrink-0"
          >
            <span>Pending</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/20">{{ counts.pending || 0 }}</span>
          </button>

          <button 
            @click="setStatusFilter('in_review')"
            :class="[statusFilter === 'in_review' ? 'bg-blue-600 text-white font-bold' : 'bg-white/5 text-blue-400 hover:text-white']"
            class="px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 flex-shrink-0"
          >
            <span>In Review</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/40">{{ counts.in_review || 0 }}</span>
          </button>

          <button 
            @click="setStatusFilter('available')"
            :class="[statusFilter === 'available' ? 'bg-green-600 text-white font-bold' : 'bg-white/5 text-green-400 hover:text-white']"
            class="px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 flex-shrink-0"
          >
            <span>Available</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/40">{{ counts.available || 0 }}</span>
          </button>

          <button 
            @click="setStatusFilter('rejected')"
            :class="[statusFilter === 'rejected' ? 'bg-red-600 text-white font-bold' : 'bg-white/5 text-red-400 hover:text-white']"
            class="px-3 py-1.5 rounded-xl text-xs transition flex items-center gap-1.5 flex-shrink-0"
          >
            <span>Declined</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/40">{{ counts.rejected || 0 }}</span>
          </button>
        </div>

        <!-- Search Input -->
        <div class="relative w-full md:w-64">
          <input 
            type="text" 
            v-model="search" 
            @keydown.enter="applyFilters"
            placeholder="Search request or user..."
            class="w-full h-9 bg-black/60 border border-white/10 rounded-xl pl-8 pr-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix"
          />
          <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-gray-500"></i>
        </div>
      </div>

      <!-- Requests Table -->
      <div class="rounded-2xl border border-white/10 bg-gray-900/60 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-gray-300 divide-y divide-white/10">
            <thead class="bg-white/5 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4">User</th>
                <th class="py-3 px-4">Title & Type</th>
                <th class="py-3 px-4">Notes</th>
                <th class="py-3 px-4 text-center">Status</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr 
                v-for="req in requests.data" 
                :key="req.id"
                class="hover:bg-white/5 transition"
              >
                <!-- User -->
                <td class="py-3 px-4 whitespace-nowrap">
                  <div class="flex items-center gap-2.5">
                    <img 
                      :src="req.user?.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop'" 
                      class="w-7 h-7 rounded-full object-cover"
                    />
                    <div>
                      <div class="font-bold text-white">{{ req.user?.name || 'Deleted User' }}</div>
                      <div class="text-[10px] text-gray-500">{{ req.user?.email || 'N/A' }}</div>
                    </div>
                  </div>
                </td>

                <!-- Title & Type -->
                <td class="py-3 px-4">
                  <div class="flex items-center gap-2">
                    <span 
                      :class="req.type === 'tv' ? 'bg-purple-500/20 text-purple-400 border-purple-500/30' : 'bg-blue-500/20 text-blue-400 border-blue-500/30'"
                      class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded border"
                    >
                      {{ req.type === 'tv' ? 'TV' : 'Movie' }}
                    </span>
                    <span class="font-bold text-white text-sm">{{ req.title }}</span>
                    <span v-if="req.release_year" class="text-xs text-gray-500">({{ req.release_year }})</span>
                  </div>
                  <div class="text-[10px] text-gray-500 mt-0.5">
                    Requested on {{ formatDate(req.created_at) }}
                  </div>
                </td>

                <!-- Notes & Admin Note -->
                <td class="py-3 px-4 max-w-xs">
                  <div v-if="req.user_notes" class="text-gray-300 italic text-[11px] truncate">
                    "{{ req.user_notes }}"
                  </div>
                  <div v-if="req.admin_notes" class="text-red-300 text-[11px] font-semibold truncate mt-0.5 flex items-center gap-1">
                    <i class="fas fa-comment-dots text-netflix"></i>
                    <span>Admin: {{ req.admin_notes }}</span>
                  </div>
                </td>

                <!-- Status Badge -->
                <td class="py-3 px-4 text-center whitespace-nowrap">
                  <span 
                    :class="statusBadge(req.status)"
                    class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase border inline-block"
                  >
                    {{ req.status }}
                  </span>
                </td>

                <!-- Quick Actions -->
                <td class="py-3 px-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <button 
                      @click="openStatusModal(req)"
                      class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition flex items-center gap-1"
                      title="Update Status & Note"
                    >
                      <i class="fas fa-edit text-[10px]"></i>
                      <span>Update</span>
                    </button>

                    <button 
                      @click="deleteRequest(req)"
                      class="w-7 h-7 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 flex items-center justify-center transition"
                      title="Delete Request"
                    >
                      <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="requests.data.length === 0">
                <td colspan="5" class="py-12 text-center text-gray-400">
                  No requests found for this filter.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="requests.links && requests.links.length > 3" class="p-4 border-t border-white/10 flex justify-center gap-1">
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

      <!-- Update Status Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-md" @click="showModal = false"></div>

        <div class="relative w-full max-w-md rounded-3xl bg-gray-900 border border-white/15 p-6 shadow-2xl z-10 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <h3 class="text-sm font-extrabold text-white">
              Update Status: {{ activeReq?.title }}
            </h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-white text-sm">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="submitStatusUpdate" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1.5">Status</label>
              <select 
                v-model="statusForm.status"
                class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix cursor-pointer"
              >
                <option value="pending">Pending (Review Queue)</option>
                <option value="in_review">In Review (Sourcing & Encoding)</option>
                <option value="available">Available in Library</option>
                <option value="rejected">Declined / Unavailable</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1.5">Admin Note to User</label>
              <textarea 
                v-model="statusForm.admin_notes" 
                rows="3" 
                placeholder="e.g. Added to 4K Ultra HD library, or Not available on digital stream yet."
                class="w-full bg-black/60 border border-white/10 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-netflix"
              ></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-white/10">
              <button 
                type="button" 
                @click="showModal = false"
                class="px-4 py-2 rounded-xl bg-white/5 text-gray-300 text-xs font-bold"
              >
                Cancel
              </button>
              <button 
                type="submit"
                class="px-5 py-2 rounded-xl bg-netflix text-white text-xs font-bold shadow"
              >
                Save Changes
              </button>
            </div>
          </form>
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
  requests: {
    type: Object,
    default: () => ({ data: [], links: [], total: 0 }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  counts: {
    type: Object,
    default: () => ({}),
  },
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const showModal = ref(false);
const activeReq = ref(null);
const statusForm = ref({
  status: 'pending',
  admin_notes: '',
});

const applyFilters = () => {
  router.get('/admin/requests', {
    search: search.value || undefined,
    status: statusFilter.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const setStatusFilter = (status) => {
  statusFilter.value = status;
  applyFilters();
};

const openStatusModal = (req) => {
  activeReq.value = req;
  statusForm.value = {
    status: req.status,
    admin_notes: req.admin_notes || '',
  };
  showModal.value = true;
};

const submitStatusUpdate = () => {
  router.put(`/admin/requests/${activeReq.value.id}`, statusForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      showModal.value = false;
    },
  });
};

const deleteRequest = (req) => {
  if (confirm(`Delete request "${req.title}"?`)) {
    router.delete(`/admin/requests/${req.id}`, {
      preserveScroll: true,
    });
  }
};

const statusBadge = (status) => {
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

const formatDate = (str) => {
  if (!str) return 'Recently';
  return new Date(str).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
</script>
