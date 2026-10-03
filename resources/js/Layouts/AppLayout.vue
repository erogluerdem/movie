<template>
  <div class="min-h-screen bg-gray-900 text-white font-sans selection:bg-netflix selection:text-white">
    <!-- Hero Background Image (Fixed) like original -->
    <div class="fixed inset-0 -z-10 pointer-events-none">
      <div class="absolute inset-0 bg-gradient-to-br from-black via-black to-black"></div>
      <div class="absolute inset-0 opacity-20">
        <img 
          src="https://image.tmdb.org/t/p/w1280/ceTxx1kqdygf9mBvVOT9oE1OCil.jpg" 
          alt="Background" 
          class="w-full h-full object-cover"
        />
      </div>
      <div class="absolute inset-0 bg-gradient-to-b from-transparent via-black/50 to-black"></div>
    </div>

    <!-- Global Announcement Banner -->
    <div 
      v-if="$page.props.site_announcement" 
      class="bg-gradient-to-r from-red-700 via-netflix to-red-800 text-white text-xs font-semibold py-2 px-4 text-center shadow-lg relative z-50 flex items-center justify-center gap-2"
    >
      <i class="fas fa-bullhorn text-amber-300 animate-pulse text-xs"></i>
      <span>{{ $page.props.site_announcement }}</span>
    </div>

    <!-- Original Movie® Header (site-header-v2) -->
    <nav class="site-header-v2 relative z-50 glass-dark border-b border-white/10">
      <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <!-- Left: Logo & Nav -->
          <div class="site-header-left flex items-center min-w-0 flex-1">
            <div class="flex-shrink-0">
              <Link href="/" class="site-header-brand flex items-center">
                <img src="/images/logos/logo_movie.png?v=2" alt="Movie®" class="h-8 lg:h-10 w-auto">
              </Link>
            </div>

            <!-- Desktop Nav Links -->
            <div class="hidden lg:block">
              <div class="site-header-primary ml-6 xl:ml-10 flex items-baseline space-x-1 xl:space-x-2">
                <Link 
                  href="/" 
                  :class="[$page.url === '/' ? 'bg-netflix text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white']"
                  class="px-2 xl:px-3 py-2 rounded-md text-xs xl:text-sm font-medium transition-colors flex items-center"
                >
                  <i class="fas fa-home w-3 xl:w-4 mr-1 xl:mr-2"></i>
                  <span class="hidden xl:inline">{{ $t('nav.home') }}</span>
                </Link>

                <Link 
                  href="/trending" 
                  :class="[$page.url.startsWith('/trending') ? 'bg-netflix text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white']"
                  class="px-2 xl:px-3 py-2 rounded-md text-xs xl:text-sm font-medium transition-colors flex items-center"
                >
                  <i class="fas fa-fire w-3 xl:w-4 mr-1 xl:mr-2"></i>
                  <span class="hidden xl:inline">{{ $t('nav.trending') }}</span>
                </Link>

                <Link 
                  href="/movies" 
                  :class="[$page.url.startsWith('/movie') ? 'bg-netflix text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white']"
                  class="px-2 xl:px-3 py-2 rounded-md text-xs xl:text-sm font-medium transition-colors flex items-center"
                >
                  <i class="fas fa-film w-3 xl:w-4 mr-1 xl:mr-2"></i>
                  <span class="hidden xl:inline">{{ $t('nav.movies') }}</span>
                </Link>

                <Link 
                  href="/tv-shows" 
                  :class="[$page.url.startsWith('/tv-show') ? 'bg-netflix text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white']"
                  class="px-2 xl:px-3 py-2 rounded-md text-xs xl:text-sm font-medium transition-colors flex items-center"
                >
                  <i class="fas fa-tv w-3 xl:w-4 mr-1 xl:mr-2"></i>
                  <span class="hidden xl:inline">{{ $t('nav.tv_shows') }}</span>
                </Link>

                <Link 
                  href="/watchlist" 
                  :class="[$page.url.startsWith('/watchlist') ? 'bg-netflix text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white']"
                  class="px-2 xl:px-3 py-2 rounded-md text-xs xl:text-sm font-medium transition-colors flex items-center"
                >
                  <i class="fas fa-plus w-3 xl:w-4 mr-1 xl:mr-2"></i>
                  <span class="hidden xl:inline">{{ $t('nav.my_list') }}</span>
                </Link>

                <!-- More Dropdown (Anime & Sports) -->
                <div class="relative" @mouseenter="moreDropdownOpen = true" @mouseleave="moreDropdownOpen = false">
                  <button 
                    type="button"
                    class="px-2 xl:px-3 py-2 rounded-md text-xs xl:text-sm font-medium text-gray-300 hover:bg-white/10 hover:text-white transition-colors flex items-center focus:outline-none cursor-pointer"
                  >
                    <i class="fas fa-th-large w-3 xl:w-4 mr-1 xl:mr-2"></i>
                    <span class="hidden xl:inline">{{ $t('nav.more') }}</span>
                    <i :class="[moreDropdownOpen ? 'rotate-180' : '']" class="fas fa-chevron-down ml-1 text-xs transition-transform duration-200"></i>
                  </button>

                  <div 
                    v-if="moreDropdownOpen"
                    class="absolute top-full left-0 mt-1 w-44 bg-black/95 backdrop-blur-lg border border-white/10 rounded-lg shadow-xl z-50 p-1"
                  >
                    <Link 
                      href="/anime"
                      class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-gray-300 hover:bg-white/10 hover:text-white transition-colors"
                    >
                      <i class="fas fa-torii-gate w-4 text-center text-netflix"></i>
                      <span>{{ $t('nav.anime') }}</span>
                    </Link>
                    <Link 
                      href="/sports"
                      class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-gray-300 hover:bg-white/10 hover:text-white transition-colors"
                    >
                      <i class="fas fa-futbol w-4 text-center text-netflix"></i>
                      <span>{{ $t('nav.sports') }}</span>
                    </Link>
                    <Link 
                      href="/lists"
                      class="flex items-center gap-2 px-3 py-2 rounded-md text-sm text-gray-300 hover:bg-white/10 hover:text-white transition-colors"
                    >
                      <i class="fas fa-layer-group w-4 text-center text-netflix"></i>
                      <span>{{ $t('nav.collections') }}</span>
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Middle: Desktop Mega Search Trigger -->
          <div class="site-header-search hidden lg:flex items-center flex-shrink-0 w-48 lg:w-56 xl:w-72 mx-2 lg:mx-4 xl:mx-6">
            <button
              type="button"
              @click="showMegaSearch = true"
              class="w-full h-10 bg-white/[0.08] hover:bg-white/[0.14] border border-white/15 hover:border-white/25 rounded-xl px-3.5 flex items-center justify-between text-xs text-zinc-400 hover:text-zinc-200 backdrop-blur-md transition-all duration-200 shadow-inner group cursor-pointer"
              :title="$t('nav.search_hint')"
            >
              <div class="flex items-center gap-2.5 min-w-0">
                <svg class="w-4 h-4 text-zinc-400 group-hover:text-netflix transition-colors flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="11" cy="11" r="8"></circle>
                  <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <span class="font-medium text-zinc-400 group-hover:text-zinc-200 truncate">{{ $t('nav.search_placeholder') }}</span>
              </div>
              <kbd class="hidden xl:inline-block px-1.5 py-0.5 rounded bg-white/10 border border-white/10 text-[10px] font-mono text-zinc-400 group-hover:text-white flex-shrink-0">
                Ctrl K
              </kbd>
            </button>
          </div>

          <!-- Right Side Actions (Desktop & Mobile) -->
          <div class="site-header-actions flex items-center space-x-1.5 flex-shrink-0">
            <!-- Mobile Actions -->
            <div class="header-mobile-actions flex lg:hidden items-center justify-end gap-1.5 flex-shrink-0">
              <!-- Mobile Language Switcher -->
              <LanguageSwitcher variant="minimal" class="flex-shrink-0" />

              <button 
                type="button" 
                @click="showMegaSearch = true" 
                class="w-9 h-9 rounded-full bg-white/5 active:bg-white/15 text-gray-300 hover:text-white flex items-center justify-center transition active:scale-90 touch-press cursor-pointer flex-shrink-0"
                :aria-label="$t('search.title')"
              >
                <i class="fas fa-search text-sm"></i>
              </button>
              
              <button 
                v-if="$page.props.auth?.user"
                type="button"
                @click="toggleNotifications"
                class="relative w-9 h-9 rounded-full bg-white/5 active:bg-white/15 text-gray-300 hover:text-white flex items-center justify-center transition active:scale-90 touch-press cursor-pointer flex-shrink-0"
                :title="$t('nav.notifications')"
              >
                <i class="fas fa-bell text-sm"></i>
                <span 
                  v-if="unreadNotificationsCount > 0"
                  class="absolute top-1 right-1 min-w-3.5 h-3.5 px-0.5 rounded-full bg-netflix text-[9px] font-black text-white flex items-center justify-center shadow"
                >
                  {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
                </span>
              </button>

              <Link 
                v-if="!$page.props.auth?.user" 
                href="/login" 
                class="bg-netflix hover:bg-netflix-dark px-2.5 py-1.5 rounded-xl text-xs font-bold transition-all text-white shadow-md shadow-red-900/40 active:scale-95 touch-press whitespace-nowrap flex-shrink-0"
              >
                {{ $t('nav.login') }}
              </Link>
              
              <button 
                type="button" 
                @click="mobileDrawerOpen = !mobileDrawerOpen" 
                class="w-9 h-9 rounded-full bg-white/5 active:bg-white/15 text-gray-300 hover:text-white flex items-center justify-center transition active:scale-90 touch-press cursor-pointer flex-shrink-0"
                :aria-label="$t('nav.menu')"
              >
                <template v-if="$page.props.auth?.user">
                  <img 
                    v-if="$page.props.auth.user.avatar" 
                    :src="$page.props.auth.user.avatar" 
                    class="w-6 h-6 rounded-full object-cover ring-1 ring-netflix" 
                    alt="" 
                  />
                  <div v-else class="w-6 h-6 rounded-full bg-netflix text-[10px] font-black text-white flex items-center justify-center">
                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                  </div>
                </template>
                <i v-else class="fas fa-bars text-base"></i>
              </button>
            </div>

            <!-- Desktop Login / Profile -->
            <div class="hidden lg:flex items-center space-x-2.5">
              <!-- Desktop Language Switcher -->
              <LanguageSwitcher variant="navbar" />

              <!-- In-App Notification Center Bell (Desktop) -->
              <div v-if="$page.props.auth?.user" class="relative">
                <button 
                  type="button"
                  @click="toggleNotifications"
                  class="relative w-9 h-9 rounded-full bg-white/10 hover:bg-white/15 text-gray-300 hover:text-white flex items-center justify-center transition border border-white/10 cursor-pointer"
                  :title="$t('nav.notifications')"
                >
                  <i class="fas fa-bell text-sm"></i>
                  <span 
                    v-if="unreadNotificationsCount > 0"
                    class="absolute -top-1 -right-1 min-w-4 h-4 px-1 rounded-full bg-netflix text-[10px] font-black text-white flex items-center justify-center shadow-lg animate-pulse"
                  >
                    {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
                  </span>
                </button>

                <!-- Notifications Dropdown -->
                <div 
                  v-if="notificationDropdownOpen"
                  class="absolute right-0 mt-2 w-80 sm:w-96 bg-gray-950 border border-white/15 rounded-2xl shadow-2xl z-50 backdrop-blur-2xl overflow-hidden divide-y divide-white/10 animate-fade-in"
                >
                  <div class="p-3.5 flex items-center justify-between bg-white/5">
                    <div class="flex items-center gap-2">
                      <i class="fas fa-bell text-netflix text-xs"></i>
                      <h4 class="text-xs font-bold text-white uppercase tracking-wider">{{ $t('nav.notifications') }}</h4>
                      <span v-if="unreadNotificationsCount > 0" class="px-1.5 py-0.5 rounded-full text-[10px] bg-netflix/20 text-netflix font-bold">
                        {{ unreadNotificationsCount }}
                      </span>
                    </div>
                    <button 
                      v-if="unreadNotificationsCount > 0"
                      @click="markAllNotificationsAsRead"
                      class="text-[11px] text-gray-400 hover:text-white transition cursor-pointer"
                    >
                      {{ $t('nav.mark_all_read') }}
                    </button>
                  </div>

                  <div class="max-h-80 overflow-y-auto divide-y divide-white/5">
                    <div v-if="loadingNotifications" class="py-8 text-center text-xs text-gray-500 flex items-center justify-center gap-2">
                      <i class="fas fa-spinner fa-spin text-netflix"></i> {{ $t('common.loading') }}
                    </div>
                    <div v-else-if="notifications.length === 0" class="py-8 text-center text-xs text-gray-500">
                      {{ $t('nav.no_notifications') }}
                    </div>
                    <div 
                      v-for="notif in notifications" 
                      :key="notif.id"
                      @click="handleNotificationClick(notif)"
                      :class="['p-3 flex items-start gap-3 transition cursor-pointer hover:bg-white/5', !notif.is_read ? 'bg-white/[0.04]' : 'opacity-70']"
                    >
                      <div 
                        class="w-7 h-7 rounded-xl flex items-center justify-center flex-shrink-0 text-xs"
                        :class="getNotifIconClass(notif.type)"
                      >
                        <i :class="getNotifIcon(notif.type)"></i>
                      </div>
                      <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                          <h5 class="text-xs font-bold text-white truncate">{{ notif.title }}</h5>
                          <span class="text-[10px] text-gray-500 whitespace-nowrap">{{ formatTimeAgo(notif.created_at) }}</span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-0.5 line-clamp-2 leading-tight">{{ notif.message }}</p>
                      </div>
                      <div v-if="!notif.is_read" class="w-2 h-2 rounded-full bg-netflix flex-shrink-0 self-center"></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Desktop User Dropdown -->
              <div v-if="$page.props.auth?.user" class="relative">
                <button 
                  @click="userMenuOpen = !userMenuOpen"
                  class="flex items-center gap-2.5 bg-white/10 hover:bg-white/15 px-3 py-1.5 rounded-full border border-white/10 transition-colors"
                >
                  <img 
                    v-if="$page.props.auth.user.avatar"
                    :src="$page.props.auth.user.avatar" 
                    :alt="$page.props.auth.user.name"
                    class="w-6 h-6 rounded-full object-cover ring-2 ring-netflix/50"
                  />
                  <div v-else class="w-6 h-6 rounded-full bg-netflix text-white text-xs flex items-center justify-center font-bold">
                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                  </div>
                  <span class="text-xs font-semibold text-white">{{ $page.props.auth.user.name }}</span>
                  <i class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform" :class="userMenuOpen ? 'rotate-180' : ''"></i>
                </button>

                <!-- Desktop User Dropdown Menu -->
                <div 
                  v-if="userMenuOpen" 
                  class="absolute right-0 mt-2 w-56 bg-black/95 border border-white/15 rounded-2xl shadow-2xl py-2 z-50 backdrop-blur-xl animate-fade-in divide-y divide-white/10"
                >
                  <!-- User Header -->
                  <div class="px-4 py-2.5">
                    <div class="flex items-center gap-2">
                      <p class="text-xs font-bold text-white truncate">{{ $page.props.auth.user.name }}</p>
                      <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-netflix text-white">VIP</span>
                    </div>
                    <p class="text-[11px] text-gray-400 truncate mt-0.5">{{ $page.props.auth.user.email }}</p>
                  </div>

                  <!-- Navigation Links -->
                  <!-- Navigation Links -->
                  <div class="py-1">
                    <Link 
                      v-if="$page.props.auth.user.role === 'admin'"
                      href="/admin" 
                      @click="userMenuOpen = false"
                      class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-red-400 hover:text-white hover:bg-netflix transition"
                    >
                      <i class="fas fa-shield-alt w-4 text-center"></i>
                      <span>{{ $t('nav.admin_panel') }}</span>
                    </Link>
                    <Link 
                      href="/dashboard" 
                      @click="userMenuOpen = false"
                      class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-300 hover:text-white hover:bg-white/10 transition"
                    >
                      <i class="fas fa-chart-pie w-4 text-center text-netflix"></i>
                      <span>{{ $t('nav.dashboard') }}</span>
                    </Link>
                    <Link 
                      href="/dashboard/watchlist" 
                      @click="userMenuOpen = false"
                      class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-300 hover:text-white hover:bg-white/10 transition"
                    >
                      <i class="fas fa-bookmark w-4 text-center text-netflix"></i>
                      <span>{{ $t('nav.watchlist') }}</span>
                    </Link>
                    <Link 
                      href="/dashboard/history" 
                      @click="userMenuOpen = false"
                      class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-300 hover:text-white hover:bg-white/10 transition"
                    >
                      <i class="fas fa-history w-4 text-center text-netflix"></i>
                      <span>{{ $t('nav.history') }}</span>
                    </Link>
                    <Link 
                      href="/dashboard/requests" 
                      @click="userMenuOpen = false"
                      class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-300 hover:text-white hover:bg-white/10 transition"
                    >
                      <i class="fas fa-film w-4 text-center text-netflix"></i>
                      <span>{{ $t('nav.requests') }}</span>
                    </Link>
                    <Link 
                      href="/dashboard/settings" 
                      @click="userMenuOpen = false"
                      class="flex items-center gap-2.5 px-4 py-2 text-xs text-gray-300 hover:text-white hover:bg-white/10 transition"
                    >
                      <i class="fas fa-sliders-h w-4 text-center text-netflix"></i>
                      <span>{{ $t('nav.settings') }}</span>
                    </Link>
                  </div>

                  <!-- Logout Button -->
                  <div class="pt-1">
                    <button 
                      @click="logout" 
                      class="w-full text-left flex items-center gap-2.5 px-4 py-2 text-xs text-red-400 hover:text-red-300 hover:bg-red-500/10 transition cursor-pointer"
                    >
                      <i class="fas fa-sign-out-alt w-4 text-center"></i>
                      <span>{{ $t('nav.logout') }}</span>
                    </button>
                  </div>
                </div>
              </div>

              <div v-else class="flex items-center space-x-3">
                <Link href="/login" class="text-gray-300 hover:text-white text-xs sm:text-sm font-medium transition-colors">
                  {{ $t('nav.login') }}
                </Link>
                <Link href="/register" class="bg-netflix hover:bg-netflix-dark px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-semibold transition-colors text-white shadow-md shadow-red-600/30">
                  {{ $t('nav.signup') }}
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Mobile In-App Notifications Modal / Bottom Sheet -->
    <div v-if="notificationDropdownOpen" class="fixed inset-0 z-50 lg:hidden flex items-end sm:items-center justify-center p-0 sm:p-4">
      <div class="fixed inset-0 bg-black/80 backdrop-blur-md" @click="notificationDropdownOpen = false"></div>
      <div class="relative w-full sm:max-w-md bg-[#0e1117] border-t sm:border border-white/15 rounded-t-3xl sm:rounded-2xl shadow-2xl z-10 overflow-hidden divide-y divide-white/10 max-h-[85vh] flex flex-col animate-slide-up pb-safe">
        <div class="p-4 flex items-center justify-between bg-white/5">
          <div class="flex items-center gap-2">
            <i class="fas fa-bell text-netflix text-sm"></i>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider">{{ $t('nav.notifications') }}</h4>
            <span v-if="unreadNotificationsCount > 0" class="px-2 py-0.5 rounded-full text-[10px] bg-netflix text-white font-black">
              {{ unreadNotificationsCount }}
            </span>
          </div>
          <div class="flex items-center gap-3">
            <button 
              v-if="unreadNotificationsCount > 0"
              @click="markAllNotificationsAsRead"
              class="text-xs text-gray-400 hover:text-white transition cursor-pointer"
            >
              {{ $t('nav.mark_all_read') }}
            </button>
            <button @click="notificationDropdownOpen = false" class="w-7 h-7 rounded-full bg-white/10 text-gray-400 hover:text-white flex items-center justify-center text-xs">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto divide-y divide-white/5 p-2 max-h-[60vh]">
          <div v-if="loadingNotifications" class="py-12 text-center text-xs text-gray-400 flex items-center justify-center gap-2">
            <i class="fas fa-spinner fa-spin text-netflix"></i> {{ $t('common.loading') }}
          </div>
          <div v-else-if="notifications.length === 0" class="py-12 text-center text-xs text-gray-500">
            {{ $t('nav.no_notifications') }}
          </div>
          <div 
            v-for="notif in notifications" 
            :key="notif.id"
            @click="handleNotificationClick(notif)"
            :class="['p-3 rounded-xl flex items-start gap-3 transition cursor-pointer hover:bg-white/5', !notif.is_read ? 'bg-white/[0.04]' : 'opacity-70']"
          >
            <div 
              class="w-7 h-7 rounded-xl flex items-center justify-center flex-shrink-0 text-xs"
              :class="getNotifIconClass(notif.type)"
            >
              <i :class="getNotifIcon(notif.type)"></i>
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center justify-between gap-2">
                <h5 class="text-xs font-bold text-white truncate">{{ notif.title }}</h5>
                <span class="text-[10px] text-gray-500 whitespace-nowrap">{{ formatTimeAgo(notif.created_at) }}</span>
              </div>
              <p class="text-[11px] text-gray-400 mt-0.5 line-clamp-2 leading-tight">{{ notif.message }}</p>
            </div>
            <div v-if="!notif.is_read" class="w-2 h-2 rounded-full bg-netflix flex-shrink-0 self-center"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Native App Drawer / Bottom Sheet -->
    <div v-if="mobileDrawerOpen" class="fixed inset-0 z-50 lg:hidden flex flex-col justify-end">
      <!-- Backdrop -->
      <div 
        class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity" 
        @click="mobileDrawerOpen = false"
      ></div>

      <!-- Sheet Container -->
      <div 
        class="relative w-full max-h-[88vh] bg-gradient-to-b from-[#161a22] to-[#0a0c10] border-t border-white/15 rounded-t-[28px] p-5 pb-dock z-10 shadow-2xl flex flex-col overflow-hidden animate-slide-up"
      >
        <!-- Drag Handle Pill -->
        <div class="w-12 h-1 bg-white/20 rounded-full mx-auto mb-3 cursor-pointer" @click="mobileDrawerOpen = false"></div>

        <!-- Header Row -->
        <div class="flex items-center justify-between pb-3 border-b border-white/10">
          <Link href="/" @click="mobileDrawerOpen = false" class="flex items-center">
            <img src="/images/logos/logo_movie.png?v=2" alt="Movie®" class="h-7 w-auto">
          </Link>
          <button 
            type="button" 
            @click="mobileDrawerOpen = false" 
            class="w-8 h-8 rounded-full bg-white/10 text-gray-300 hover:text-white flex items-center justify-center transition cursor-pointer"
          >
            <i class="fas fa-times text-sm"></i>
          </button>
        </div>

        <!-- Scrollable Sheet Content -->
        <div class="flex-1 overflow-y-auto no-scrollbar py-3 space-y-4">
          <!-- User Profile Card (Logged In) -->
          <div v-if="$page.props.auth?.user" class="p-3.5 rounded-2xl bg-white/[0.04] border border-white/10 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
              <img 
                v-if="$page.props.auth.user.avatar"
                :src="$page.props.auth.user.avatar"
                class="w-11 h-11 rounded-full object-cover ring-2 ring-netflix flex-shrink-0"
              />
              <div v-else class="w-11 h-11 rounded-full bg-netflix text-white font-bold text-sm flex items-center justify-center ring-2 ring-netflix/50 flex-shrink-0">
                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                  <p class="text-sm font-bold text-white truncate">{{ $page.props.auth.user.name }}</p>
                  <span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-netflix text-white">VIP</span>
                </div>
                <p class="text-[11px] text-gray-400 truncate">{{ $page.props.auth.user.email }}</p>
              </div>
            </div>
            <Link 
              href="/dashboard/settings" 
              @click="mobileDrawerOpen = false"
              class="w-8 h-8 rounded-full bg-white/10 text-gray-300 hover:text-white flex items-center justify-center transition flex-shrink-0"
              :title="$t('nav.settings')"
            >
              <i class="fas fa-cog text-xs"></i>
            </Link>
          </div>

          <!-- Guest Welcome Card (Logged Out) -->
          <div v-else class="p-3.5 rounded-2xl bg-gradient-to-r from-netflix/20 via-netflix/10 to-transparent border border-netflix/30">
            <p class="text-xs font-bold text-white">{{ $t('footer.brand_desc') || 'Sınırsız film, dizi ve canlı yayın keyfi.' }}</p>
            <div class="grid grid-cols-2 gap-2 mt-3">
              <Link 
                href="/login" 
                @click="mobileDrawerOpen = false"
                class="py-2 text-center rounded-xl bg-netflix text-white text-xs font-bold shadow-md shadow-red-900/30 active:scale-95 transition"
              >
                {{ $t('nav.login') }}
              </Link>
              <Link 
                href="/register" 
                @click="mobileDrawerOpen = false"
                class="py-2 text-center rounded-xl bg-white/10 text-white text-xs font-semibold hover:bg-white/15 active:scale-95 transition"
              >
                {{ $t('nav.signup') }}
              </Link>
            </div>
          </div>

          <!-- App Launcher Grid -->
          <div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1 mb-2">{{ $t('footer.explore') }}</p>
            <div class="grid grid-cols-4 gap-2">
              <Link 
                v-for="item in navItems" 
                :key="item.name" 
                :href="item.href"
                @click="mobileDrawerOpen = false"
                class="flex flex-col items-center justify-center p-2.5 rounded-xl bg-white/[0.03] hover:bg-white/[0.08] border border-white/5 hover:border-white/10 active:scale-95 transition text-center group"
              >
                <div class="w-8 h-8 rounded-lg bg-netflix/15 group-hover:bg-netflix/25 flex items-center justify-center mb-1 text-netflix text-sm transition">
                  <i :class="['fas', item.icon]"></i>
                </div>
                <span class="text-[11px] font-semibold text-gray-300 group-hover:text-white truncate w-full">{{ item.name }}</span>
              </Link>
            </div>
          </div>

          <!-- Account & Activity Shortcuts (Logged In) -->
          <div v-if="$page.props.auth?.user" class="pt-2 border-t border-white/10">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1 mb-2">{{ $t('nav.my_account') }}</p>
            <div class="grid grid-cols-2 gap-2">
              <Link 
                v-if="$page.props.auth?.user?.role === 'admin'"
                href="/admin"
                @click="mobileDrawerOpen = false"
                class="flex items-center gap-2.5 p-2.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-bold hover:bg-netflix hover:text-white transition active:scale-95 col-span-2"
              >
                <i class="fas fa-shield-alt w-4 text-center"></i>
                <span>{{ $t('nav.admin_panel') }}</span>
              </Link>
              <Link 
                href="/dashboard"
                @click="mobileDrawerOpen = false"
                class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white/[0.03] border border-white/5 text-gray-300 hover:text-white text-xs font-semibold hover:bg-white/[0.08] transition active:scale-95"
              >
                <i class="fas fa-chart-pie w-4 text-center text-netflix"></i>
                <span>{{ $t('nav.dashboard') }}</span>
              </Link>
              <Link 
                href="/dashboard/watchlist"
                @click="mobileDrawerOpen = false"
                class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white/[0.03] border border-white/5 text-gray-300 hover:text-white text-xs font-semibold hover:bg-white/[0.08] transition active:scale-95"
              >
                <i class="fas fa-bookmark w-4 text-center text-netflix"></i>
                <span>{{ $t('nav.watchlist') }}</span>
              </Link>
              <Link 
                href="/dashboard/history"
                @click="mobileDrawerOpen = false"
                class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white/[0.03] border border-white/5 text-gray-300 hover:text-white text-xs font-semibold hover:bg-white/[0.08] transition active:scale-95"
              >
                <i class="fas fa-history w-4 text-center text-netflix"></i>
                <span>{{ $t('nav.history') }}</span>
              </Link>
              <Link 
                href="/dashboard/requests"
                @click="mobileDrawerOpen = false"
                class="flex items-center gap-2.5 p-2.5 rounded-xl bg-white/[0.03] border border-white/5 text-gray-300 hover:text-white text-xs font-semibold hover:bg-white/[0.08] transition active:scale-95"
              >
                <i class="fas fa-film w-4 text-center text-netflix"></i>
                <span>{{ $t('nav.requests') }}</span>
              </Link>
            </div>
          </div>

          <!-- Language Preferences -->
          <div class="pt-2 border-t border-white/10 flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-300">{{ $t('common.language') }}</span>
            <LanguageSwitcher variant="footer" />
          </div>

          <!-- Logout Button (Logged In) -->
          <div v-if="$page.props.auth?.user" class="pt-2">
            <button 
              @click="logout" 
              class="w-full py-2.5 px-4 rounded-xl bg-red-500/15 border border-red-500/25 text-red-400 text-xs font-bold hover:bg-red-500/25 transition active:scale-95 flex items-center justify-center gap-2 cursor-pointer"
            >
              <i class="fas fa-sign-out-alt"></i>
              <span>{{ $t('nav.logout') }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Page Body with Bottom Dock Clearance -->
    <main class="min-h-[70vh] pb-24 lg:pb-0">
      <slot />
    </main>

    <!-- Mobile Bottom Dock Navigation Bar (#mobile-bottom-nav) -->
    <div id="mobile-bottom-nav" class="lg:hidden">
      <div class="mobile-bottom-nav-inner">
        <Link 
          href="/" 
          :class="['mobile-bottom-nav-item', $page.url === '/' ? 'is-active' : '']"
        >
          <i class="fas fa-home"></i>
          <span>{{ $t('nav.home') }}</span>
        </Link>

        <Link 
          href="/trending" 
          :class="['mobile-bottom-nav-item', $page.url.startsWith('/trending') ? 'is-active' : '']"
        >
          <i class="fas fa-fire"></i>
          <span>{{ $t('nav.trending') }}</span>
        </Link>

        <!-- Center Search Button -->
        <button 
          type="button"
          @click="showMegaSearch = true" 
          class="mobile-bottom-nav-search cursor-pointer"
          :aria-label="$t('search.title')"
        >
          <i class="fas fa-search"></i>
          <span class="sr-only">{{ $t('search.title') }}</span>
        </button>

        <Link 
          href="/movies" 
          :class="['mobile-bottom-nav-item', $page.url.startsWith('/movies') || $page.url.startsWith('/movie') ? 'is-active' : '']"
        >
          <i class="fas fa-film"></i>
          <span>{{ $t('nav.movies') }}</span>
        </Link>

        <button 
          type="button"
          @click="mobileDrawerOpen = !mobileDrawerOpen" 
          :class="['mobile-bottom-nav-item cursor-pointer', mobileDrawerOpen ? 'is-active' : '']"
          :aria-label="$page.props.auth?.user ? $t('nav.my_account') : $t('nav.menu')"
        >
          <template v-if="$page.props.auth?.user">
            <img 
              v-if="$page.props.auth.user.avatar" 
              :src="$page.props.auth.user.avatar" 
              class="w-5 h-5 rounded-full object-cover ring-1 ring-white/30" 
              alt="" 
            />
            <div v-else class="w-5 h-5 rounded-full bg-netflix text-[9px] font-black text-white flex items-center justify-center">
              {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
            </div>
            <span>{{ $t('nav.my_account') }}</span>
          </template>
          <template v-else>
            <i class="fas fa-bars"></i>
            <span>{{ $t('nav.menu') }}</span>
          </template>
        </button>
      </div>
    </div>

    <!-- Original Movie® Footer (site-footer) -->
    <footer class="site-footer relative z-10 mt-16">
      <div class="site-footer-inner">
        <div class="site-footer-grid">
          <!-- Brand Column -->
          <section class="site-footer-brand" aria-label="Movie®">
            <img src="/images/logos/logo_movie.png?v=2" alt="Movie®" class="site-footer-logo">
            <p>{{ $t('footer.brand_desc') }}</p>
            <img src="/images/footer-cinema-chair.webp" alt="" class="site-footer-cinema" aria-hidden="true">
          </section>

          <!-- Links: Explore -->
          <nav class="site-footer-links" :aria-label="$t('footer.explore')">
            <h3>{{ $t('footer.explore') }}</h3>
            <Link href="/movies">{{ $t('nav.movies') }}</Link>
            <Link href="/tv-shows">{{ $t('nav.tv_shows') }}</Link>
            <Link href="/trending">{{ $t('nav.trending') }}</Link>
            <Link href="/anime">{{ $t('nav.anime') }}</Link>
            <Link href="/sports">{{ $t('nav.sports') }}</Link>
          </nav>

          <!-- Links: Support -->
          <nav class="site-footer-links" :aria-label="$t('footer.support')">
            <h3>{{ $t('footer.support') }}</h3>
            <Link href="/my-requests">{{ $t('footer.request_content') }}</Link>
            <Link href="/contact">{{ $t('footer.contact_us') }}</Link>
            <Link href="/terms">{{ $t('footer.terms') }}</Link>
            <Link href="/privacy">{{ $t('footer.privacy') }}</Link>
            <Link href="/contact">{{ $t('footer.help_center') }}</Link>
          </nav>

          <!-- Newsletter & Socials -->
          <section class="site-footer-newsletter">
            <h3><i class="far fa-envelope" aria-hidden="true"></i><span>{{ $t('footer.newsletter_title') }}</span></h3>
            <p>{{ $t('footer.newsletter_desc') }}</p>
            <form @submit.prevent="newsletterSubmitted = true" class="site-footer-newsletter-form">
              <label class="sr-only" for="footer-email">{{ $t('footer.newsletter_placeholder') }}</label>
              <input id="footer-email" type="email" :placeholder="$t('footer.newsletter_placeholder')" required>
              <button type="submit" aria-label="Subscribe" style="background: #e50914;" class="cursor-pointer">
                <i class="fas fa-chevron-right"></i>
              </button>
            </form>
            <small v-if="newsletterSubmitted" class="text-green-400 font-bold block mt-1">{{ $t('footer.newsletter_thanks') }}</small>
            <small v-else>{{ $t('footer.newsletter_disclaimer') }}</small>

            <div class="site-footer-socials">
              <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
              <a href="https://x.com/" target="_blank" rel="noopener noreferrer" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
              <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
              <a href="https://youtube.com/" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
          </section>
        </div>

        <div class="site-footer-bottom flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-white/10 mt-8">
          <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4 text-xs text-gray-400">
            <p>{{ $t('footer.copyright') }}</p>
            <span class="hidden sm:inline text-gray-600">•</span>
            <a href="https://erdemeroglu.com.tr/yazilimlar/movie" target="_blank" rel="noopener noreferrer" class="hover:text-netflix transition-colors">
              Movie® by Erdem Eroğlu
            </a>
          </div>
          <div class="flex items-center gap-4">
            <LanguageSwitcher variant="footer" />
            <p class="site-footer-made hidden sm:block">{{ $t('footer.made_with') }} <i class="far fa-heart text-netflix" aria-hidden="true"></i></p>
          </div>
        </div>
      </div>
    </footer>

    <!-- PWA Install Floating Banner -->
    <div 
      v-if="deferredPrompt && showPwaBanner" 
      class="fixed bottom-20 lg:bottom-4 left-4 right-4 sm:left-auto sm:right-6 sm:max-w-md z-40 bg-gray-950/95 border border-white/20 rounded-2xl p-4 shadow-2xl backdrop-blur-xl flex items-center justify-between gap-4"
    >
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-10 h-10 rounded-xl bg-netflix flex items-center justify-center text-white font-black text-base shadow-lg flex-shrink-0">
          M
        </div>
        <div class="min-w-0">
          <h4 class="text-xs font-bold text-white truncate">{{ $t('common.install_app') }}</h4>
          <p class="text-[11px] text-gray-400 truncate">{{ $t('common.install_desc') }}</p>
        </div>
      </div>
      <div class="flex items-center gap-2 flex-shrink-0">
        <button 
          @click="installPwa" 
          class="px-3 py-1.5 rounded-xl bg-netflix hover:bg-red-700 text-white text-xs font-bold transition shadow-lg cursor-pointer"
        >
          {{ $t('common.install') }}
        </button>
        <button 
          @click="showPwaBanner = false" 
          class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 text-gray-400 hover:text-white flex items-center justify-center text-xs transition cursor-pointer"
          :title="$t('common.dismiss')"
        >
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    <!-- Global Mega Search Modal -->
    <MegaSearchModal 
      :is-open="showMegaSearch" 
      @close="showMegaSearch = false" 
    />

    <!-- Global AdBlock Detector Modal & Warning Bar -->
    <AdBlockModal />

    <!-- Global Floating Sticky Footer Ad Unit -->
    <StickyFooterAd />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import MegaSearchModal from '@/Components/MegaSearchModal.vue';
import AdBlockModal from '@/Components/AdBlockModal.vue';
import StickyFooterAd from '@/Components/StickyFooterAd.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useI18n } from '@/Composables/useI18n';

const page = usePage();
const { t } = useI18n();

const showMegaSearch = ref(false);
const searchQuery = ref('');
const searchResults = ref([]);
const showSearchDropdown = ref(false);
const moreDropdownOpen = ref(false);
const userMenuOpen = ref(false);
const mobileDrawerOpen = ref(false);
const newsletterSubmitted = ref(false);

// In-App Notifications
const notifications = ref([]);
const unreadNotificationsCount = ref(0);
const notificationDropdownOpen = ref(false);
const loadingNotifications = ref(false);

const fetchNotifications = async () => {
  if (!page.props.auth?.user) return;
  try {
    const res = await axios.get('/api/notifications');
    notifications.value = res.data.notifications || [];
    unreadNotificationsCount.value = res.data.unread_count || 0;
  } catch (err) {
    console.error('Error fetching notifications:', err);
  }
};

const toggleNotifications = async () => {
  notificationDropdownOpen.value = !notificationDropdownOpen.value;
  if (notificationDropdownOpen.value) {
    loadingNotifications.value = true;
    await fetchNotifications();
    loadingNotifications.value = false;
  }
};

const markAllNotificationsAsRead = async () => {
  try {
    await axios.post('/api/notifications/read-all');
    unreadNotificationsCount.value = 0;
    notifications.value.forEach((n) => { n.is_read = true; });
  } catch (err) {
    console.error(err);
  }
};

const handleNotificationClick = async (notif) => {
  if (!notif.is_read) {
    try {
      await axios.post(`/api/notifications/${notif.id}/read`);
      notif.is_read = true;
      if (unreadNotificationsCount.value > 0) {
        unreadNotificationsCount.value--;
      }
    } catch (err) {
      console.error(err);
    }
  }
  notificationDropdownOpen.value = false;
  if (notif.link) {
    router.visit(notif.link);
  }
};

const getNotifIcon = (type) => {
  switch (type) {
    case 'review': return 'fas fa-star';
    case 'like': return 'fas fa-heart';
    case 'content_ready': return 'fas fa-play';
    case 'system': return 'fas fa-bell';
    default: return 'fas fa-info-circle';
  }
};

const getNotifIconClass = (type) => {
  switch (type) {
    case 'review': return 'bg-amber-500/20 text-amber-400';
    case 'like': return 'bg-rose-500/20 text-rose-400';
    case 'content_ready': return 'bg-emerald-500/20 text-emerald-400';
    case 'system': return 'bg-blue-500/20 text-blue-400';
    default: return 'bg-netflix/20 text-netflix';
  }
};

const formatTimeAgo = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  const now = new Date();
  const diffInSec = Math.floor((now - date) / 1000);
  if (diffInSec < 60) return 'just now';
  const diffInMin = Math.floor(diffInSec / 60);
  if (diffInMin < 60) return `${diffInMin}m ago`;
  const diffInHours = Math.floor(diffInMin / 60);
  if (diffInHours < 24) return `${diffInHours}h ago`;
  const diffInDays = Math.floor(diffInHours / 24);
  return `${diffInDays}d ago`;
};

// PWA Installation
const deferredPrompt = ref(null);
const showPwaBanner = ref(true);

const handleBeforeInstallPrompt = (e) => {
  e.preventDefault();
  deferredPrompt.value = e;
};

const installPwa = async () => {
  if (!deferredPrompt.value) return;
  deferredPrompt.value.prompt();
  const { outcome } = await deferredPrompt.value.userChoice;
  if (outcome === 'accepted') {
    showPwaBanner.value = false;
  }
  deferredPrompt.value = null;
};

onMounted(() => {
  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
  fetchNotifications();
});

onUnmounted(() => {
  window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
});

let searchTimeout = null;

const navItems = computed(() => [
  { name: t('nav.home'), href: '/', icon: 'fa-home' },
  { name: t('nav.trending'), href: '/trending', icon: 'fa-fire' },
  { name: t('nav.movies'), href: '/movies', icon: 'fa-film' },
  { name: t('nav.tv_shows'), href: '/tv-shows', icon: 'fa-tv' },
  { name: t('nav.anime'), href: '/anime', icon: 'fa-torii-gate' },
  { name: t('nav.sports'), href: '/sports', icon: 'fa-futbol' },
  { name: t('nav.my_list'), href: '/watchlist', icon: 'fa-bookmark' },
  { name: t('nav.collections'), href: '/lists', icon: 'fa-layer-group' },
]);

const handleLiveSearch = () => {
  clearTimeout(searchTimeout);
  if (searchQuery.value.trim().length < 2) {
    searchResults.value = [];
    showSearchDropdown.value = false;
    return;
  }

  searchTimeout = setTimeout(async () => {
    try {
      const res = await axios.get(`/api/search/live?q=${encodeURIComponent(searchQuery.value.trim())}`);
      searchResults.value = res.data.results || [];
      showSearchDropdown.value = true;
    } catch (e) {
      console.error(e);
    }
  }, 200);
};

const submitSearch = () => {
  if (searchQuery.value.trim()) {
    showSearchDropdown.value = false;
    router.visit(`/search?q=${encodeURIComponent(searchQuery.value.trim())}`);
  }
};

const logout = () => {
  router.post('/logout');
};
</script>
