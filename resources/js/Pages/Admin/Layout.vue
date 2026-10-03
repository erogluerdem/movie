<template>
  <div class="h-screen w-screen overflow-hidden bg-[#07090e] text-gray-100 flex font-sans selection:bg-netflix selection:text-white relative">
    <!-- Ambient Background Lighting Accents -->
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-red-600/5 rounded-full blur-3xl pointer-events-none -z-10"></div>
    <div class="fixed bottom-0 right-1/4 w-96 h-96 bg-indigo-600/5 rounded-full blur-3xl pointer-events-none -z-10"></div>

    <!-- Mobile Sidebar Backdrop (Only for Sidebar mode on mobile) -->
    <div 
      v-if="menuLayout === 'sidebar' && mobileSidebarOpen" 
      @click="mobileSidebarOpen = false" 
      class="fixed inset-0 z-40 bg-black/80 backdrop-blur-md lg:hidden"
    ></div>

    <!-- Mobile Drawer for Sidebar Mode -->
    <aside 
      v-if="menuLayout === 'sidebar' && mobileSidebarOpen" 
      class="fixed inset-y-0 left-0 z-50 w-56 bg-gray-950/95 border-r border-white/10 flex flex-col justify-between shadow-2xl backdrop-blur-xl lg:hidden select-none"
    >
      <div class="flex-1 flex flex-col min-h-0">
        <div class="h-14 shrink-0 flex items-center justify-between px-3.5 border-b border-white/[0.08] bg-white/[0.02]">
          <Link href="/admin" class="flex items-center gap-2.5">
            <img src="/images/logos/logo_movie.png?v=2" alt="Movie®" class="h-6 w-auto flex-shrink-0" />
            <span class="px-1.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-gradient-to-r from-red-600 to-rose-600 text-white shadow-sm">
              ADMIN
            </span>
          </Link>
          <button 
            @click="mobileSidebarOpen = false" 
            class="text-gray-400 hover:text-white text-xs p-1.5 rounded-lg bg-white/5 hover:bg-white/10 transition"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>

        <nav class="flex-1 p-2 space-y-2.5 overflow-y-auto scrollbar-none">
          <div v-for="group in navGroups" :key="group.title" class="space-y-0.5">
            <div class="px-2 pt-1.5 pb-0.5 text-[9px] font-black tracking-wider text-gray-500/80 uppercase select-none">
              {{ group.title }}
            </div>
            <Link 
              v-for="item in group.items" 
              :key="item.href"
              :href="item.href"
              @click="mobileSidebarOpen = false"
              :class="[
                isActive(item.href) 
                  ? 'bg-gradient-to-r from-netflix to-red-700 text-white font-semibold shadow-sm shadow-red-600/30' 
                  : 'text-gray-400 hover:text-white hover:bg-white/[0.05] font-medium'
              ]"
              class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] transition-all duration-150"
            >
              <div 
                :class="isActive(item.href) ? 'bg-black/20 text-white' : 'bg-white/[0.04] text-gray-400'"
                class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0 text-[10px]"
              >
                <i :class="item.icon"></i>
              </div>
              <span class="truncate flex-1">{{ item.name }}</span>
              <span 
                v-if="item.badge && item.badge > 0" 
                :class="item.badgeClass || 'bg-amber-500 text-black font-black'"
                class="px-1.5 py-0.2 rounded-full text-[9px]"
              >
                {{ item.badge }}
              </span>
            </Link>
          </div>
        </nav>
      </div>

      <div class="p-2 shrink-0 border-t border-white/[0.06] space-y-1 bg-white/[0.01]">
        <Link 
          href="/" 
          class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] text-gray-400 hover:text-white hover:bg-white/[0.05] transition"
        >
          <div class="w-6 h-6 rounded-md bg-white/5 text-netflix flex items-center justify-center flex-shrink-0 text-[10px]">
            <i class="fas fa-external-link-alt"></i>
          </div>
          <span class="truncate font-medium">Ana Site</span>
        </Link>
        <button 
          @click="logout" 
          class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] text-red-400 hover:text-red-300 hover:bg-red-500/10 transition"
        >
          <div class="w-6 h-6 rounded-md bg-red-500/10 text-red-400 flex items-center justify-center flex-shrink-0 text-[10px]">
            <i class="fas fa-sign-out-alt"></i>
          </div>
          <span class="truncate font-medium">Çıkış Yap</span>
        </button>
      </div>
    </aside>

    <!-- Desktop Sidebar (Permanently Fixed in Place when menuLayout === 'sidebar') -->
    <aside 
      v-if="menuLayout === 'sidebar'"
      :class="sidebarCollapsed ? 'w-16' : 'w-52'"
      class="hidden lg:flex h-full bg-gray-950/95 border-r border-white/10 flex-col justify-between shrink-0 transition-all duration-200 shadow-2xl backdrop-blur-xl z-40 select-none"
    >
      <!-- Top Brand / Logo -->
      <div class="h-14 shrink-0 flex items-center justify-between px-3.5 border-b border-white/[0.08] bg-white/[0.02]">
        <Link href="/admin" class="flex items-center gap-2 overflow-hidden group">
          <img src="/images/logos/logo_movie.png?v=2" alt="Movie®" class="h-6 w-auto flex-shrink-0 group-hover:scale-105 transition" />
          <span 
            v-if="!sidebarCollapsed" 
            class="px-1.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-gradient-to-r from-red-600 to-rose-600 text-white shadow-sm ring-1 ring-white/10"
          >
            ADMIN
          </span>
        </Link>
      </div>

      <!-- Grouped Navigation Menu (Scrolls internally if items exceed height) -->
      <nav class="flex-1 p-2 space-y-2.5 overflow-y-auto scrollbar-none">
        <div v-for="group in navGroups" :key="group.title" class="space-y-0.5">
          <!-- Group Label -->
          <div 
            v-if="!sidebarCollapsed" 
            class="px-2 pt-1.5 pb-0.5 text-[9px] font-black tracking-wider text-gray-500/80 uppercase select-none"
          >
            {{ group.title }}
          </div>
          <div v-else class="h-1.5"></div>

          <!-- Group Items -->
          <Link 
            v-for="item in group.items" 
            :key="item.href"
            :href="item.href"
            :class="[
              isActive(item.href) 
                ? 'bg-gradient-to-r from-netflix to-red-700 text-white font-semibold shadow-sm shadow-red-600/30' 
                : 'text-gray-400 hover:text-white hover:bg-white/[0.05] font-medium'
            ]"
            class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] transition-all duration-150 group relative"
            :title="sidebarCollapsed ? item.name : ''"
          >
            <div 
              :class="[
                isActive(item.href) 
                  ? 'bg-black/20 text-white' 
                  : 'bg-white/[0.04] text-gray-400 group-hover:text-white group-hover:bg-white/10'
              ]"
              class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0 text-[10px] transition-colors"
            >
              <i :class="item.icon"></i>
            </div>
            <span v-if="!sidebarCollapsed" class="truncate flex-1 tracking-normal">{{ item.name }}</span>
            
            <!-- Live Counter Badge -->
            <span 
              v-if="!sidebarCollapsed && item.badge && item.badge > 0" 
              :class="item.badgeClass || 'bg-amber-500 text-black font-black'"
              class="px-1.5 py-0.2 rounded-full text-[9px] leading-none shadow-sm"
            >
              {{ item.badge }}
            </span>

            <!-- Collapsed Dot Indicator -->
            <span 
              v-if="sidebarCollapsed && item.badge && item.badge > 0" 
              class="absolute top-1.5 right-1.5 w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"
            ></span>
          </Link>
        </div>
      </nav>

      <!-- Sidebar Bottom Utility Shortcuts -->
      <div class="p-2 shrink-0 border-t border-white/[0.06] space-y-1 bg-white/[0.01]">
        <Link 
          href="/" 
          class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] text-gray-400 hover:text-white hover:bg-white/[0.05] transition"
          :title="sidebarCollapsed ? 'Ana Site' : ''"
        >
          <div class="w-6 h-6 rounded-md bg-white/5 text-netflix flex items-center justify-center flex-shrink-0 text-[10px]">
            <i class="fas fa-external-link-alt"></i>
          </div>
          <span v-if="!sidebarCollapsed" class="truncate font-medium">Ana Site</span>
        </Link>

        <button 
          @click="logout" 
          class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[11px] text-red-400 hover:text-red-300 hover:bg-red-500/10 transition"
          :title="sidebarCollapsed ? 'Çıkış Yap' : ''"
        >
          <div class="w-6 h-6 rounded-md bg-red-500/10 text-red-400 flex items-center justify-center flex-shrink-0 text-[10px]">
            <i class="fas fa-sign-out-alt"></i>
          </div>
          <span v-if="!sidebarCollapsed" class="truncate font-medium">Çıkış Yap</span>
        </button>
      </div>
    </aside>

    <!-- Main Content Area Wrapper (Pinned Header & Smooth Content Scroll) -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
      <!-- Top Application Header (Fixed at top) -->
      <header class="h-16 shrink-0 bg-gray-950/90 border-b border-white/10 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-3 z-30 backdrop-blur-lg">
          <!-- Left: Brand Logo (if top menu) OR Collapse Toggle & Breadcrumbs (if sidebar) -->
          <div class="flex items-center gap-3 sm:gap-4 min-w-0">
            <!-- Brand Logo (Visible when Top Menu is active, or on mobile when sidebar closed) -->
            <Link 
              v-if="menuLayout === 'top'" 
              href="/admin" 
              class="flex items-center gap-2.5 flex-shrink-0 mr-1"
            >
              <img src="/images/logos/logo_movie.png?v=2" alt="Movie®" class="h-7 w-auto" />
              <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-600 text-white shadow-md shadow-red-600/50">
                ADMIN
              </span>
            </Link>

            <!-- Mobile Toggle (Only in Sidebar mode on small screens) -->
            <button 
              v-if="menuLayout === 'sidebar'"
              @click="mobileSidebarOpen = true" 
              class="text-gray-400 hover:text-white lg:hidden p-2 rounded-lg bg-white/5"
              aria-label="Menüyü Aç"
            >
              <i class="fas fa-bars text-sm"></i>
            </button>

            <!-- Desktop Collapse Toggle (Only in Sidebar mode) -->
            <button 
              v-if="menuLayout === 'sidebar'"
              @click="sidebarCollapsed = !sidebarCollapsed" 
              class="text-gray-400 hover:text-white hidden lg:flex p-2 rounded-lg bg-white/5 hover:bg-white/10 text-xs transition"
              title="Kenar Çubuğunu Daralt/Genişlet"
            >
              <i :class="sidebarCollapsed ? 'fa-indent' : 'fa-outdent'" class="fas"></i>
            </button>

            <!-- Dynamic Breadcrumbs -->
            <nav class="hidden sm:flex items-center gap-1.5 text-xs text-gray-400 overflow-hidden truncate">
              <template v-for="(crumb, idx) in breadcrumbs" :key="crumb.label">
                <Link 
                  v-if="idx < breadcrumbs.length - 1"
                  :href="crumb.href" 
                  class="hover:text-white transition truncate max-w-[120px]"
                >
                  {{ crumb.label }}
                </Link>
                <span v-else class="text-white font-bold truncate max-w-[150px]">
                  {{ crumb.label }}
                </span>
                <span v-if="idx < breadcrumbs.length - 1" class="text-gray-600 text-[10px]">
                  <i class="fas fa-chevron-right"></i>
                </span>
              </template>
            </nav>
          </div>

          <!-- Middle: Quick Search / Jump -->
          <div class="relative hidden md:block w-48 lg:w-72">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
            <input 
              v-model="quickSearch"
              @focus="searchFocused = true"
              @blur="handleSearchBlur"
              type="text"
              :placeholder="$t('admin.search_modules')"
              class="w-full pl-8 pr-8 py-1.5 rounded-xl bg-white/[0.04] border border-white/10 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix focus:bg-black/60 focus:ring-1 focus:ring-netflix/40 transition"
            />
            <span v-if="!quickSearch" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-mono px-1.5 py-0.5 rounded bg-white/10 text-gray-400">
              /
            </span>
            <button 
              v-else 
              @click="quickSearch = ''" 
              class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white text-xs"
            >
              <i class="fas fa-times"></i>
            </button>

            <!-- Quick Search Dropdown Menu -->
            <div 
              v-if="searchFocused && filteredQuickLinks.length > 0" 
              class="absolute top-full left-0 right-0 mt-2 bg-gray-950/95 border border-white/10 rounded-2xl shadow-2xl p-1.5 z-50 divide-y divide-white/5 backdrop-blur-xl animate-scale-up"
            >
              <Link 
                v-for="item in filteredQuickLinks" 
                :key="item.href"
                :href="item.href"
                @mousedown="quickNavigate(item.href)"
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs text-gray-300 hover:text-white hover:bg-white/10 transition group"
              >
                <div class="w-6 h-6 rounded-lg bg-netflix/10 text-netflix flex items-center justify-center text-xs group-hover:bg-netflix group-hover:text-white transition">
                  <i :class="item.icon"></i>
                </div>
                <span class="font-semibold">{{ item.name }}</span>
                <span class="ml-auto text-[10px] text-gray-500 uppercase tracking-wider font-mono">{{ item.group }}</span>
              </Link>
            </div>
          </div>

          <!-- Right: Menu Layout Switcher, Language Switcher, Clear Cache, Create & Profile -->
          <div class="flex items-center gap-2 sm:gap-2.5">
            <!-- 🌐 Language Switcher in Admin Top Bar -->
            <LanguageSwitcher variant="navbar" />

            <!-- ⚡ MENU LAYOUT SELECTOR (Sol Menü / Üst Menü) ⚡ -->
            <div class="flex items-center bg-black/60 p-1 rounded-xl border border-white/10 text-xs shadow-inner backdrop-blur-md">
              <button 
                @click="setMenuLayout('sidebar')" 
                :class="menuLayout === 'sidebar' ? 'bg-gradient-to-r from-netflix to-red-700 text-white font-bold shadow-md shadow-red-600/40 ring-1 ring-white/20' : 'text-gray-400 hover:text-white'"
                class="flex items-center gap-1.5 px-3 py-1 rounded-lg transition-all duration-200 text-[11px] cursor-pointer"
                :title="$t('admin.sidebar_menu')"
              >
                <i class="fas fa-columns text-[10px]"></i>
                <span class="hidden sm:inline">{{ $t('admin.sidebar_menu') }}</span>
              </button>
              <button 
                @click="setMenuLayout('top')" 
                :class="menuLayout === 'top' ? 'bg-gradient-to-r from-netflix to-red-700 text-white font-bold shadow-md shadow-red-600/40 ring-1 ring-white/20' : 'text-gray-400 hover:text-white'"
                class="flex items-center gap-1.5 px-3 py-1 rounded-lg transition-all duration-200 text-[11px] cursor-pointer"
                :title="$t('admin.top_menu')"
              >
                <i class="fas fa-bars-staggered text-[10px]"></i>
                <span class="hidden sm:inline">{{ $t('admin.top_menu') }}</span>
              </button>
            </div>

            <!-- Clear Cache Shortcut -->
            <button 
              @click="triggerClearCache" 
              :disabled="clearingCache"
              class="px-2.5 py-1.5 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 text-gray-300 hover:text-white text-xs transition flex items-center gap-1.5 shadow-sm cursor-pointer"
              :title="$t('admin.clear_cache')"
            >
              <i :class="clearingCache ? 'fas fa-spinner fa-spin text-amber-400' : 'fas fa-broom text-amber-400'"></i>
              <span class="hidden xl:inline text-[11px] font-semibold">{{ $t('admin.cache') }}</span>
            </button>

            <!-- + Quick Action Dropdown -->
            <div class="relative">
              <button 
                @click="createMenuOpen = !createMenuOpen" 
                class="px-2.5 sm:px-3 py-1.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg shadow-red-600/30 flex items-center gap-1.5 cursor-pointer"
              >
                <i class="fas fa-plus text-[10px]"></i>
                <span class="hidden sm:inline">{{ $t('admin.create') }}</span>
                <i class="fas fa-chevron-down text-[9px] opacity-70 ml-0.5"></i>
              </button>

              <div 
                v-if="createMenuOpen" 
                @click="createMenuOpen = false"
                class="fixed inset-0 z-40"
              ></div>

              <div 
                v-if="createMenuOpen" 
                class="absolute right-0 mt-2 w-48 bg-gray-950 border border-white/10 rounded-xl shadow-2xl p-1.5 z-50 space-y-1 animate-scale-up"
              >
                <div class="px-2.5 py-1 text-[10px] font-black text-gray-500 uppercase tracking-wider">
                  {{ $t('admin.quick_actions') }}
                </div>
                <Link 
                  href="/admin/movies" 
                  class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-gray-300 hover:text-white hover:bg-white/10 transition font-medium"
                >
                  <i class="fas fa-film text-netflix w-4"></i>
                  <span>{{ $t('admin.add_movie') }}</span>
                </Link>
                <Link 
                  href="/admin/tv-shows" 
                  class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-gray-300 hover:text-white hover:bg-white/10 transition font-medium"
                >
                  <i class="fas fa-tv text-purple-400 w-4"></i>
                  <span>{{ $t('admin.add_tv') }}</span>
                </Link>
                <Link 
                  href="/admin/sports" 
                  class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-gray-300 hover:text-white hover:bg-white/10 transition font-medium"
                >
                  <i class="fas fa-futbol text-emerald-400 w-4"></i>
                  <span>{{ $t('admin.sports') }}</span>
                </Link>
                <Link 
                  href="/admin/users" 
                  class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-gray-300 hover:text-white hover:bg-white/10 transition font-medium"
                >
                  <i class="fas fa-user-plus text-sky-400 w-4"></i>
                  <span>{{ $t('admin.add_user') }}</span>
                </Link>
                <Link 
                  href="/admin/collections" 
                  class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-gray-300 hover:text-white hover:bg-white/10 transition font-medium"
                >
                  <i class="fas fa-folder-plus text-amber-400 w-4"></i>
                  <span>Koleksiyon Yönet</span>
                </Link>
                <Link 
                  href="/admin/announcements" 
                  class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-gray-300 hover:text-white hover:bg-white/10 transition font-medium"
                >
                  <i class="fas fa-bullhorn text-sky-400 w-4"></i>
                  <span>Duyuru / Bildirim</span>
                </Link>
              </div>
            </div>

            <!-- Profile Menu Dropdown -->
            <div class="relative pl-1 border-l border-white/10">
              <button 
                @click="profileMenuOpen = !profileMenuOpen" 
                class="flex items-center gap-2 p-1 rounded-xl hover:bg-white/5 transition"
              >
                <div class="relative">
                  <img 
                    :src="$page.props.auth?.user?.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop'"
                    :alt="$page.props.auth?.user?.name"
                    class="w-8 h-8 rounded-full object-cover ring-2 ring-netflix/50 flex-shrink-0"
                  />
                  <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-500 border-2 border-gray-950 rounded-full animate-pulse"></span>
                </div>
                <div class="hidden sm:block text-left leading-tight">
                  <div class="text-xs font-bold text-white truncate max-w-[90px]">{{ $page.props.auth?.user?.name }}</div>
                  <div class="text-[9px] font-black uppercase text-red-400 tracking-wider">Super Admin</div>
                </div>
                <i class="fas fa-chevron-down text-[9px] text-gray-500 hidden sm:inline"></i>
              </button>

              <div 
                v-if="profileMenuOpen" 
                @click="profileMenuOpen = false" 
                class="fixed inset-0 z-40"
              ></div>

              <div 
                v-if="profileMenuOpen" 
                class="absolute right-0 mt-2 w-56 bg-gray-950 border border-white/10 rounded-2xl shadow-2xl p-2 z-50 space-y-1.5 animate-scale-up"
              >
                <div class="p-2 rounded-xl bg-white/5 border border-white/5 space-y-1">
                  <div class="font-bold text-xs text-white truncate">{{ $page.props.auth?.user?.name }}</div>
                  <div class="text-[10px] text-gray-400 truncate">{{ $page.props.auth?.user?.email }}</div>
                  <div class="inline-block px-2 py-0.5 rounded text-[9px] font-black uppercase bg-red-600/30 text-red-400 border border-red-600/40">
                    Administrator
                  </div>
                </div>

                <div class="space-y-0.5 pt-1">
                  <Link 
                    href="/admin/settings" 
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs text-gray-300 hover:text-white hover:bg-white/10 transition"
                  >
                    <i class="fas fa-sliders-h text-netflix w-4 text-center"></i>
                    <span>Yönetici Ayarları</span>
                  </Link>
                  <Link 
                    href="/dashboard" 
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs text-gray-300 hover:text-white hover:bg-white/10 transition"
                  >
                    <i class="fas fa-columns text-blue-400 w-4 text-center"></i>
                    <span>Kullanıcı Paneli</span>
                  </Link>
                  <Link 
                    href="/dashboard/settings" 
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs text-gray-300 hover:text-white hover:bg-white/10 transition"
                  >
                    <i class="fas fa-user-cog text-purple-400 w-4 text-center"></i>
                    <span>Profil Tercihleri</span>
                  </Link>
                </div>

                <div class="border-t border-white/10 pt-1">
                  <button 
                    @click="logout" 
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs text-red-400 hover:text-red-300 hover:bg-red-500/10 transition font-medium"
                  >
                    <i class="fas fa-sign-out-alt w-4 text-center"></i>
                    <span>Çıkış Yap</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </header>

        <!-- 🌟 TOP MENU HORIZONTAL NAVBAR (Rendered when menuLayout === 'top') 🌟 -->
        <nav 
          v-if="menuLayout === 'top'" 
          class="shrink-0 bg-gray-950/80 border-b border-white/[0.08] px-4 sm:px-6 lg:px-8 py-2 z-20 backdrop-blur-2xl shadow-xl shadow-black/40"
        >
          <div class="w-full flex items-center gap-1.5 overflow-x-auto scrollbar-none py-1">
            <Link 
              v-for="item in flatNavItems" 
              :key="item.href"
              :href="item.href"
              :class="[
                isActive(item.href) 
                  ? 'bg-gradient-to-r from-netflix to-red-700 text-white font-bold shadow-md shadow-red-600/30 ring-1 ring-white/20' 
                  : 'text-gray-300 hover:text-white hover:bg-white/[0.06] border border-transparent hover:border-white/5 font-medium'
              ]"
              class="group flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all duration-200 flex-shrink-0"
            >
              <span class="w-6 h-6 rounded-lg flex items-center justify-center bg-white/[0.06] group-hover:bg-white/10 transition text-[11px]" :class="isActive(item.href) ? 'bg-black/20 text-white' : ''">
                <i :class="item.icon"></i>
              </span>
              <span>{{ item.name }}</span>
              <span 
                v-if="item.badge && item.badge > 0" 
                :class="item.badgeClass || 'bg-amber-500 text-black font-black'"
                class="px-1.5 py-0.5 rounded-full text-[10px] leading-none shadow-sm"
              >
                {{ item.badge }}
              </span>
            </Link>
          </div>
        </nav>

        <!-- Scrollable Main Container (Page content scrolls independently; Sidebar and Header stay fixed!) -->
        <div ref="contentScrollContainer" class="flex-1 overflow-y-auto overflow-x-hidden flex flex-col scroll-smooth">
          <!-- Main Body Content: Uçtan Uca Geniş Ekran (No max-w-7xl constraint!) -->
          <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 w-full">
            <!-- Flash Messages -->
            <div 
              v-if="$page.props.flash?.success" 
              class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs sm:text-sm flex items-center justify-between gap-3 animate-fade-in"
            >
              <div class="flex items-center gap-2.5">
                <i class="fas fa-check-circle text-base flex-shrink-0"></i>
                <span>{{ $page.props.flash.success }}</span>
              </div>
              <button @click="$page.props.flash.success = null" class="text-emerald-400/60 hover:text-emerald-400">
                <i class="fas fa-times text-xs"></i>
              </button>
            </div>

            <div 
              v-if="$page.props.flash?.error" 
              class="p-4 rounded-2xl bg-red-500/15 border border-red-500/30 text-red-400 text-xs sm:text-sm flex items-center justify-between gap-3 animate-fade-in"
            >
              <div class="flex items-center gap-2.5">
                <i class="fas fa-exclamation-circle text-base flex-shrink-0"></i>
                <span>{{ $page.props.flash.error }}</span>
              </div>
              <button @click="$page.props.flash.error = null" class="text-red-400/60 hover:text-red-400">
                <i class="fas fa-times text-xs"></i>
              </button>
            </div>

            <slot />
          </main>

          <!-- Informative Admin Footer: Uçtan Uca Geniş Ekran -->
          <footer class="mt-auto shrink-0 border-t border-white/10 bg-gray-950/80 backdrop-blur-md px-4 sm:px-6 lg:px-8 py-4 w-full">
            <div class="w-full flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
              <!-- Left: Brand & Build Info -->
              <div class="flex items-center gap-3 text-gray-400 text-center md:text-left">
                <div class="w-7 h-7 rounded-lg bg-netflix/20 text-netflix flex items-center justify-center font-black text-xs border border-netflix/30">
                  M
                </div>
                <div>
                  <div class="font-bold text-white flex items-center gap-2">
                    <span>Movie® Kontrol Merkezi</span>
                    <span class="px-1.5 py-0.2 rounded text-[9px] font-mono bg-white/10 text-gray-300">v2.5 Pro</span>
                  </div>
                  <div class="text-[10px] text-gray-500">© 2026 Movie® Inc. Yüksek performanslı sinema motoru.</div>
                </div>
              </div>

              <!-- Middle: Live Telemetry Status -->
              <div class="flex items-center flex-wrap justify-center gap-2 text-[11px]">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 font-semibold">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                  <span>Sistem: Operasyonel</span>
                </span>
                <span class="px-2 py-0.5 rounded-lg bg-white/5 border border-white/5 text-gray-400 font-mono text-[10px]">
                  PHP 8.3
                </span>
                <span class="px-2 py-0.5 rounded-lg bg-white/5 border border-white/5 text-gray-400 font-mono text-[10px]">
                  Laravel 11
                </span>
                <span class="px-2 py-0.5 rounded-lg bg-white/5 border border-white/5 text-gray-400 font-mono text-[10px]">
                  DB: SQLite
                </span>
              </div>

              <!-- Right: Quick Navigation & Scroll to top -->
              <div class="flex items-center gap-3 text-gray-400">
                <Link href="/" class="hover:text-white transition flex items-center gap-1 text-[11px] px-2.5 py-1 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/5">
                  <i class="fas fa-external-link-alt text-[9px] text-netflix"></i>
                  <span>Ana Site</span>
                </Link>
                <button 
                  @click="triggerClearCache" 
                  :disabled="clearingCache"
                  class="hover:text-amber-400 transition flex items-center gap-1 text-[11px] px-2.5 py-1 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/5"
                  title="Tüm önbelleği temizle"
                >
                  <i :class="clearingCache ? 'fas fa-spinner fa-spin text-amber-400' : 'fas fa-broom text-amber-400'" class="text-[10px]"></i>
                  <span>Önbellek Temizle</span>
                </button>
                <button 
                  @click="scrollToTop" 
                  class="w-7 h-7 rounded-lg bg-white/5 hover:bg-white/15 text-gray-300 hover:text-white transition flex items-center justify-center text-xs border border-white/5"
                  title="Sayfa Başına Dön"
                >
                  <i class="fas fa-arrow-up"></i>
                </button>
              </div>
            </div>
          </footer>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useI18n } from '@/Composables/useI18n';

const page = usePage();
const { t } = useI18n();
const sidebarCollapsed = ref(false);
const mobileSidebarOpen = ref(false);
const createMenuOpen = ref(false);
const profileMenuOpen = ref(false);
const quickSearch = ref('');
const searchFocused = ref(false);
const clearingCache = ref(false);
const contentScrollContainer = ref(null);

const scrollToTop = () => {
  if (contentScrollContainer.value) {
    contentScrollContainer.value.scrollTo({ top: 0, behavior: 'smooth' });
  }
  if (typeof window !== 'undefined') {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

// Layout Preference: 'sidebar' (Sol Menü) or 'top' (Üst Menü)
const menuLayout = ref('sidebar');

onMounted(() => {
  if (typeof window !== 'undefined') {
    const saved = localStorage.getItem('admin_menu_layout');
    if (saved === 'top' || saved === 'sidebar') {
      menuLayout.value = saved;
    }
  }
});

const setMenuLayout = (layout) => {
  menuLayout.value = layout;
  if (typeof window !== 'undefined') {
    localStorage.setItem('admin_menu_layout', layout);
  }
};

const navGroups = computed(() => [
  {
    title: t('admin.analytics'),
    items: [
      { name: t('admin.dashboard'), href: '/admin', icon: 'fas fa-chart-pie' },
      { name: t('admin.analytics'), href: '/admin/analytics', icon: 'fas fa-chart-line' },
      { 
        name: t('admin.reports'), 
        href: '/admin/reports', 
        icon: 'fas fa-flag',
        badge: page.props.admin_badges?.pending_reports,
        badgeClass: 'bg-rose-500 text-white font-extrabold shadow-sm'
      },
      { name: t('admin.logs'), href: '/admin/logs', icon: 'fas fa-shield-alt' },
    ],
  },
  {
    title: t('admin.movies'),
    items: [
      { name: t('admin.movies'), href: '/admin/movies', icon: 'fas fa-film' },
      { name: t('admin.tv_shows'), href: '/admin/tv-shows', icon: 'fas fa-tv' },
      { name: t('admin.collections'), href: '/admin/collections', icon: 'fas fa-folder-plus' },
      { 
        name: t('admin.sports'), 
        href: '/admin/sports', 
        icon: 'fas fa-futbol', 
        badge: page.props.admin_badges?.live_sports,
        badgeClass: 'bg-emerald-500 text-black font-extrabold shadow-sm'
      },
      { 
        name: t('admin.reviews'), 
        href: '/admin/reviews', 
        icon: 'fas fa-star-half-alt', 
        badge: page.props.admin_badges?.total_reviews,
        badgeClass: 'bg-amber-500 text-black font-extrabold shadow-sm'
      },
    ],
  },
  {
    title: t('admin.users'),
    items: [
      { name: t('admin.users'), href: '/admin/users', icon: 'fas fa-users' },
      { 
        name: t('admin.requests'), 
        href: '/admin/requests', 
        icon: 'fas fa-envelope-open-text', 
        badge: page.props.admin_badges?.pending_requests,
        badgeClass: 'bg-rose-500 text-white font-extrabold shadow-sm'
      },
      { name: t('admin.announcements'), href: '/admin/announcements', icon: 'fas fa-bullhorn' },
    ],
  },
  {
    title: t('admin.settings'),
    items: [
      { 
        name: t('admin.tmdb_import'), 
        href: '/admin/tmdb', 
        icon: 'fas fa-robot', 
        badge: 'AUTO', 
        badgeClass: 'bg-indigo-500 text-white font-extrabold shadow-sm' 
      },
      { 
        name: t('admin.tmdb_bulk'), 
        href: '/admin/tmdb/bulk', 
        icon: 'fas fa-layer-group',
        badge: 'BULK',
        badgeClass: 'bg-emerald-500 text-black font-extrabold shadow-sm'
      },
      { 
        name: t('admin.ads'), 
        href: '/admin/ads', 
        icon: 'fas fa-ad',
        badge: 'ADS',
        badgeClass: 'bg-amber-500 text-black font-extrabold shadow-sm'
      },
      { name: t('admin.settings'), href: '/admin/settings', icon: 'fas fa-sliders-h' },
    ],
  },
]);

const flatNavItems = computed(() => [
  { name: t('admin.dashboard'), href: '/admin', icon: 'fas fa-chart-pie' },
  { name: t('admin.analytics'), href: '/admin/analytics', icon: 'fas fa-chart-line' },
  { name: t('admin.movies'), href: '/admin/movies', icon: 'fas fa-film' },
  { name: t('admin.tv_shows'), href: '/admin/tv-shows', icon: 'fas fa-tv' },
  { name: t('admin.collections'), href: '/admin/collections', icon: 'fas fa-folder-plus' },
  { 
    name: t('admin.reports'), 
    href: '/admin/reports', 
    icon: 'fas fa-flag',
    badge: page.props.admin_badges?.pending_reports,
    badgeClass: 'bg-rose-500 text-white font-extrabold shadow-sm'
  },
  { 
    name: t('admin.sports'), 
    href: '/admin/sports', 
    icon: 'fas fa-futbol', 
    badge: page.props.admin_badges?.live_sports,
    badgeClass: 'bg-emerald-500 text-black font-extrabold shadow-sm'
  },
  { 
    name: t('admin.requests'), 
    href: '/admin/requests', 
    icon: 'fas fa-envelope-open-text', 
    badge: page.props.admin_badges?.pending_requests,
    badgeClass: 'bg-rose-500 text-white font-extrabold shadow-sm'
  },
  { name: t('admin.users'), href: '/admin/users', icon: 'fas fa-users' },
  { name: t('admin.announcements'), href: '/admin/announcements', icon: 'fas fa-bullhorn' },
  { 
    name: t('admin.tmdb_import'), 
    href: '/admin/tmdb', 
    icon: 'fas fa-robot', 
    badge: 'AUTO', 
    badgeClass: 'bg-indigo-500 text-white font-extrabold shadow-sm' 
  },
  { name: t('admin.tmdb_bulk'), href: '/admin/tmdb/bulk', icon: 'fas fa-layer-group' },
  { name: t('admin.logs'), href: '/admin/logs', icon: 'fas fa-shield-alt' },
  { name: t('admin.ads'), href: '/admin/ads', icon: 'fas fa-ad' },
  { name: t('admin.settings'), href: '/admin/settings', icon: 'fas fa-sliders-h' },
]);

const breadcrumbs = computed(() => {
  const url = page.url.split('?')[0];
  const crumbs = [{ label: 'Admin', href: '/admin' }];

  if (url === '/admin') {
    crumbs.push({ label: 'Dashboard', href: '/admin' });
  } else if (url.startsWith('/admin/analytics')) {
    crumbs.push({ label: 'Analizler', href: '/admin/analytics' });
    crumbs.push({ label: 'Yayın Analitiği', href: '/admin/analytics' });
  } else if (url.startsWith('/admin/reports')) {
    crumbs.push({ label: 'Moderasyon', href: '/admin/reports' });
    crumbs.push({ label: 'Hata Bildirimleri', href: '/admin/reports' });
  } else if (url.startsWith('/admin/logs')) {
    crumbs.push({ label: 'Güvenlik', href: '/admin/logs' });
    crumbs.push({ label: 'Denetim Logları', href: '/admin/logs' });
  } else if (url.startsWith('/admin/collections')) {
    crumbs.push({ label: 'İçerik', href: '/admin/collections' });
    crumbs.push({ label: 'Özel Koleksiyonlar', href: '/admin/collections' });
  } else if (url.startsWith('/admin/announcements')) {
    crumbs.push({ label: 'İletişim', href: '/admin/announcements' });
    crumbs.push({ label: 'Duyuru & Bildirimler', href: '/admin/announcements' });
  } else if (url.startsWith('/admin/tmdb/bulk')) {
    crumbs.push({ label: 'Sistem', href: '/admin/tmdb' });
    crumbs.push({ label: 'Toplu TMDB İçe Aktar', href: '/admin/tmdb/bulk' });
  } else if (url.startsWith('/admin/tmdb')) {
    crumbs.push({ label: 'Sistem', href: '/admin/tmdb' });
    crumbs.push({ label: 'TMDB Bot & Senkron', href: '/admin/tmdb' });
  } else if (url.startsWith('/admin/movies')) {
    crumbs.push({ label: 'İçerik', href: '/admin/movies' });
    crumbs.push({ label: 'Filmler', href: '/admin/movies' });
  } else if (url.startsWith('/admin/tv-shows')) {
    crumbs.push({ label: 'İçerik', href: '/admin/tv-shows' });
    crumbs.push({ label: 'Dizi & Anime', href: '/admin/tv-shows' });
  } else if (url.startsWith('/admin/sports')) {
    crumbs.push({ label: 'İçerik', href: '/admin/sports' });
    crumbs.push({ label: 'Canlı Sporlar', href: '/admin/sports' });
  } else if (url.startsWith('/admin/reviews')) {
    crumbs.push({ label: 'İçerik', href: '/admin/reviews' });
    crumbs.push({ label: 'İncelemeler', href: '/admin/reviews' });
  } else if (url.startsWith('/admin/requests')) {
    crumbs.push({ label: 'Topluluk', href: '/admin/requests' });
    crumbs.push({ label: 'Talepler', href: '/admin/requests' });
  } else if (url.startsWith('/admin/users')) {
    crumbs.push({ label: 'Topluluk', href: '/admin/users' });
    crumbs.push({ label: 'Kullanıcılar', href: '/admin/users' });
  } else if (url.startsWith('/admin/ads')) {
    crumbs.push({ label: 'Sistem', href: '/admin/settings' });
    crumbs.push({ label: 'Reklam & Gelir', href: '/admin/ads' });
  } else if (url.startsWith('/admin/settings')) {
    crumbs.push({ label: 'Sistem', href: '/admin/settings' });
    crumbs.push({ label: 'Ayarlar & Yedek', href: '/admin/settings' });
  } else {
    crumbs.push({ label: 'Genel Bakış', href: '/admin' });
  }

  return crumbs;
});

const quickSearchCatalog = [
  { name: 'Dashboard Analytics', href: '/admin', group: 'Analizler', icon: 'fas fa-chart-pie' },
  { name: 'Yayın İstatistikleri & Trendler', href: '/admin/analytics', group: 'Analizler', icon: 'fas fa-chart-line' },
  { name: 'Reklam & Monetizasyon Yönetimi', href: '/admin/ads', group: 'Sistem', icon: 'fas fa-ad' },
  { name: 'Hata & Kırık Link Raporları', href: '/admin/reports', group: 'Moderasyon', icon: 'fas fa-flag' },
  { name: 'Güvenlik & İşlem Logları', href: '/admin/logs', group: 'Güvenlik', icon: 'fas fa-shield-alt' },
  { name: 'Özel Koleksiyonlar & Vitrin', href: '/admin/collections', group: 'İçerik', icon: 'fas fa-folder-plus' },
  { name: 'Duyuru Bandı & Toplu Bildirim', href: '/admin/announcements', group: 'İletişim', icon: 'fas fa-bullhorn' },
  { name: 'Toplu TMDB İçe Aktarma', href: '/admin/tmdb/bulk', group: 'Sistem', icon: 'fas fa-layer-group' },
  { name: 'TMDB Bot & Otomasyon', href: '/admin/tmdb', group: 'Sistem', icon: 'fas fa-robot' },
  { name: 'Filmleri Yönet', href: '/admin/movies', group: 'İçerik', icon: 'fas fa-film' },
  { name: 'Dizi & Anime Yönetimi', href: '/admin/tv-shows', group: 'İçerik', icon: 'fas fa-tv' },
  { name: 'Canlı Spor Maçları', href: '/admin/sports', group: 'İçerik', icon: 'fas fa-futbol' },
  { name: 'İnceleme Moderasyonu', href: '/admin/reviews', group: 'İçerik', icon: 'fas fa-star-half-alt' },
  { name: 'Kullanıcılar & Roller', href: '/admin/users', group: 'Topluluk', icon: 'fas fa-users' },
  { name: 'İçerik Talepleri', href: '/admin/requests', group: 'Topluluk', icon: 'fas fa-envelope-open-text' },
  { name: 'Platform Ayarları & DB Yedek', href: '/admin/settings', group: 'Sistem', icon: 'fas fa-sliders-h' },
];

const filteredQuickLinks = computed(() => {
  if (!quickSearch.value) return quickSearchCatalog.slice(0, 8);
  const q = quickSearch.value.toLowerCase();
  return quickSearchCatalog.filter(i => 
    i.name.toLowerCase().includes(q) || i.group.toLowerCase().includes(q)
  );
});

const handleSearchBlur = () => {
  setTimeout(() => {
    searchFocused.value = false;
  }, 200);
};

const quickNavigate = (href) => {
  quickSearch.value = '';
  searchFocused.value = false;
  router.visit(href);
};

const isActive = (href) => {
  const currentUrl = page.url.split('?')[0];
  if (href === '/admin') {
    return currentUrl === '/admin';
  }
  return currentUrl.startsWith(href);
};

const triggerClearCache = () => {
  clearingCache.value = true;
  router.post('/admin/clear-cache', {}, {
    preserveScroll: true,
    onFinish: () => {
      clearingCache.value = false;
    },
  });
};

const logout = () => {
  router.post('/logout');
};
</script>
