<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-white/10">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <i class="fas fa-users text-blue-400"></i>
            <span>Users Management</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/10 text-gray-300">
              {{ users.total || 0 }}
            </span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">Manage platform members, assign admin privileges, and inspect member activity.</p>
        </div>
      </div>

      <!-- Filters & Search Bar -->
      <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="relative w-full md:w-80">
          <input 
            type="text" 
            v-model="search" 
            @keydown.enter="applyFilters"
            placeholder="Search by name or email..."
            class="w-full h-10 bg-black/60 border border-white/10 rounded-xl pl-9 pr-3 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix focus:ring-1 focus:ring-netflix transition"
          />
          <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500"></i>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
          <select 
            v-model="roleFilter" 
            @change="applyFilters"
            class="h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-gray-300 focus:outline-none focus:border-netflix cursor-pointer"
          >
            <option value="">All Roles</option>
            <option value="admin">Admins Only</option>
            <option value="user">Regular Users</option>
          </select>

          <button 
            @click="resetFilters" 
            class="px-3 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white text-xs font-semibold transition"
          >
            Reset
          </button>
        </div>
      </div>

      <!-- Users Table -->
      <div class="rounded-2xl border border-white/10 bg-gray-900/60 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-gray-300 divide-y divide-white/10">
            <thead class="bg-white/5 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
              <tr>
                <th class="py-3 px-4">User</th>
                <th class="py-3 px-4 text-center">Role</th>
                <th class="py-3 px-4 text-center">Quality Pref</th>
                <th class="py-3 px-4 text-center">Activity</th>
                <th class="py-3 px-4">Member Since</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr 
                v-for="user in users.data" 
                :key="user.id"
                class="hover:bg-white/5 transition"
              >
                <!-- User Profile -->
                <td class="py-3 px-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <img 
                      :src="user.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop'" 
                      class="w-9 h-9 rounded-xl object-cover ring-2 ring-white/10"
                    />
                    <div>
                      <div class="font-bold text-white text-sm flex items-center gap-1.5">
                        <span>{{ user.name }}</span>
                        <span v-if="user.id === $page.props.auth?.user?.id" class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-white/10 text-netflix">
                          You
                        </span>
                        <span v-if="user.is_banned" class="text-[9px] font-black px-1.5 py-0.5 rounded bg-red-600/30 text-red-400 border border-red-600/40">
                          Yasaklı
                        </span>
                      </div>
                      <div class="text-[11px] text-gray-400">{{ user.email }}</div>
                      <div v-if="user.is_banned && user.banned_reason" class="text-[10px] text-red-400/80 italic">
                        Sebep: {{ user.banned_reason }}
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Role Badge -->
                <td class="py-3 px-4 text-center whitespace-nowrap">
                  <span 
                    :class="user.role === 'admin' ? 'bg-red-500/20 text-red-400 border-red-500/30' : 'bg-white/10 text-gray-400 border-white/10'"
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase border"
                  >
                    {{ user.role }}
                  </span>
                </td>

                <!-- Quality -->
                <td class="py-3 px-4 text-center whitespace-nowrap font-mono text-gray-300">
                  {{ user.preferred_quality || '1080p' }}
                </td>

                <!-- Activity -->
                <td class="py-3 px-4 text-center whitespace-nowrap">
                  <div class="flex items-center justify-center gap-2 text-[11px] text-gray-400">
                    <span title="Watched entries">
                      <i class="fas fa-play text-[9px] text-netflix mr-1"></i>
                      {{ user.watch_histories_count || 0 }}
                    </span>
                    <span>•</span>
                    <span title="Submitted requests">
                      <i class="fas fa-envelope text-[9px] text-amber-400 mr-1"></i>
                      {{ user.content_requests_count || 0 }}
                    </span>
                  </div>
                </td>

                <!-- Date -->
                <td class="py-3 px-4 whitespace-nowrap text-gray-400">
                  {{ formatDate(user.created_at) }}
                </td>

                <!-- Actions -->
                <td class="py-3 px-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <!-- Toggle Role (if not self) -->
                    <button 
                      v-if="user.id !== $page.props.auth?.user?.id"
                      @click="toggleRole(user)"
                      :class="user.role === 'admin' ? 'bg-amber-500/10 hover:bg-amber-500/20 text-amber-400' : 'bg-red-500/10 hover:bg-red-500/20 text-red-400'"
                      class="px-2.5 py-1 rounded-lg text-xs font-bold transition"
                      :title="user.role === 'admin' ? 'Demote to User' : 'Promote to Admin'"
                    >
                      <i :class="user.role === 'admin' ? 'fas fa-user-minus' : 'fas fa-user-shield'" class="mr-1"></i>
                      <span>{{ user.role === 'admin' ? 'Demote' : 'Make Admin' }}</span>
                    </button>

                    <!-- Toggle Ban -->
                    <button 
                      v-if="user.id !== $page.props.auth?.user?.id"
                      @click="toggleBan(user)"
                      :class="user.is_banned ? 'bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400' : 'bg-red-600/10 hover:bg-red-600/20 text-red-400'"
                      class="px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1"
                      :title="user.is_banned ? 'Yasağı kaldır' : 'Kullanıcıyı yasakla'"
                    >
                      <i :class="user.is_banned ? 'fas fa-unlock' : 'fas fa-ban'"></i>
                      <span>{{ user.is_banned ? 'Aç' : 'Yasakla' }}</span>
                    </button>

                    <button 
                      @click="openEditModal(user)"
                      class="w-7 h-7 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 flex items-center justify-center transition"
                      title="Edit User"
                    >
                      <i class="fas fa-edit"></i>
                    </button>

                    <button 
                      v-if="user.id !== $page.props.auth?.user?.id"
                      @click="deleteUser(user)"
                      class="w-7 h-7 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 flex items-center justify-center transition"
                      title="Delete User"
                    >
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="users.links && users.links.length > 3" class="p-4 border-t border-white/10 flex justify-center gap-1">
          <Link 
            v-for="(link, i) in users.links" 
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

      <!-- Edit User Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/80 backdrop-blur-md" @click="showModal = false"></div>

        <div class="relative w-full max-w-md rounded-3xl bg-gray-900 border border-white/15 p-6 shadow-2xl z-10 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-white/10">
            <h3 class="text-sm font-extrabold text-white">
              Edit User: {{ activeUser?.name }}
            </h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-white text-sm">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="submitUserEdit" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1">Name *</label>
              <input 
                type="text" 
                v-model="userForm.name" 
                required 
                class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1">Email *</label>
              <input 
                type="email" 
                v-model="userForm.email" 
                required 
                class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1">Role</label>
              <select 
                v-model="userForm.role"
                class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
              >
                <option value="user">User (Regular Member)</option>
                <option value="admin">Admin (Full Permissions)</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-300 mb-1">New Password (optional)</label>
              <input 
                type="password" 
                v-model="userForm.password" 
                placeholder="Leave blank to keep current password"
                class="w-full h-10 bg-black/60 border border-white/10 rounded-xl px-3 text-xs text-white"
              />
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
  users: {
    type: Object,
    default: () => ({ data: [], links: [], total: 0 }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || '');

const showModal = ref(false);
const activeUser = ref(null);
const userForm = ref({
  name: '',
  email: '',
  role: 'user',
  password: '',
});

const applyFilters = () => {
  router.get('/admin/users', {
    search: search.value || undefined,
    role: roleFilter.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  search.value = '';
  roleFilter.value = '';
  applyFilters();
};

const openEditModal = (user) => {
  activeUser.value = user;
  userForm.value = {
    name: user.name,
    email: user.email,
    role: user.role,
    password: '',
  };
  showModal.value = true;
};

const submitUserEdit = () => {
  router.put(`/admin/users/${activeUser.value.id}`, userForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      showModal.value = false;
    },
  });
};

const toggleRole = (user) => {
  router.post(`/admin/users/${user.id}/toggle-role`, {}, {
    preserveScroll: true,
  });
};

const toggleBan = (user) => {
  const reason = user.is_banned ? null : prompt(`"${user.name}" kullanıcısını askıya alma nedeni:`, 'Platform kuralları ihlali');
  if (!user.is_banned && reason === null) return;
  router.post(`/admin/users/${user.id}/toggle-ban`, { reason }, {
    preserveScroll: true,
  });
};

const deleteUser = (user) => {
  if (confirm(`Are you sure you want to delete user "${user.name}"?`)) {
    router.delete(`/admin/users/${user.id}`, {
      preserveScroll: true,
    });
  }
};

const formatDate = (str) => {
  if (!str) return 'Recently';
  return new Date(str).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>
