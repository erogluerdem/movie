<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-white/10">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <i class="fas fa-futbol text-emerald-400"></i>
            <span>Live Sports Management</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/10 text-gray-300">
              {{ matches.total || 0 }}
            </span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">Schedule matches, configure live broadcast embeds, and toggle active streams.</p>
        </div>

        <button 
          @click="openCreateModal"
          class="px-4 py-2.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg flex items-center gap-2 flex-shrink-0"
        >
          <i class="fas fa-plus"></i>
          <span>Add Match</span>
        </button>
      </div>

      <!-- Filters & Search Bar -->
      <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-80">
          <input 
            type="text" 
            v-model="search" 
            @keydown.enter="applyFilters"
            placeholder="Search team, league, or title..."
            class="w-full h-10 bg-black/60 border border-white/10 rounded-xl pl-9 pr-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
          />
          <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500"></i>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
          <select 
            v-model="liveFilter" 
            @change="applyFilters"
            class="h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-gray-300 focus:outline-none focus:border-netflix cursor-pointer"
          >
            <option value="">All Statuses</option>
            <option value="1">Live Now Only</option>
            <option value="0">Upcoming / Ended</option>
          </select>

          <button 
            @click="resetFilters" 
            class="px-3 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white text-xs font-semibold transition"
          >
            Reset
          </button>
        </div>
      </div>

      <!-- Sports Table -->
      <div class="rounded-2xl border border-white/10 bg-gray-900/60 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-gray-300 divide-y divide-white/10">
            <thead class="bg-white/5 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4">Fixture</th>
                <th class="py-3 px-4">League</th>
                <th class="py-3 px-4">Time & Status</th>
                <th class="py-3 px-4 text-center">Live Status</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr 
                v-for="match in matches.data" 
                :key="match.id"
                class="hover:bg-white/5 transition"
              >
                <!-- Teams -->
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                      <img 
                        :src="match.home_logo || '/images/sports/team-default.png'" 
                        class="w-6 h-6 object-contain rounded-full bg-white/10 p-0.5"
                        @error="$event.target.src = 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=50&auto=format&fit=crop'"
                      />
                      <span class="text-gray-500 font-bold text-[10px]">vs</span>
                      <img 
                        :src="match.away_logo || '/images/sports/team-default.png'" 
                        class="w-6 h-6 object-contain rounded-full bg-white/10 p-0.5"
                        @error="$event.target.src = 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=50&auto=format&fit=crop'"
                      />
                    </div>
                    <div>
                      <div class="font-bold text-white text-sm">{{ match.title }}</div>
                      <div class="text-[11px] text-gray-400">{{ match.team_home }} vs {{ match.team_away }}</div>
                    </div>
                  </div>
                </td>

                <!-- League -->
                <td class="py-3 px-4 whitespace-nowrap">
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-white/5 border border-white/10 text-gray-300">
                    {{ match.league || 'Football' }}
                  </span>
                </td>

                <!-- Time -->
                <td class="py-3 px-4 whitespace-nowrap">
                  <div class="font-bold text-white">{{ match.match_time || 'TBD' }}</div>
                  <div class="text-[11px] text-gray-400">{{ match.status }}</div>
                </td>

                <!-- Live Toggle -->
                <td class="py-3 px-4 text-center">
                  <button 
                    @click="toggleLive(match)"
                    :class="match.is_live ? 'bg-red-500/20 text-red-400 border-red-500/40' : 'bg-white/5 text-gray-500 border-white/10'"
                    class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase border transition flex items-center gap-1.5 mx-auto"
                  >
                    <span v-if="match.is_live" class="w-1.5 h-1.5 rounded-full bg-red-400 animate-ping"></span>
                    <span>{{ match.is_live ? 'LIVE NOW' : 'OFFLINE' }}</span>
                  </button>
                </td>

                <!-- Actions -->
                <td class="py-3 px-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <button 
                      @click="openEditModal(match)"
                      class="w-7 h-7 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 flex items-center justify-center transition"
                      title="Edit Match"
                    >
                      <i class="fas fa-edit"></i>
                    </button>

                    <button 
                      @click="deleteMatch(match)"
                      class="w-7 h-7 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 flex items-center justify-center transition"
                      title="Delete Match"
                    >
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="matches.data.length === 0">
                <td colspan="5" class="py-12 text-center text-gray-400">
                  No matches found. Click "Add Match" above.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="matches.links && matches.links.length > 3" class="p-4 border-t border-white/10 flex justify-center gap-1">
          <Link 
            v-for="(link, i) in matches.links" 
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

      <!-- Create / Edit Match Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-md" @click="showModal = false"></div>

        <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-3xl bg-gray-900 border border-white/15 p-6 sm:p-8 shadow-2xl z-10 space-y-5">
          <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
              <i class="fas fa-futbol text-emerald-400"></i>
              <span>{{ isEditing ? 'Edit Match' : 'Add Live Match' }}</span>
            </h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-white text-sm">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1">Match Title *</label>
              <input 
                type="text" 
                v-model="form.title" 
                required 
                placeholder="e.g. Manchester City vs Real Madrid"
                class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Home Team *</label>
                <input 
                  type="text" 
                  v-model="form.team_home" 
                  required 
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Away Team *</label>
                <input 
                  type="text" 
                  v-model="form.team_away" 
                  required 
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Home Logo URL</label>
                <input 
                  type="text" 
                  v-model="form.home_logo" 
                  placeholder="https://..."
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Away Logo URL</label>
                <input 
                  type="text" 
                  v-model="form.away_logo" 
                  placeholder="https://..."
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">League / Tournament</label>
                <input 
                  type="text" 
                  v-model="form.league" 
                  placeholder="UEFA Champions League"
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Match Time</label>
                <input 
                  type="text" 
                  v-model="form.match_time" 
                  placeholder="Today, 21:00"
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1">Stream Embed URL</label>
              <input 
                type="url" 
                v-model="form.stream_url" 
                placeholder="https://live-embed.stream/..."
                class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
              />
            </div>

            <div class="p-3 rounded-xl bg-black/40 border border-white/5">
              <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-300">
                <input type="checkbox" v-model="form.is_live" class="rounded bg-black border-white/20 text-red-500 focus:ring-0" />
                <span class="text-red-400">Mark as Live Stream Now</span>
              </label>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-white/10">
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
                {{ isEditing ? 'Update Match' : 'Create Match' }}
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
  matches: {
    type: Object,
    default: () => ({ data: [], links: [], total: 0 }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const search = ref(props.filters.search || '');
const liveFilter = ref(props.filters.is_live ?? '');

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = ref({
  title: '',
  league: '',
  team_home: '',
  team_away: '',
  home_logo: '',
  away_logo: '',
  match_time: '',
  status: 'Upcoming',
  is_live: false,
  stream_url: '',
});

const applyFilters = () => {
  router.get('/admin/sports', {
    search: search.value || undefined,
    is_live: liveFilter.value !== '' ? liveFilter.value : undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  search.value = '';
  liveFilter.value = '';
  applyFilters();
};

const openCreateModal = () => {
  isEditing.value = false;
  editingId.value = null;
  form.value = {
    title: '',
    league: 'Premier League',
    team_home: '',
    team_away: '',
    home_logo: '',
    away_logo: '',
    match_time: 'Today, 21:00',
    status: 'Upcoming',
    is_live: false,
    stream_url: '',
  };
  showModal.value = true;
};

const openEditModal = (match) => {
  isEditing.value = true;
  editingId.value = match.id;
  form.value = {
    title: match.title,
    league: match.league || '',
    team_home: match.team_home,
    team_away: match.team_away,
    home_logo: match.home_logo || '',
    away_logo: match.away_logo || '',
    match_time: match.match_time || '',
    status: match.status || 'Upcoming',
    is_live: Boolean(match.is_live),
    stream_url: match.stream_url || '',
  };
  showModal.value = true;
};

const submitForm = () => {
  if (isEditing.value) {
    router.put(`/admin/sports/${editingId.value}`, form.value, {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false;
      },
    });
  } else {
    router.post('/admin/sports', form.value, {
      preserveScroll: true,
      onSuccess: () => {
        showModal.value = false;
      },
    });
  }
};

const toggleLive = (match) => {
  router.post(`/admin/sports/${match.id}/toggle-live`, {}, {
    preserveScroll: true,
  });
};

const deleteMatch = (match) => {
  if (confirm(`Delete match "${match.title}"?`)) {
    router.delete(`/admin/sports/${match.id}`, {
      preserveScroll: true,
    });
  }
};
</script>
