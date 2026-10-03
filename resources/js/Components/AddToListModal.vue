<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="close"></div>

    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
      <div 
        class="relative transform overflow-hidden rounded-2xl bg-gray-950 border border-white/10 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 border-b border-white/10">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-netflix/20 text-netflix flex items-center justify-center text-sm">
              <i class="fas fa-bookmark"></i>
            </div>
            <div>
              <h3 class="text-base font-bold text-white leading-none">{{ $t('lists.add_to_list') }}</h3>
              <p class="text-xs text-gray-400 mt-1 truncate max-w-[240px]">{{ title }}</p>
            </div>
          </div>
          <button 
            type="button" 
            @click="close" 
            class="text-gray-400 hover:text-white p-1 rounded-lg hover:bg-white/5 transition"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>

        <!-- Body -->
        <div class="mt-4">
          <!-- Unauthenticated State -->
          <div v-if="!isAuthenticated && !loading" class="text-center py-6">
            <div class="w-12 h-12 rounded-full bg-white/5 flex items-center justify-center text-xl text-gray-400 mx-auto mb-3">
              <i class="fas fa-lock"></i>
            </div>
            <h4 class="text-sm font-semibold text-white">{{ $t('lists.login_required') }}</h4>
            <p class="text-xs text-gray-400 mt-1 mb-4">{{ $t('lists.login_required_desc') }}</p>
            <a 
              href="/login" 
              class="inline-block px-5 py-2 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg shadow-netflix/20"
            >
              {{ $t('lists.login_btn') }}
            </a>
          </div>

          <!-- Loading State -->
          <div v-else-if="loading" class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-netflix mb-2"></i>
            <p class="text-xs text-gray-400">{{ $t('lists.lists_loading') }}</p>
          </div>

          <!-- Lists Content -->
          <div v-else>
            <!-- Existing Lists -->
            <div v-if="lists.length > 0" class="space-y-2 max-h-60 overflow-y-auto pr-1">
              <div 
                v-for="list in lists" 
                :key="list.id" 
                class="flex items-center justify-between p-3 rounded-xl bg-white/[0.03] hover:bg-white/[0.06] border border-white/5 transition"
              >
                <div class="min-w-0 flex-1 pr-3">
                  <div class="flex items-center gap-1.5">
                    <span class="text-sm font-semibold text-white truncate">{{ list.title }}</span>
                    <i v-if="!list.is_public" class="fas fa-lock text-[10px] text-gray-500" :title="$t('lists.private_badge')"></i>
                  </div>
                  <span class="text-xs text-gray-400">{{ $t('lists.items_count', { count: list.items_count }) }}</span>
                </div>

                <button 
                  type="button" 
                  @click="toggleItemInList(list)"
                  :disabled="list.processing"
                  :class="[
                    list.contains 
                      ? 'bg-netflix text-white shadow-md shadow-netflix/20 hover:bg-red-700' 
                      : 'bg-white/10 text-gray-300 hover:bg-white/20 hover:text-white',
                    'px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition'
                  ]"
                >
                  <i v-if="list.processing" class="fas fa-spinner fa-spin text-xs"></i>
                  <template v-else>
                    <i :class="list.contains ? 'fas fa-check' : 'fas fa-plus'"></i>
                    <span>{{ list.contains ? $t('lists.added') : $t('lists.add') }}</span>
                  </template>
                </button>
              </div>
            </div>

            <!-- Empty Lists Notice -->
            <div v-else-if="!showCreateForm" class="text-center py-6 bg-white/[0.02] rounded-xl border border-dashed border-white/10">
              <i class="fas fa-folder-open text-3xl text-gray-600 mb-2"></i>
              <p class="text-xs text-gray-400">{{ $t('lists.no_lists') }}</p>
            </div>

            <!-- Create New List Accordion / Form -->
            <div class="mt-4 pt-4 border-t border-white/10">
              <button 
                v-if="!showCreateForm"
                type="button" 
                @click="showCreateForm = true"
                class="w-full py-2.5 rounded-xl border border-dashed border-white/20 hover:border-netflix text-gray-300 hover:text-white text-xs font-semibold flex items-center justify-center gap-2 transition bg-white/[0.02] hover:bg-netflix/5"
              >
                <i class="fas fa-plus-circle text-netflix"></i>
                <span>{{ $t('lists.create_new') }}</span>
              </button>

              <form v-else @submit.prevent="createList" class="space-y-3 bg-white/[0.02] p-3.5 rounded-xl border border-white/10">
                <div class="flex items-center justify-between">
                  <h4 class="text-xs font-bold text-white">{{ $t('lists.new_list') }}</h4>
                  <button type="button" @click="showCreateForm = false" class="text-gray-400 hover:text-white text-xs">
                    <i class="fas fa-times"></i>
                  </button>
                </div>

                <input 
                  type="text" 
                  v-model="newTitle" 
                  :placeholder="$t('lists.title_placeholder')" 
                  class="w-full px-3 py-2 text-xs rounded-lg bg-black/60 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-netflix"
                  required
                  maxlength="100"
                />

                <textarea 
                  v-model="newDescription" 
                  :placeholder="$t('lists.desc_placeholder')" 
                  rows="2"
                  class="w-full px-3 py-2 text-xs rounded-lg bg-black/60 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-netflix resize-none"
                  maxlength="300"
                ></textarea>

                <div class="flex items-center justify-between pt-1">
                  <label class="flex items-center gap-2 text-xs text-gray-400 cursor-pointer">
                    <input type="checkbox" v-model="newIsPublic" class="rounded bg-gray-800 border-gray-700 text-netflix focus:ring-0" />
                    <span>{{ $t('lists.is_public_label') }}</span>
                  </label>

                  <button 
                    type="submit" 
                    :disabled="creating || !newTitle.trim()"
                    class="px-4 py-1.5 rounded-lg bg-netflix hover:bg-red-700 text-white text-xs font-bold transition disabled:opacity-50"
                  >
                    <i v-if="creating" class="fas fa-spinner fa-spin mr-1"></i>
                    <span>{{ creating ? $t('lists.creating') : $t('lists.create_and_add') }}</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  mediaType: { type: String, required: true }, // 'movie' | 'tv'
  mediaId: { type: Number, required: true },
  title: { type: String, default: '' },
});

const emit = defineEmits(['close']);

const isAuthenticated = ref(true);
const loading = ref(false);
const lists = ref([]);

const showCreateForm = ref(false);
const newTitle = ref('');
const newDescription = ref('');
const newIsPublic = ref(true);
const creating = ref(false);

const close = () => {
  emit('close');
};

const fetchListsStatus = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/custom-lists/status', {
      params: {
        media_type: props.mediaType,
        media_id: props.mediaId,
      },
    });

    isAuthenticated.value = res.data.authenticated;
    lists.value = (res.data.lists || []).map(l => ({ ...l, processing: false }));
  } catch (err) {
    console.error('Error fetching custom lists status:', err);
  } finally {
    loading.value = false;
  }
};

const toggleItemInList = async (list) => {
  list.processing = true;
  const wasContained = list.contains;

  try {
    if (wasContained) {
      await axios.post(`/lists/${list.id}/items/remove`, {
        media_type: props.mediaType,
        media_id: props.mediaId,
      });
      list.contains = false;
      list.items_count = Math.max(0, list.items_count - 1);
    } else {
      await axios.post(`/lists/${list.id}/items`, {
        media_type: props.mediaType,
        media_id: props.mediaId,
      });
      list.contains = true;
      list.items_count = (list.items_count || 0) + 1;
    }
  } catch (err) {
    console.error('Error toggling custom list item:', err);
  } finally {
    list.processing = false;
  }
};

const createList = async () => {
  if (!newTitle.value.trim() || creating.value) return;
  creating.value = true;

  try {
    const res = await axios.post('/lists', {
      title: newTitle.value.trim(),
      description: newDescription.value.trim(),
      is_public: newIsPublic.value,
    });

    if (res.data.success && res.data.list) {
      const createdList = res.data.list;

      // Automatically add the current item to this brand new list!
      await axios.post(`/lists/${createdList.id}/items`, {
        media_type: props.mediaType,
        media_id: props.mediaId,
      });

      // Reset form
      newTitle.value = '';
      newDescription.value = '';
      showCreateForm.value = false;

      // Refresh list
      await fetchListsStatus();
    }
  } catch (err) {
    console.error('Error creating custom list:', err);
  } finally {
    creating.value = false;
  }
};

watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal) {
      fetchListsStatus();
    }
  }
);
</script>
