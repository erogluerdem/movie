<template>
  <AdminLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-white/10">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <span>Özel Koleksiyonlar & Vitrinler</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
              Curated Shelves
            </span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">Ana sayfada ve özel sayfalarda sergilenecek tematik film & dizi koleksiyonları oluşturun ve sıralayın.</p>
        </div>

        <button 
          @click="openCreateModal"
          class="px-4 py-2 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg shadow-red-600/30 flex items-center gap-2"
        >
          <i class="fas fa-plus"></i>
          <span>Yeni Koleksiyon</span>
        </button>
      </div>

      <!-- Collections Shelves -->
      <div v-if="collections.length === 0" class="p-16 text-center rounded-2xl bg-white/5 border border-white/10 text-gray-400 text-sm">
        <i class="fas fa-folder-plus text-4xl mb-3 text-gray-600"></i>
        <div class="font-bold text-white text-base">Henüz hiç koleksiyon oluşturulmadı</div>
        <p class="text-xs text-gray-400 mt-1">Örn: "Oscar Ödüllü Başyapıtlar", "Marvel Evreni", "Sürükleyici Gerilim Dizileri"</p>
        <button 
          @click="openCreateModal"
          class="mt-4 px-4 py-2 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition"
        >
          İlk Koleksiyonu Oluştur
        </button>
      </div>

      <div v-else class="space-y-6">
        <div 
          v-for="col in collections" 
          :key="col.id"
          class="p-5 sm:p-6 rounded-2xl bg-white/5 border border-white/10 space-y-4 hover:border-white/20 transition"
        >
          <!-- Collection Header -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-white/10">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-base flex-shrink-0">
                <i class="fas fa-layer-group"></i>
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h2 class="text-base font-black text-white">{{ col.title }}</h2>
                  <span v-if="col.is_featured" class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-500 text-black shadow-sm">
                    Vitrin
                  </span>
                  <span class="text-[10px] font-mono text-gray-500">/{{ col.slug }}</span>
                </div>
                <p v-if="col.description" class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ col.description }}</p>
              </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-2 flex-wrap">
              <button 
                @click="openAddItemModal(col)"
                class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition flex items-center gap-1.5"
              >
                <i class="fas fa-plus text-[10px]"></i>
                <span>İçerik Ekle</span>
              </button>

              <button 
                @click="openEditModal(col)"
                class="px-3 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white text-xs font-semibold transition border border-white/10 flex items-center gap-1.5"
              >
                <i class="fas fa-pen text-[10px]"></i>
                <span>Düzenle</span>
              </button>

              <button 
                @click="deleteCollection(col)"
                class="px-2.5 py-1.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs transition"
                title="Koleksiyonu sil"
              >
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </div>

          <!-- Collection Items Grid / Scroll -->
          <div>
            <div class="flex items-center justify-between text-xs text-gray-400 mb-3">
              <span>İçerikler ({{ col.items.length }})</span>
              <span v-if="col.items.length === 0" class="text-amber-400/80 text-[11px]">Henüz içerik eklenmedi. Yukarıdaki "+ İçerik Ekle" butonunu kullanın.</span>
            </div>

            <div v-if="col.items.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
              <div 
                v-for="item in col.items" 
                :key="item.id"
                class="p-2 rounded-xl bg-black/40 border border-white/5 relative group flex flex-col justify-between"
              >
                <div class="aspect-[2/3] rounded-lg overflow-hidden bg-gray-900 mb-2 relative">
                  <img 
                    :src="item.collectible?.poster_url || '/images/placeholders/poster.png'" 
                    :alt="item.collectible?.title"
                    class="w-full h-full object-cover"
                    loading="lazy"
                  />
                  <!-- Remove Overlay Button -->
                  <button 
                    @click="removeItem(col, item)"
                    class="absolute top-1 right-1 w-6 h-6 rounded-md bg-black/80 hover:bg-red-600 text-white text-[10px] flex items-center justify-center transition opacity-0 group-hover:opacity-100 shadow"
                    title="Koleksiyondan çıkar"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </div>

                <div class="text-xs font-bold text-white truncate" :title="item.collectible?.title">
                  {{ item.collectible?.title || 'Silinmiş İçerik' }}
                </div>
                <div class="text-[10px] text-gray-500 flex items-center justify-between mt-1">
                  <span>{{ item.collectible_type.includes('Movie') ? 'Film' : 'Dizi' }}</span>
                  <span class="font-mono">#{{ item.order }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Create / Edit Collection Modal -->
      <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-fade-in">
        <div class="w-full max-w-lg bg-gray-950 border border-white/10 rounded-2xl shadow-2xl p-6 space-y-5">
          <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <h3 class="text-base font-bold text-white">
              {{ editingCollection ? 'Koleksiyonu Düzenle' : 'Yeni Koleksiyon Oluştur' }}
            </h3>
            <button @click="modalOpen = false" class="text-gray-400 hover:text-white text-sm">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="saveCollection" class="space-y-4">
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-gray-400">Koleksiyon Başlığı</label>
              <input 
                type="text" 
                v-model="form.title"
                class="w-full px-3.5 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix"
                placeholder="Örn: 2026'nın En Çok Konuşulan Gerilim Filmleri"
                required
              />
            </div>

            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-gray-400">Açıklama (İsteğe Bağlı)</label>
              <textarea 
                v-model="form.description"
                rows="3"
                class="w-full px-3.5 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix"
                placeholder="Bu koleksiyon hakkında kısa bir tanıtım..."
              ></textarea>
            </div>

            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-gray-400">Arkaplan Resmi (Backdrop URL / TMDB Path)</label>
              <input 
                type="text" 
                v-model="form.backdrop_path"
                class="w-full px-3.5 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix font-mono"
                placeholder="Örn: /backdrop.jpg veya https://..."
              />
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/5">
              <div>
                <div class="text-xs font-bold text-white">Vitrin Olarak Öne Çıkar</div>
                <div class="text-[10px] text-gray-400">Ana sayfada özel banner olarak sergilenir</div>
              </div>
              <input type="checkbox" v-model="form.is_featured" class="w-4 h-4 rounded text-netflix focus:ring-netflix bg-black/60 border-white/30 cursor-pointer" />
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-white/10">
              <button 
                type="button" 
                @click="modalOpen = false" 
                class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white text-xs transition font-semibold"
              >
                Vazgeç
              </button>
              <button 
                type="submit" 
                :disabled="saving"
                class="px-5 py-2 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg shadow-red-600/30 flex items-center gap-2"
              >
                <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
                <span>{{ editingCollection ? 'Güncelle' : 'Kaydet' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Add Item Modal -->
      <div v-if="itemModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-fade-in">
        <div class="w-full max-w-lg bg-gray-950 border border-white/10 rounded-2xl shadow-2xl p-6 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <div>
              <h3 class="text-base font-bold text-white">Koleksiyona İçerik Ekle</h3>
              <p class="text-xs text-amber-400">{{ activeCollection?.title }}</p>
            </div>
            <button @click="itemModalOpen = false" class="text-gray-400 hover:text-white text-sm">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <!-- Type Selection & Filter Search -->
          <div class="space-y-3">
            <div class="flex items-center gap-2 p-1 rounded-xl bg-black/40 border border-white/10">
              <button 
                @click="selectedItemType = 'movie'"
                :class="selectedItemType === 'movie' ? 'bg-netflix text-white font-bold' : 'text-gray-400 hover:text-white'"
                class="flex-1 py-1.5 rounded-lg text-xs transition flex items-center justify-center gap-1.5"
              >
                <i class="fas fa-film"></i>
                <span>Filmler ({{ recentMovies.length }})</span>
              </button>
              <button 
                @click="selectedItemType = 'tv'"
                :class="selectedItemType === 'tv' ? 'bg-netflix text-white font-bold' : 'text-gray-400 hover:text-white'"
                class="flex-1 py-1.5 rounded-lg text-xs transition flex items-center justify-center gap-1.5"
              >
                <i class="fas fa-tv"></i>
                <span>Diziler ({{ recentTvShows.length }})</span>
              </button>
            </div>

            <input 
              type="text" 
              v-model="itemSearch"
              placeholder="Listede ara..." 
              class="w-full px-3.5 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-netflix placeholder-gray-500"
            />
          </div>

          <!-- List of Candidate Media -->
          <div class="max-h-80 overflow-y-auto space-y-1.5 pr-1">
            <div 
              v-for="media in filteredCandidateList" 
              :key="media.id"
              class="flex items-center justify-between p-2 rounded-xl bg-white/[0.02] hover:bg-white/5 border border-white/5 transition"
            >
              <div class="flex items-center gap-2.5 min-w-0">
                <img 
                  :src="media.poster_url || '/images/placeholders/poster.png'" 
                  :alt="media.title"
                  class="w-8 h-11 rounded object-cover bg-gray-900 flex-shrink-0"
                />
                <div class="min-w-0">
                  <div class="text-xs font-bold text-white truncate">{{ media.title }}</div>
                  <div class="text-[10px] text-gray-500 font-mono">ID #{{ media.id }}</div>
                </div>
              </div>

              <button 
                @click="addItemToCollection(media.id)"
                class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition flex items-center gap-1 flex-shrink-0"
              >
                <i class="fas fa-plus text-[10px]"></i>
                <span>Ekle</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '../Layout.vue';

const props = defineProps({
  collections: {
    type: Array,
    default: () => [],
  },
  recentMovies: {
    type: Array,
    default: () => [],
  },
  recentTvShows: {
    type: Array,
    default: () => [],
  },
});

const modalOpen = ref(false);
const editingCollection = ref(null);
const saving = ref(false);

const form = reactive({
  title: '',
  description: '',
  backdrop_path: '',
  is_featured: false,
});

const openCreateModal = () => {
  editingCollection.value = null;
  form.title = '';
  form.description = '';
  form.backdrop_path = '';
  form.is_featured = false;
  modalOpen.value = true;
};

const openEditModal = (col) => {
  editingCollection.value = col;
  form.title = col.title;
  form.description = col.description || '';
  form.backdrop_path = col.backdrop_path || '';
  form.is_featured = col.is_featured;
  modalOpen.value = true;
};

const saveCollection = () => {
  saving.value = true;
  if (editingCollection.value) {
    router.put(`/admin/collections/${editingCollection.value.id}`, form, {
      preserveScroll: true,
      onFinish: () => {
        saving.value = false;
        modalOpen.value = false;
      },
    });
  } else {
    router.post('/admin/collections', form, {
      preserveScroll: true,
      onFinish: () => {
        saving.value = false;
        modalOpen.value = false;
      },
    });
  }
};

const deleteCollection = (col) => {
  if (confirm(`"${col.title}" koleksiyonunu silmek istediğinize emin misiniz?`)) {
    router.delete(`/admin/collections/${col.id}`, { preserveScroll: true });
  }
};

// Item Modal logic
const itemModalOpen = ref(false);
const activeCollection = ref(null);
const selectedItemType = ref('movie');
const itemSearch = ref('');

const openAddItemModal = (col) => {
  activeCollection.value = col;
  itemSearch.value = '';
  itemModalOpen.value = true;
};

const filteredCandidateList = computed(() => {
  const list = selectedItemType.value === 'movie' ? props.recentMovies : props.recentTvShows;
  if (!itemSearch.value) return list;
  const q = itemSearch.value.toLowerCase();
  return list.filter(i => (i.title || '').toLowerCase().includes(q));
});

const addItemToCollection = (id) => {
  if (!activeCollection.value) return;
  router.post(`/admin/collections/${activeCollection.value.id}/items`, {
    type: selectedItemType.value,
    id: id,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      itemModalOpen.value = false;
    },
  });
};

const removeItem = (col, item) => {
  if (confirm('Bu içeriği koleksiyondan çıkarmak istediğinize emin misiniz?')) {
    router.delete(`/admin/collections/${col.id}/items/${item.id}`, { preserveScroll: true });
  }
};
</script>
