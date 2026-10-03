<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-white/10">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <i class="fas fa-film text-netflix"></i>
            <span>Movies Management</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/10 text-gray-300">
              {{ movies.total || 0 }}
            </span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">Add, update, curate trending films, and configure streaming sources.</p>
        </div>

        <div class="flex items-center gap-2.5 flex-shrink-0">
          <button 
            @click="showTmdbModal = true"
            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-lg flex items-center gap-2"
          >
            <i class="fas fa-cloud-download-alt"></i>
            <span>Import from TMDB</span>
          </button>

          <button 
            @click="openCreateModal"
            class="px-4 py-2.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg flex items-center gap-2"
          >
            <i class="fas fa-plus"></i>
            <span>Add New Movie</span>
          </button>
        </div>
      </div>


      <!-- Filters & Search Bar -->
      <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative w-full md:w-80">
          <input 
            type="text" 
            v-model="search" 
            @keydown.enter="applyFilters"
            placeholder="Search movie title, slug, or TMDB ID..."
            class="w-full h-10 bg-black/60 border border-white/10 rounded-xl pl-9 pr-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
          />
          <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500"></i>
        </div>

        <!-- Filters & Reset -->
        <div class="flex items-center gap-3 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
          <select 
            v-model="trendingFilter" 
            @change="applyFilters"
            class="h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-gray-300 focus:outline-none focus:border-netflix cursor-pointer"
          >
            <option value="">All Trending Status</option>
            <option value="1">Trending Only</option>
            <option value="0">Not Trending</option>
          </select>

          <select 
            v-model="featuredFilter" 
            @change="applyFilters"
            class="h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-gray-300 focus:outline-none focus:border-netflix cursor-pointer"
          >
            <option value="">All Featured Status</option>
            <option value="1">Featured Only</option>
            <option value="0">Standard</option>
          </select>

          <button 
            @click="resetFilters" 
            class="px-3 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white text-xs font-semibold transition whitespace-nowrap"
          >
            Reset
          </button>
        </div>
      </div>

      <!-- Movies Table -->
      <div class="rounded-2xl border border-white/10 bg-gray-900/60 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-gray-300 divide-y divide-white/10">
            <thead class="bg-white/5 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4">Movie</th>
                <th class="py-3 px-4">Rating & Year</th>
                <th class="py-3 px-4 text-center">Trending</th>
                <th class="py-3 px-4 text-center">Featured</th>
                <th class="py-3 px-4 text-center">Servers</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr 
                v-for="movie in movies.data" 
                :key="movie.id"
                class="hover:bg-white/5 transition group"
              >
                <!-- Title & Poster -->
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <img 
                      :src="movie.poster_url || movie.poster_path || 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=100&auto=format&fit=crop'" 
                      :alt="movie.title"
                      class="w-10 h-14 object-cover rounded-lg flex-shrink-0 shadow"
                    />
                    <div class="min-w-0">
                      <div class="font-bold text-white text-sm truncate max-w-xs group-hover:text-netflix transition-colors">
                        {{ movie.title }}
                      </div>
                      <div class="text-[11px] text-gray-400 truncate max-w-xs font-mono mt-0.5">
                        /movie/{{ movie.slug }}
                      </div>
                      <span v-if="movie.tmdb_id" class="text-[10px] text-gray-500 font-mono">
                        TMDB: {{ movie.tmdb_id }}
                      </span>
                    </div>
                  </div>
                </td>

                <!-- Rating & Year -->
                <td class="py-3 px-4 whitespace-nowrap">
                  <div class="flex items-center gap-1.5 font-bold text-amber-400">
                    <i class="fas fa-star text-[10px]"></i>
                    <span>{{ movie.vote_average || '0.0' }}</span>
                  </div>
                  <div class="text-gray-400 text-[11px] mt-0.5">
                    {{ movie.release_date ? movie.release_date.substring(0, 4) : '2025' }} · {{ movie.runtime || '120m' }}
                  </div>
                </td>

                <!-- Trending Switch -->
                <td class="py-3 px-4 text-center">
                  <button 
                    @click="toggleTrending(movie)"
                    :class="movie.is_trending ? 'bg-red-500/20 text-red-400 border-red-500/40' : 'bg-white/5 text-gray-500 border-white/10'"
                    class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase border transition"
                  >
                    {{ movie.is_trending ? 'Trending' : 'No' }}
                  </button>
                </td>

                <!-- Featured Switch -->
                <td class="py-3 px-4 text-center">
                  <button 
                    @click="toggleFeatured(movie)"
                    :class="movie.is_featured ? 'bg-amber-500/20 text-amber-400 border-amber-500/40' : 'bg-white/5 text-gray-500 border-white/10'"
                    class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase border transition"
                  >
                    {{ movie.is_featured ? 'Featured' : 'Standard' }}
                  </button>
                </td>

                <!-- Servers Count -->
                <td class="py-3 px-4 text-center">
                  <span class="px-2 py-0.5 rounded-md text-[11px] font-mono bg-white/5 border border-white/10 text-gray-300">
                    {{ movie.stream_servers?.length || 0 }} stream{{ (movie.stream_servers?.length || 0) === 1 ? '' : 's' }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="py-3 px-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <a 
                      :href="`/movie/${movie.slug}`" 
                      target="_blank"
                      class="w-7 h-7 rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white flex items-center justify-center transition"
                      title="View on Main Site"
                    >
                      <i class="fas fa-external-link-alt text-xs"></i>
                    </a>

                    <button 
                      @click="openEditModal(movie)"
                      class="w-7 h-7 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 hover:text-blue-300 flex items-center justify-center transition"
                      title="Edit Movie"
                    >
                      <i class="fas fa-edit text-xs"></i>
                    </button>

                    <button 
                      @click="deleteMovie(movie)"
                      class="w-7 h-7 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 flex items-center justify-center transition"
                      title="Delete Movie"
                    >
                      <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="movies.data.length === 0">
                <td colspan="6" class="py-12 text-center text-gray-400">
                  No movies found matching the filters.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="movies.links && movies.links.length > 3" class="p-4 border-t border-white/10 flex justify-center gap-1">
          <Link 
            v-for="(link, i) in movies.links" 
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
              <i class="fas fa-cloud-download-alt text-blue-500"></i>
              <span>Import Movie from TMDB</span>
            </h3>
            <button @click="showTmdbModal = false" class="text-gray-400 hover:text-white text-sm p-1">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <p class="text-xs text-gray-400">
            Enter a TMDB Movie ID (e.g. <span class="text-amber-400 font-mono">157336</span> for Interstellar) or paste the full TMDB URL. We will automatically import the trailer, poster, cast, overview, and details.
          </p>

          <form @submit.prevent="submitTmdbImport" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1">TMDB ID or URL *</label>
              <input 
                type="text" 
                v-model="tmdbInput" 
                required 
                placeholder="e.g. 157336 or https://www.themoviedb.org/movie/..."
                class="w-full h-11 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition font-mono"
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
                class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-xs font-bold text-white transition flex items-center gap-2"
              >
                <i v-if="tmdbImporting" class="fas fa-spinner fa-spin"></i>
                <span>{{ tmdbImporting ? 'Importing...' : 'Fetch & Save' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Create / Edit Movie Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-md" @click="showModal = false"></div>


        <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-3xl bg-gray-900 border border-white/15 p-6 sm:p-8 shadow-2xl z-10 space-y-6">
          <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <h3 class="text-lg font-extrabold text-white flex items-center gap-2">
              <i class="fas fa-film text-netflix"></i>
              <span>{{ isEditing ? 'Edit Movie: ' + form.title : 'Add New Movie' }}</span>
            </h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-white text-sm p-1">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="submitForm" class="space-y-4">
            <!-- Title & Slug -->
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

            <!-- TMDB ID & Release Date & Runtime -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <div class="flex items-center justify-between mb-1">
                  <label class="block text-xs font-bold text-gray-300">TMDB ID</label>
                  <button 
                    type="button" 
                    @click="fetchTmdbToForm" 
                    :disabled="fetchingTmdb || !form.tmdb_id"
                    class="text-[11px] font-bold text-blue-400 hover:text-blue-300 disabled:opacity-40 flex items-center gap-1 transition"
                    title="Auto-fetch movie metadata and stream servers"
                  >
                    <i :class="fetchingTmdb ? 'fas fa-spinner fa-spin' : 'fas fa-magic'"></i>
                    <span>{{ fetchingTmdb ? 'Fetching...' : 'Auto-Fill' }}</span>
                  </button>
                </div>
                <input 
                  type="text" 
                  v-model="form.tmdb_id" 
                  placeholder="e.g. 157336"
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Release Date</label>
                <input 
                  type="text" 
                  v-model="form.release_date" 
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

            <!-- Poster & Backdrop URL -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Poster Image URL / Path</label>
                <input 
                  type="text" 
                  v-model="form.poster_path" 
                  placeholder="/ceTxx... or https://..."
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-gray-300 mb-1">Backdrop Image URL / Path</label>
                <input 
                  type="text" 
                  v-model="form.backdrop_path" 
                  placeholder="/ceTxx... or https://..."
                  class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
                />
              </div>
            </div>

            <!-- Overview -->
            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1">Overview / Synopsis</label>
              <textarea 
                v-model="form.overview" 
                rows="3"
                class="w-full bg-black/60 border border-white/10 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
              ></textarea>
            </div>

            <!-- Toggles: Trending & Featured -->
            <div class="flex items-center gap-6 p-3 rounded-xl bg-black/40 border border-white/5">
              <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-300">
                <input type="checkbox" v-model="form.is_trending" class="rounded bg-black border-white/20 text-netflix focus:ring-0" />
                <span>Mark as Trending</span>
              </label>

              <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-gray-300">
                <input type="checkbox" v-model="form.is_featured" class="rounded bg-black border-white/20 text-amber-500 focus:ring-0" />
                <span>Mark as Featured Hero Banner</span>
              </label>
            </div>

            <!-- Stream Servers Dynamic Section -->
            <div class="space-y-3 pt-2 border-t border-white/10">
              <div class="flex items-center justify-between">
                <label class="text-xs font-extrabold uppercase tracking-wider text-gray-300 flex items-center gap-1.5">
                  <i class="fas fa-server text-netflix"></i>
                  <span>Streaming Embed Servers</span>
                </label>
                <button 
                  type="button" 
                  @click="addServer" 
                  class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/15 text-white text-[11px] font-bold transition flex items-center gap-1"
                >
                  <i class="fas fa-plus"></i>
                  <span>Add Server</span>
                </button>
              </div>

              <div v-if="form.stream_servers.length > 0" class="space-y-2">
                <div 
                  v-for="(srv, sIndex) in form.stream_servers" 
                  :key="sIndex"
                  class="p-3 rounded-xl bg-black/50 border border-white/10 flex flex-col sm:flex-row items-center gap-2"
                >
                  <input 
                    type="text" 
                    v-model="srv.server_name" 
                    placeholder="Server Name (e.g. VidCloud HD)"
                    class="h-8 bg-black border border-white/10 rounded-lg px-2.5 text-xs text-white w-full sm:w-1/3"
                  />
                  <input 
                    type="url" 
                    v-model="srv.embed_url" 
                    placeholder="https://embed.domain/movie/..."
                    class="h-8 bg-black border border-white/10 rounded-lg px-2.5 text-xs text-white w-full sm:flex-1"
                  />
                  <input 
                    type="text" 
                    v-model="srv.quality" 
                    placeholder="Quality (4K/1080p)"
                    class="h-8 bg-black border border-white/10 rounded-lg px-2.5 text-xs text-white w-full sm:w-24"
                  />
                  <button 
                    type="button" 
                    @click="removeServer(sIndex)"
                    class="w-8 h-8 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 flex items-center justify-center flex-shrink-0"
                    title="Remove Server"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>

              <div v-else class="text-center py-4 bg-black/20 rounded-xl text-gray-500 text-xs">
                No custom servers added. Default player fallback will be used.
              </div>
            </div>

            <!-- Submit Buttons -->
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
                <span>{{ isEditing ? 'Update Movie' : 'Create Movie' }}</span>
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
  movies: {
    type: Object,
    default: () => ({ data: [], links: [], total: 0 }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const search = ref(props.filters.search || '');
const trendingFilter = ref(props.filters.trending ?? '');
const featuredFilter = ref(props.filters.featured ?? '');

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const submitting = ref(false);

const showTmdbModal = ref(false);
const tmdbInput = ref('');
const tmdbImporting = ref(false);
const fetchingTmdb = ref(false);

const fetchTmdbToForm = async () => {
  let id = form.value.tmdb_id?.trim();
  if (!id) return;
  if (id.includes('movie/')) {
    const match = id.match(/movie\/(\d+)/);
    if (match) id = match[1];
  }
  form.value.tmdb_id = id;
  fetchingTmdb.value = true;
  try {
    const res = await axios.post('/admin/tmdb/fetch', {
      type: 'movie',
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
      if (d.release_date) form.value.release_date = d.release_date;
      if (d.vote_average) form.value.vote_average = d.vote_average;
      if (d.runtime) form.value.runtime = d.runtime;
      if (d.director) form.value.director = d.director;
      if (d.trailer_url) form.value.trailer_url = d.trailer_url;
      if (d.stream_servers && d.stream_servers.length) {
        form.value.stream_servers = d.stream_servers;
      }
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

const submitTmdbImport = () => {
  if (!tmdbInput.value.trim()) return;
  tmdbImporting.value = true;
  router.post('/admin/movies/import-tmdb', {
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


const form = ref({
  title: '',
  slug: '',
  tmdb_id: '',
  tagline: '',
  overview: '',
  poster_path: '',
  backdrop_path: '',
  release_date: '',
  vote_average: 7.5,
  runtime: '120 min',
  director: '',
  trailer_url: '',
  is_trending: false,
  is_featured: false,
  stream_servers: [],
});

const applyFilters = () => {
  router.get('/admin/movies', {
    search: search.value || undefined,
    trending: trendingFilter.value !== '' ? trendingFilter.value : undefined,
    featured: featuredFilter.value !== '' ? featuredFilter.value : undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  search.value = '';
  trendingFilter.value = '';
  featuredFilter.value = '';
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
    release_date: '',
    vote_average: 7.5,
    runtime: '120 min',
    director: '',
    trailer_url: '',
    is_trending: false,
    is_featured: false,
    stream_servers: [
      { server_name: 'VidCloud HD', embed_url: '', quality: '1080p' }
    ],
  };
  showModal.value = true;
};

const openEditModal = (movie) => {
  isEditing.value = true;
  editingId.value = movie.id;
  form.value = {
    title: movie.title || '',
    slug: movie.slug || '',
    tmdb_id: movie.tmdb_id || '',
    tagline: movie.tagline || '',
    overview: movie.overview || '',
    poster_path: movie.poster_path || '',
    backdrop_path: movie.backdrop_path || '',
    release_date: movie.release_date || '',
    vote_average: movie.vote_average || 7.5,
    runtime: movie.runtime || '',
    director: movie.director || '',
    trailer_url: movie.trailer_url || '',
    is_trending: Boolean(movie.is_trending),
    is_featured: Boolean(movie.is_featured),
    stream_servers: Array.isArray(movie.stream_servers) ? [...movie.stream_servers] : [],
  };
  showModal.value = true;
};

const addServer = () => {
  form.value.stream_servers.push({
    server_name: `Server ${form.value.stream_servers.length + 1}`,
    embed_url: '',
    quality: '1080p',
  });
};

const removeServer = (index) => {
  form.value.stream_servers.splice(index, 1);
};

const submitForm = () => {
  submitting.value = true;
  if (isEditing.value) {
    router.put(`/admin/movies/${editingId.value}`, form.value, {
      preserveScroll: true,
      onFinish: () => {
        submitting.value = false;
        showModal.value = false;
      },
    });
  } else {
    router.post('/admin/movies', form.value, {
      preserveScroll: true,
      onFinish: () => {
        submitting.value = false;
        showModal.value = false;
      },
    });
  }
};

const toggleTrending = (movie) => {
  router.post(`/admin/movies/${movie.id}/toggle-trending`, {}, {
    preserveScroll: true,
  });
};

const toggleFeatured = (movie) => {
  router.post(`/admin/movies/${movie.id}/toggle-featured`, {}, {
    preserveScroll: true,
  });
};

const deleteMovie = (movie) => {
  if (confirm(`Are you sure you want to delete "${movie.title}"? This cannot be undone.`)) {
    router.delete(`/admin/movies/${movie.id}`, {
      preserveScroll: true,
    });
  }
};
</script>
