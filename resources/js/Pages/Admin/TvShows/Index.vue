<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-white/10">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <i class="fas fa-tv text-purple-400"></i>
            <span>TV Shows & Anime</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/10 text-gray-300">
              {{ shows.total || 0 }}
            </span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">Manage series, anime catalogs, multi-season structure, and episode servers.</p>
        </div>

        <div class="flex items-center gap-2.5 flex-shrink-0">
          <button 
            @click="showTmdbModal = true"
            class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition shadow-lg flex items-center gap-2"
          >
            <i class="fas fa-cloud-download-alt"></i>
            <span>Import from TMDB</span>
          </button>

          <button 
            @click="openCreateModal"
            class="px-4 py-2.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg flex items-center gap-2"
          >
            <i class="fas fa-plus"></i>
            <span>Add New Show</span>
          </button>
        </div>
      </div>

      <!-- Filters & Search Bar -->
      <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-80">
          <input 
            type="text" 
            v-model="search" 
            @keydown.enter="applyFilters"
            placeholder="Search TV show title, slug, or TMDB ID..."
            class="w-full h-10 bg-black/60 border border-white/10 rounded-xl pl-9 pr-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
          />
          <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500"></i>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
          <select 
            v-model="animeFilter" 
            @change="applyFilters"
            class="h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-gray-300 focus:outline-none focus:border-netflix cursor-pointer"
          >
            <option value="">All Categories</option>
            <option value="1">Anime Only</option>
            <option value="0">Standard TV Shows</option>
          </select>

          <select 
            v-model="trendingFilter" 
            @change="applyFilters"
            class="h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-gray-300 focus:outline-none focus:border-netflix cursor-pointer"
          >
            <option value="">All Trending Status</option>
            <option value="1">Trending Only</option>
            <option value="0">Not Trending</option>
          </select>

          <button 
            @click="resetFilters" 
            class="px-3 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white text-xs font-semibold transition whitespace-nowrap"
          >
            Reset
          </button>
        </div>
      </div>

      <!-- TV Shows Table -->
      <div class="rounded-2xl border border-white/10 bg-gray-900/60 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-gray-300 divide-y divide-white/10">
            <thead class="bg-white/5 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4">Show</th>
                <th class="py-3 px-4">Rating & Air Date</th>
                <th class="py-3 px-4 text-center">Category</th>
                <th class="py-3 px-4 text-center">Trending</th>
                <th class="py-3 px-4 text-center">Seasons & Episodes</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr 
                v-for="show in shows.data" 
                :key="show.id"
                class="hover:bg-white/5 transition group"
              >
                <!-- Title & Poster -->
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <img 
                      :src="show.poster_url || show.poster_path || 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=100&auto=format&fit=crop'" 
                      :alt="show.title"
                      class="w-10 h-14 object-cover rounded-lg flex-shrink-0 shadow"
                    />
                    <div class="min-w-0">
                      <div class="font-bold text-white text-sm truncate max-w-xs group-hover:text-netflix transition-colors">
                        {{ show.title }}
                      </div>
                      <div class="text-[11px] text-gray-400 truncate max-w-xs font-mono mt-0.5">
                        /tv-show/{{ show.slug }}
                      </div>
                      <span v-if="show.tmdb_id" class="text-[10px] text-gray-500 font-mono">
                        TMDB: {{ show.tmdb_id }}
                      </span>
                    </div>
                  </div>
                </td>

                <!-- Rating & Air Date -->
                <td class="py-3 px-4 whitespace-nowrap">
                  <div class="flex items-center gap-1.5 font-bold text-amber-400">
                    <i class="fas fa-star text-[10px]"></i>
                    <span>{{ show.vote_average || '0.0' }}</span>
                  </div>
                  <div class="text-gray-400 text-[11px] mt-0.5">
                    {{ show.first_air_date ? show.first_air_date.substring(0, 4) : '2025' }}
                  </div>
                </td>

                <!-- Anime / TV Category -->
                <td class="py-3 px-4 text-center">
                  <button 
                    @click="toggleAnime(show)"
                    :class="show.is_anime ? 'bg-purple-500/20 text-purple-400 border-purple-500/40' : 'bg-blue-500/20 text-blue-400 border-blue-500/40'"
                    class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase border transition"
                  >
                    {{ show.is_anime ? 'Anime' : 'TV Series' }}
                  </button>
                </td>

                <!-- Trending Switch -->
                <td class="py-3 px-4 text-center">
                  <button 
                    @click="toggleTrending(show)"
                    :class="show.is_trending ? 'bg-red-500/20 text-red-400 border-red-500/40' : 'bg-white/5 text-gray-500 border-white/10'"
                    class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase border transition"
                  >
                    {{ show.is_trending ? 'Trending' : 'No' }}
                  </button>
                </td>

                <!-- Seasons & Episodes -->
                <td class="py-3 px-4 text-center">
                  <Link 
                    :href="`/admin/tv-shows/${show.id}`"
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-white/10 hover:bg-netflix text-white font-bold text-xs transition"
                  >
                    <i class="fas fa-layer-group text-[10px]"></i>
                    <span>{{ show.number_of_seasons || 1 }} S · {{ show.number_of_episodes || 0 }} Ep</span>
                  </Link>
                </td>

                <!-- Actions -->
                <td class="py-3 px-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <Link 
                      :href="`/admin/tv-shows/${show.id}`" 
                      class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-white flex items-center gap-1 text-xs font-semibold transition"
                      title="Manage Episodes"
                    >
                      <i class="fas fa-list-ol text-purple-400"></i>
                      <span>Episodes</span>
                    </Link>

                    <button 
                      @click="openEditModal(show)"
                      class="w-7 h-7 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 hover:text-blue-300 flex items-center justify-center transition"
                      title="Edit Show"
                    >
                      <i class="fas fa-edit text-xs"></i>
                    </button>

                    <button 
                      @click="deleteShow(show)"
                      class="w-7 h-7 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 flex items-center justify-center transition"
                      title="Delete Show"
                    >
                      <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="shows.data.length === 0">
                <td colspan="6" class="py-12 text-center text-gray-400">
                  No TV shows found matching the filters.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="shows.links && shows.links.length > 3" class="p-4 border-t border-white/10 flex justify-center gap-1">
          <Link 
            v-for="(link, i) in shows.links" 
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

      <!-- TMDB Quick Import Modal -->
      <div v-if="showTmdbModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-md" @click="showTmdbModal = false"></div>

        <div class="relative w-full max-w-md rounded-3xl bg-gray-900 border border-white/15 p-6 sm:p-8 shadow-2xl z-10 space-y-5">
          <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <h3 class="text-base font-extrabold text-white flex items-center gap-2">
              <i class="fas fa-cloud-download-alt text-purple-400"></i>
              <span>Import Series from TMDB</span>
            </h3>
            <button @click="showTmdbModal = false" class="text-gray-400 hover:text-white text-sm p-1">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <p class="text-xs text-gray-400">
            Enter a TMDB TV Show ID (e.g. <span class="text-amber-400 font-mono">1399</span> for Game of Thrones) or paste the full TMDB URL. We will automatically import series metadata, season 1, premiere episode, and working stream servers.
          </p>

          <form @submit.prevent="submitTmdbImport" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1">TMDB ID or URL *</label>
              <input 
                type="text" 
                v-model="tmdbInput" 
                required 
                placeholder="e.g. 1399 or https://www.themoviedb.org/tv/..."
                class="w-full h-11 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition font-mono"
              />
            </div>

            <div class="flex justify-end gap-3 pt-2">
              <button 
                type="button" 
                @click="showTmdbModal = false"
                class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-xs text-gray-300 transition"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                :disabled="tmdbImporting"
                class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-xs font-bold text-white transition flex items-center gap-2"
              >
                <i v-if="tmdbImporting" class="fas fa-spinner fa-spin"></i>
                <span>{{ tmdbImporting ? 'Importing...' : 'Fetch & Save' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Create / Edit Show Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-md" @click="showModal = false"></div>

        <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl bg-gray-900 border border-white/15 p-6 sm:p-8 shadow-2xl z-10 space-y-6">
          <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <h3 class="text-lg font-extrabold text-white flex items-center gap-2">
              <i class="fas fa-tv text-netflix"></i>
              <span>{{ isEditing ? 'Edit TV Show: ' + form.title : 'Add New TV Show / Anime' }}</span>
            </h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-white text-sm p-1">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Title *</label>
                <input 
                  type="text" 
                  v-model="form.title" 
                  required 
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Slug (URL)</label>
                <input 
                  type="text" 
                  v-model="form.slug" 
                  placeholder="Leave empty to auto-generate"
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <div class="flex items-center justify-between mb-1">
                  <label class="block text-xs font-bold text-gray-300">TMDB ID</label>
                  <button 
                    type="button" 
                    @click="fetchTmdbToForm" 
                    :disabled="fetchingTmdb || !form.tmdb_id"
                    class="text-[11px] font-bold text-purple-400 hover:text-purple-300 disabled:opacity-40 flex items-center gap-1 transition"
                    title="Auto-fetch series metadata from TMDB"
                  >
                    <i :class="fetchingTmdb ? 'fas fa-spinner fa-spin' : 'fas fa-magic'"></i>
                    <span>{{ fetchingTmdb ? 'Fetching...' : 'Auto-Fill' }}</span>
                  </button>
                </div>
                <input 
                  type="text" 
                  v-model="form.tmdb_id" 
                  placeholder="e.g. 1399"
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">First Air Date</label>
                <input 
                  type="text" 
                  v-model="form.first_air_date" 
                  placeholder="YYYY-MM-DD"
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Rating (0-10)</label>
                <input 
                  type="number" 
                  step="0.1" 
                  min="0" 
                  max="10"
                  v-model="form.vote_average" 
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Poster Image URL</label>
                <input 
                  type="text" 
                  v-model="form.poster_path" 
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Backdrop Image URL</label>
                <input 
                  type="text" 
                  v-model="form.backdrop_path" 
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1">Overview / Synopsis</label>
              <textarea 
                v-model="form.overview" 
                rows="3"
                class="w-full bg-black/60 border border-white/10 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
              ></textarea>
            </div>

            <!-- Toggles: Anime, Trending, Featured -->
            <div class="flex items-center gap-6 p-3 rounded-xl bg-black/40 border border-white/5">
              <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-300">
                <input type="checkbox" v-model="form.is_anime" class="rounded bg-black border-white/20 text-purple-500 focus:ring-0" />
                <span>Is Anime Series</span>
              </label>

              <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-300">
                <input type="checkbox" v-model="form.is_trending" class="rounded bg-black border-white/20 text-netflix focus:ring-0" />
                <span>Trending</span>
              </label>

              <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-300">
                <input type="checkbox" v-model="form.is_featured" class="rounded bg-black border-white/20 text-amber-500 focus:ring-0" />
                <span>Featured</span>
              </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
              <button 
                type="button" 
                @click="showModal = false"
                class="px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 text-xs font-bold transition"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                :disabled="submitting"
                class="px-5 py-2.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg flex items-center gap-2 disabled:opacity-50"
              >
                <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
                <span>{{ isEditing ? 'Update Show' : 'Create Show' }}</span>
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
import axios from 'axios';
import AdminLayout from '../Layout.vue';

const props = defineProps({
  shows: {
    type: Object,
    default: () => ({ data: [], links: [], total: 0 }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const search = ref(props.filters.search || '');
const animeFilter = ref(props.filters.is_anime ?? '');
const trendingFilter = ref(props.filters.trending ?? '');

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const submitting = ref(false);

const showTmdbModal = ref(false);
const tmdbInput = ref('');
const tmdbImporting = ref(false);
const fetchingTmdb = ref(false);

const submitTmdbImport = () => {
  if (!tmdbInput.value.trim()) return;
  tmdbImporting.value = true;
  router.post('/admin/tv-shows/import-tmdb', {
    tmdb_id: tmdbInput.value.trim(),
  }, {
    preserveScroll: true,
    onFinish: () => {
      tmdbImporting.value = false;
      showTmdbModal.value = false;
      tmdbInput.value = '';
    },
  });
};

const fetchTmdbToForm = async () => {
  let id = form.value.tmdb_id?.trim();
  if (!id) return;
  if (id.includes('tv/')) {
    const match = id.match(/tv\/(\d+)/);
    if (match) id = match[1];
  }
  form.value.tmdb_id = id;
  fetchingTmdb.value = true;
  try {
    const res = await axios.post('/admin/tmdb/fetch', {
      type: 'tv',
      tmdb_id: id,
    });
    if (res.data.status === 'success' && res.data.data) {
      const d = res.data.data;
      if (d.title) form.value.title = d.title;
      if (d.slug) form.value.slug = d.slug;
      if (d.tagline) form.value.tagline = d.tagline;
      if (d.overview) form.value.overview = d.overview;
      if (d.poster_path) form.value.poster_path = d.poster_path;
      if (d.backdrop_path) form.value.backdrop_path = d.backdrop_path;
      if (d.first_air_date) form.value.first_air_date = d.first_air_date;
      if (d.vote_average) form.value.vote_average = d.vote_average;
      if (d.is_anime !== undefined) form.value.is_anime = d.is_anime;
    } else {
      alert(res.data.message || 'Could not fetch data for this TMDB ID.');
    }
  } catch (err) {
    console.error(err);
    alert('Failed to fetch from TMDB. Please verify the TMDB ID.');
  } finally {
    fetchingTmdb.value = false;
  }
};

const form = ref({
  title: '',
  slug: '',
  tmdb_id: '',
  tagline: '',
  overview: '',
  poster_path: '',
  backdrop_path: '',
  first_air_date: '',
  vote_average: 8.0,
  is_anime: false,
  is_trending: false,
  is_featured: false,
});

const applyFilters = () => {
  router.get('/admin/tv-shows', {
    search: search.value || undefined,
    is_anime: animeFilter.value !== '' ? animeFilter.value : undefined,
    trending: trendingFilter.value !== '' ? trendingFilter.value : undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  search.value = '';
  animeFilter.value = '';
  trendingFilter.value = '';
  applyFilters();
};

const openCreateModal = () => {
  isEditing.value = false;
  editingId.value = null;
  form.value = {
    title: '',
    slug: '',
    tmdb_id: '',
    tagline: '',
    overview: '',
    poster_path: '',
    backdrop_path: '',
    first_air_date: '',
    vote_average: 8.0,
    is_anime: false,
    is_trending: false,
    is_featured: false,
  };
  showModal.value = true;
};

const openEditModal = (show) => {
  isEditing.value = true;
  editingId.value = show.id;
  form.value = {
    title: show.title || '',
    slug: show.slug || '',
    tmdb_id: show.tmdb_id || '',
    tagline: show.tagline || '',
    overview: show.overview || '',
    poster_path: show.poster_path || '',
    backdrop_path: show.backdrop_path || '',
    first_air_date: show.first_air_date || '',
    vote_average: show.vote_average || 8.0,
    is_anime: Boolean(show.is_anime),
    is_trending: Boolean(show.is_trending),
    is_featured: Boolean(show.is_featured),
  };
  showModal.value = true;
};

const submitForm = () => {
  submitting.value = true;
  if (isEditing.value) {
    router.put(`/admin/tv-shows/${editingId.value}`, form.value, {
      preserveScroll: true,
      onFinish: () => {
        submitting.value = false;
        showModal.value = false;
      },
    });
  } else {
    router.post('/admin/tv-shows', form.value, {
      preserveScroll: true,
      onFinish: () => {
        submitting.value = false;
        showModal.value = false;
      },
    });
  }
};

const toggleAnime = (show) => {
  router.post(`/admin/tv-shows/${show.id}/toggle-anime`, {}, {
    preserveScroll: true,
  });
};

const toggleTrending = (show) => {
  router.post(`/admin/tv-shows/${show.id}/toggle-trending`, {}, {
    preserveScroll: true,
  });
};

const deleteShow = (show) => {
  if (confirm(`Are you sure you want to delete "${show.title}" and all its seasons & episodes?`)) {
    router.delete(`/admin/tv-shows/${show.id}`, {
      preserveScroll: true,
    });
  }
};
</script>
