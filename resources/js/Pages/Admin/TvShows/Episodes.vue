<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Back Link & Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-white/10">
        <div class="flex items-center gap-4">
          <Link 
            href="/admin/tv-shows" 
            class="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white flex items-center justify-center transition"
          >
            <i class="fas fa-arrow-left text-xs"></i>
          </Link>

          <div class="flex items-center gap-3">
            <img 
              :src="show.poster_url || show.poster_path || 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=100&auto=format&fit=crop'" 
              class="w-10 h-14 object-cover rounded-lg shadow"
            />
            <div>
              <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <span>{{ show.title }}</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-400">
                  {{ show.is_anime ? 'Anime' : 'Series' }}
                </span>
              </h1>
              <p class="text-xs text-gray-400 mt-0.5">
                {{ show.seasons?.length || 0 }} Season(s) · {{ totalEpisodesCount }} Total Episode(s)
              </p>
            </div>
          </div>
        </div>

        <!-- Add Season Form -->
        <form @submit.prevent="addSeason" class="flex items-center gap-2">
          <input 
            type="number" 
            v-model="newSeasonNum" 
            min="1" 
            placeholder="Season #"
            required
            class="w-24 h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix"
          />
          <button 
            type="submit"
            class="px-3.5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition flex items-center gap-1.5"
          >
            <i class="fas fa-plus text-[10px]"></i>
            <span>Add Season</span>
          </button>
        </form>
      </div>

      <!-- Season Tabs & Content -->
      <div v-if="show.seasons && show.seasons.length > 0" class="space-y-6">
        <!-- Season Selector Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
          <button 
            v-for="season in show.seasons" 
            :key="season.id"
            @click="activeSeasonId = season.id"
            :class="[
              activeSeasonId === season.id 
                ? 'bg-netflix text-white font-bold shadow-lg shadow-red-600/30' 
                : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10'
            ]"
            class="px-4 py-2 rounded-xl text-xs transition flex items-center gap-2 flex-shrink-0"
          >
            <span>{{ season.name || `Season ${season.season_number}` }}</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/40">
              {{ season.episodes?.length || 0 }}
            </span>
          </button>
        </div>

        <!-- Current Season Panel -->
        <div v-if="currentSeason" class="p-6 rounded-3xl bg-gray-900/60 border border-white/10 space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-white/10">
            <div>
              <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fas fa-layer-group text-netflix"></i>
                <span>{{ currentSeason.name || `Season ${currentSeason.season_number}` }}</span>
              </h2>
              <p class="text-xs text-gray-400 mt-0.5">
                {{ currentSeason.episodes?.length || 0 }} episode(s) configured.
              </p>
            </div>

            <div class="flex items-center gap-2">
              <button 
                @click="openAddEpisodeModal(currentSeason.id)"
                class="px-3.5 py-2 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow flex items-center gap-1.5"
              >
                <i class="fas fa-plus"></i>
                <span>Add Episode</span>
              </button>

              <button 
                @click="deleteSeason(currentSeason.id)"
                class="px-3 py-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-bold transition border border-red-500/20"
                title="Delete Season"
              >
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </div>

          <!-- Episodes Table -->
          <div v-if="currentSeason.episodes && currentSeason.episodes.length > 0" class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-300 divide-y divide-white/10">
              <thead class="bg-white/5 text-[11px] font-bold text-gray-400 uppercase">
                <tr>
                  <th class="py-3 px-3 w-16 text-center">Ep #</th>
                  <th class="py-3 px-4">Episode Title</th>
                  <th class="py-3 px-4">Air Date & Duration</th>
                  <th class="py-3 px-4 text-center">Stream Servers</th>
                  <th class="py-3 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/5">
                <tr 
                  v-for="ep in currentSeason.episodes" 
                  :key="ep.id"
                  class="hover:bg-white/5 transition"
                >
                  <td class="py-3 px-3 text-center font-bold text-white">
                    <span class="w-7 h-7 rounded-lg bg-white/5 flex items-center justify-center mx-auto text-xs">
                      {{ ep.episode_number }}
                    </span>
                  </td>

                  <td class="py-3 px-4">
                    <div class="font-bold text-white text-sm">{{ ep.name }}</div>
                    <div class="text-[11px] text-gray-400 line-clamp-1 mt-0.5">{{ ep.overview || 'No synopsis added.' }}</div>
                  </td>

                  <td class="py-3 px-4 text-gray-400">
                    <div>{{ ep.air_date || 'N/A' }}</div>
                    <div class="text-[10px] text-gray-500">{{ ep.duration || '24m' }}</div>
                  </td>

                  <td class="py-3 px-4 text-center">
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-white/10 text-white">
                      {{ ep.stream_servers?.length || 0 }} Server(s)
                    </span>
                  </td>

                  <td class="py-3 px-4 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                      <button 
                        @click="openEditEpisodeModal(ep)"
                        class="w-7 h-7 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs transition"
                        title="Edit Episode"
                      >
                        <i class="fas fa-edit"></i>
                      </button>

                      <button 
                        @click="deleteEpisode(ep.id)"
                        class="w-7 h-7 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 flex items-center justify-center text-xs transition"
                        title="Delete Episode"
                      >
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else class="text-center py-10 text-gray-500 text-xs">
            No episodes in this season yet. Click "Add Episode" above to configure episodes and stream URLs.
          </div>
        </div>
      </div>

      <div v-else class="p-12 text-center rounded-3xl bg-white/5 border border-white/10 text-gray-400 text-xs space-y-3">
        <p>No seasons found for this show.</p>
        <button 
          @click="addDefaultSeason"
          class="px-4 py-2 rounded-xl bg-netflix text-white font-bold"
        >
          Initialize Season 1
        </button>
      </div>

      <!-- Episode Modal -->
      <div v-if="showEpModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-md" @click="showEpModal = false"></div>

        <div class="relative w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-3xl bg-gray-900 border border-white/15 p-6 sm:p-8 shadow-2xl z-10 space-y-5">
          <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <h3 class="text-base font-extrabold text-white">
              {{ isEditingEp ? 'Edit Episode' : 'Add Episode' }}
            </h3>
            <button @click="showEpModal = false" class="text-gray-400 hover:text-white text-sm">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="submitEpForm" class="space-y-4">
            <div class="grid grid-cols-3 gap-3">
              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Episode #</label>
                <input 
                  type="number" 
                  min="1"
                  v-model="epForm.episode_number" 
                  required 
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
                />
              </div>

              <div class="col-span-2">
                <label class="block text-xs font-bold text-gray-300 mb-1">Episode Title *</label>
                <input 
                  type="text" 
                  v-model="epForm.name" 
                  required 
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Air Date</label>
                <input 
                  type="text" 
                  v-model="epForm.air_date" 
                  placeholder="YYYY-MM-DD"
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Duration</label>
                <input 
                  type="text" 
                  v-model="epForm.duration" 
                  placeholder="45m"
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1">Overview</label>
              <textarea 
                v-model="epForm.overview" 
                rows="2"
                class="w-full bg-black/60 border border-white/10 rounded-xl p-3 text-xs text-white"
              ></textarea>
            </div>

            <!-- Stream Servers for Episode -->
            <div class="space-y-2 pt-2 border-t border-white/10">
              <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-gray-300">Stream Servers</label>
                <button 
                  type="button" 
                  @click="addEpServer" 
                  class="text-[11px] font-bold text-netflix hover:underline"
                >
                  + Add Server
                </button>
              </div>

              <div v-for="(srv, si) in epForm.stream_servers" :key="si" class="flex gap-2">
                <input 
                  type="text" 
                  v-model="srv.server_name" 
                  placeholder="Server Name"
                  class="h-8 bg-black border border-white/10 rounded-lg px-2 text-xs text-white w-1/3"
                />
                <input 
                  type="url" 
                  v-model="srv.embed_url" 
                  placeholder="https://embed..."
                  class="h-8 bg-black border border-white/10 rounded-lg px-2 text-xs text-white flex-1"
                />
                <button 
                  type="button" 
                  @click="epForm.stream_servers.splice(si, 1)" 
                  class="text-red-400 p-1"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t border-white/10">
              <button 
                type="button" 
                @click="showEpModal = false"
                class="px-4 py-2 rounded-xl bg-white/5 text-gray-300 text-xs font-bold"
              >
                Cancel
              </button>
              <button 
                type="submit"
                class="px-5 py-2 rounded-xl bg-netflix text-white text-xs font-bold shadow"
              >
                {{ isEditingEp ? 'Update Episode' : 'Save Episode' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '../Layout.vue';

const props = defineProps({
  show: {
    type: Object,
    required: true,
  },
});

const activeSeasonId = ref(props.show.seasons?.[0]?.id || null);
const newSeasonNum = ref((props.show.seasons?.length || 0) + 1);

const currentSeason = computed(() => {
  return props.show.seasons?.find(s => s.id === activeSeasonId.value) || props.show.seasons?.[0];
});

const totalEpisodesCount = computed(() => {
  if (!props.show.seasons) return 0;
  return props.show.seasons.reduce((acc, s) => acc + (s.episodes?.length || 0), 0);
});

const addSeason = () => {
  router.post(`/admin/tv-shows/${props.show.id}/seasons`, {
    season_number: newSeasonNum.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      newSeasonNum.value = (props.show.seasons?.length || 0) + 1;
    },
  });
};

const addDefaultSeason = () => {
  router.post(`/admin/tv-shows/${props.show.id}/seasons`, {
    season_number: 1,
  }, {
    preserveScroll: true,
  });
};

const deleteSeason = (id) => {
  if (confirm('Delete this season and all its episodes?')) {
    router.delete(`/admin/seasons/${id}`, {
      preserveScroll: true,
    });
  }
};

// Episode modal
const showEpModal = ref(false);
const isEditingEp = ref(false);
const editingEpId = ref(null);
const targetSeasonId = ref(null);

const epForm = ref({
  episode_number: 1,
  name: '',
  overview: '',
  air_date: '',
  duration: '24m',
  stream_servers: [],
});

const openAddEpisodeModal = (seasonId) => {
  isEditingEp.value = false;
  editingEpId.value = null;
  targetSeasonId.value = seasonId;
  const nextEpNum = (currentSeason.value?.episodes?.length || 0) + 1;
  epForm.value = {
    episode_number: nextEpNum,
    name: `Episode ${nextEpNum}`,
    overview: '',
    air_date: '',
    duration: '24m',
    stream_servers: [
      { server_name: 'VidCloud HD', embed_url: '', quality: '1080p' }
    ],
  };
  showEpModal.value = true;
};

const openEditEpisodeModal = (ep) => {
  isEditingEp.value = true;
  editingEpId.value = ep.id;
  epForm.value = {
    episode_number: ep.episode_number,
    name: ep.name,
    overview: ep.overview || '',
    air_date: ep.air_date || '',
    duration: ep.duration || '24m',
    stream_servers: Array.isArray(ep.stream_servers) ? [...ep.stream_servers] : [],
  };
  showEpModal.value = true;
};

const addEpServer = () => {
  epForm.value.stream_servers.push({
    server_name: `Server ${epForm.value.stream_servers.length + 1}`,
    embed_url: '',
    quality: '1080p',
  });
};

const submitEpForm = () => {
  if (isEditingEp.value) {
    router.put(`/admin/episodes/${editingEpId.value}`, epForm.value, {
      preserveScroll: true,
      onSuccess: () => {
        showEpModal.value = false;
      },
    });
  } else {
    router.post(`/admin/seasons/${targetSeasonId.value}/episodes`, epForm.value, {
      preserveScroll: true,
      onSuccess: () => {
        showEpModal.value = false;
      },
    });
  }
};

const deleteEpisode = (id) => {
  if (confirm('Delete this episode?')) {
    router.delete(`/admin/episodes/${id}`, {
      preserveScroll: true,
    });
  }
};
</script>
