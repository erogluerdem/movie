<template>
  <AdminLayout>
    <div class="space-y-8">
      <!-- Page Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2.5">
              <i class="fas fa-robot text-indigo-400"></i>
              <span>TMDB Bot & Senkronizasyon Merkezi</span>
            </h1>
            <span 
              :class="tmdb_config.is_configured ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-red-500/20 text-red-400 border-red-500/30'"
              class="px-2.5 py-1 rounded-full text-xs font-bold border flex items-center gap-1.5"
            >
              <span class="w-2 h-2 rounded-full" :class="tmdb_config.is_configured ? 'bg-emerald-400 animate-pulse' : 'bg-red-400'"></span>
              <span>{{ tmdb_config.is_configured ? 'API Bağlandı' : 'API Bağlantısı Yok' }}</span>
            </span>
          </div>
          <p class="text-xs sm:text-sm text-gray-400 mt-1">
            The Movie Database (TMDB) API üzerinden film, dizi, anime, oyuncu kadroları ve YouTube fragmanlarını otomatik veya tek tıkla çekin.
          </p>
        </div>

        <!-- Quick Status Pill -->
        <div class="flex items-center gap-2">
          <div class="px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs flex items-center gap-2 text-gray-300">
            <i class="fas fa-clock text-indigo-400"></i>
            <span>Son Senkron: <strong>{{ automation.last_sync_at || 'Henüz Yapılmadı' }}</strong></span>
          </div>
          <button 
            @click="triggerCuratedSync" 
            :disabled="syncing"
            class="px-4 py-2 rounded-xl bg-gradient-to-r from-red-600 to-indigo-600 hover:from-red-500 hover:to-indigo-500 text-white text-xs font-black transition flex items-center gap-2 shadow-lg shadow-indigo-600/20 disabled:opacity-50"
          >
            <i :class="syncing ? 'fa-spinner fa-spin' : 'fa-bolt'" class="fas"></i>
            <span>Hızlı Küratörlü Çekim</span>
          </button>
        </div>
      </div>

      <!-- Live Sync Status Banner (if running or last result) -->
      <div 
        v-if="automation.last_sync_status && automation.last_sync_status !== 'idle'"
        :class="[
          automation.last_sync_status === 'running' ? 'bg-indigo-500/10 border-indigo-500/30 text-indigo-300' :
          automation.last_sync_status === 'success' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' :
          'bg-rose-500/10 border-rose-500/30 text-rose-300'
        ]"
        class="p-4 rounded-2xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs"
      >
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-xl flex items-center justify-center bg-white/10 flex-shrink-0 text-sm">
            <i :class="[
              automation.last_sync_status === 'running' ? 'fa-sync fa-spin text-indigo-400' :
              automation.last_sync_status === 'success' ? 'fa-check-circle text-emerald-400' :
              'fa-exclamation-triangle text-rose-400'
            ]" class="fas"></i>
          </div>
          <div>
            <div class="font-bold uppercase tracking-wider text-[11px]">
              Senkronizasyon Durumu: {{ automation.last_sync_status }}
            </div>
            <div class="text-gray-300 mt-0.5">{{ automation.last_sync_message }}</div>
          </div>
        </div>
        <div class="text-[11px] text-gray-400 font-mono">
          {{ automation.last_sync_at }}
        </div>
      </div>

      <!-- Stats Overview Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-gray-900/60 border border-white/10 hover:border-white/20 transition relative overflow-hidden group">
          <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-bold uppercase tracking-wider">Kayıtlı Filmler</span>
            <i class="fas fa-film text-indigo-400"></i>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-white">{{ stats.total_movies }}</div>
          <div class="text-[11px] text-emerald-400 flex items-center gap-1 mt-2">
            <i class="fab fa-youtube text-red-500"></i>
            <span>{{ stats.movies_with_trailers }} fragman aktif</span>
          </div>
          <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-indigo-500/5 rounded-full blur-xl group-hover:bg-indigo-500/10 transition"></div>
        </div>

        <div class="p-5 rounded-2xl bg-gray-900/60 border border-white/10 hover:border-white/20 transition relative overflow-hidden group">
          <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-bold uppercase tracking-wider">TV Dizileri</span>
            <i class="fas fa-tv text-purple-400"></i>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-white">{{ stats.total_tv }}</div>
          <div class="text-[11px] text-purple-400 flex items-center gap-1 mt-2">
            <i class="fas fa-layer-group"></i>
            <span>Sezonlar ve Bölümler Dahil</span>
          </div>
          <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-purple-500/5 rounded-full blur-xl group-hover:bg-purple-500/10 transition"></div>
        </div>

        <div class="p-5 rounded-2xl bg-gray-900/60 border border-white/10 hover:border-white/20 transition relative overflow-hidden group">
          <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-bold uppercase tracking-wider">Anime Serileri</span>
            <i class="fas fa-torii-gate text-rose-400"></i>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-white">{{ stats.total_anime }}</div>
          <div class="text-[11px] text-rose-400 flex items-center gap-1 mt-2">
            <i class="fas fa-tags"></i>
            <span>/anime sekmesinde yayında</span>
          </div>
          <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-rose-500/5 rounded-full blur-xl group-hover:bg-rose-500/10 transition"></div>
        </div>

        <div class="p-5 rounded-2xl bg-gray-900/60 border border-white/10 hover:border-white/20 transition relative overflow-hidden group">
          <div class="flex items-center justify-between text-gray-400 mb-2">
            <span class="text-xs font-bold uppercase tracking-wider">Genel Kütüphane</span>
            <i class="fas fa-database text-amber-400"></i>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-white">{{ stats.total_movies + stats.total_tv + stats.total_anime }}</div>
          <div class="text-[11px] text-amber-400 flex items-center gap-1 mt-2">
            <i class="fas fa-check-double"></i>
            <span>TMDB CDN & Fragman Entegre</span>
          </div>
          <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-amber-500/5 rounded-full blur-xl group-hover:bg-amber-500/10 transition"></div>
        </div>
      </div>

      <!-- Quick Action Buttons: Tek Tıkla Toplu Çekim Paneli -->
      <div class="p-6 rounded-3xl bg-gray-950/80 border border-white/10 space-y-6 backdrop-blur-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-white/10 pb-4">
          <div>
            <h2 class="text-base font-black text-white flex items-center gap-2">
              <i class="fas fa-bolt text-amber-400"></i>
              <span>Tek Tıkla Toplu Senkronizasyon (One-Click Bulk Sync)</span>
            </h2>
            <p class="text-xs text-gray-400 mt-0.5">
              İstediğiniz kategoriye tıklayarak TMDB'den yüksek puanlı ve afişli yapımları tek seferde platforma ekleyin.
            </p>
          </div>
          <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 text-xs text-gray-300 cursor-pointer select-none">
              <input type="checkbox" v-model="syncInBackground" class="rounded bg-white/10 border-white/20 text-indigo-600 focus:ring-0" />
              <span>Arka Planda Çalıştır (Kuyruk / Job)</span>
            </label>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Curated Mega Sync -->
          <div class="p-4 rounded-2xl bg-gradient-to-br from-indigo-950/60 to-purple-950/40 border border-indigo-500/30 hover:border-indigo-500/60 transition flex flex-col justify-between group">
            <div class="space-y-2">
              <div class="w-10 h-10 rounded-xl bg-indigo-600/30 text-indigo-400 flex items-center justify-center text-lg">
                <i class="fas fa-crown"></i>
              </div>
              <div class="font-black text-white text-sm">Küratörlü Mega Paket</div>
              <p class="text-[11px] text-gray-400">
                Popüler filmler, başyapıt klasikler, vizyondaki yapımlar, diziler ve animeleri tek seferde çeker.
              </p>
            </div>
            <button 
              @click="runSync('curated')" 
              :disabled="syncing"
              class="mt-4 w-full py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <i :class="activeSyncMode === 'curated' ? 'fa-spinner fa-spin' : 'fa-download'" class="fas"></i>
              <span>Mega Paketi Çek</span>
            </button>
          </div>

          <!-- Top Rated Classics -->
          <div class="p-4 rounded-2xl bg-gray-900/60 border border-white/10 hover:border-white/20 transition flex flex-col justify-between group">
            <div class="space-y-2">
              <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-lg">
                <i class="fas fa-star"></i>
              </div>
              <div class="font-black text-white text-sm">En Yüksek Puanlı Klasikler</div>
              <p class="text-[11px] text-gray-400">
                Godfather, Pulp Fiction, Shawshank Redemption, Interstellar gibi sinema tarihinin en iyi yapımları.
              </p>
            </div>
            <button 
              @click="runSync('top_rated_movies', { pages: 2, min_votes: 100 })" 
              :disabled="syncing"
              class="mt-4 w-full py-2 px-3 rounded-xl bg-white/10 hover:bg-amber-600 hover:text-black text-gray-200 font-bold text-xs transition flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <i :class="activeSyncMode === 'top_rated_movies' ? 'fa-spinner fa-spin' : 'fa-download'" class="fas"></i>
              <span>Klasikleri Çek (40 Film)</span>
            </button>
          </div>

          <!-- Trending Movies -->
          <div class="p-4 rounded-2xl bg-gray-900/60 border border-white/10 hover:border-white/20 transition flex flex-col justify-between group">
            <div class="space-y-2">
              <div class="w-10 h-10 rounded-xl bg-red-500/20 text-red-400 flex items-center justify-center text-lg">
                <i class="fas fa-fire"></i>
              </div>
              <div class="font-black text-white text-sm">Haftanın Trend Filmleri</div>
              <p class="text-[11px] text-gray-400">
                Şu anda dünya çapında en çok konuşulan ve izlenen sinema yapımlarını çeker.
              </p>
            </div>
            <button 
              @click="runSync('trending_movies', { pages: 2, min_votes: 40 })" 
              :disabled="syncing"
              class="mt-4 w-full py-2 px-3 rounded-xl bg-white/10 hover:bg-red-600 text-gray-200 font-bold text-xs transition flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <i :class="activeSyncMode === 'trending_movies' ? 'fa-spinner fa-spin' : 'fa-download'" class="fas"></i>
              <span>Trendleri Çek (40 Film)</span>
            </button>
          </div>

          <!-- Popular Anime -->
          <div class="p-4 rounded-2xl bg-gray-900/60 border border-white/10 hover:border-white/20 transition flex flex-col justify-between group">
            <div class="space-y-2">
              <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-lg">
                <i class="fas fa-torii-gate"></i>
              </div>
              <div class="font-black text-white text-sm">Top Anime Serileri</div>
              <p class="text-[11px] text-gray-400">
                Attack on Titan, One Piece, Demon Slayer, Solo Leveling gibi efsanevi animeleri ve sezonlarını ekler.
              </p>
            </div>
            <button 
              @click="runSync('anime', { pages: 2, min_votes: 30 })" 
              :disabled="syncing"
              class="mt-4 w-full py-2 px-3 rounded-xl bg-white/10 hover:bg-rose-600 text-gray-200 font-bold text-xs transition flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <i :class="activeSyncMode === 'anime' ? 'fa-spinner fa-spin' : 'fa-download'" class="fas"></i>
              <span>Animeleri Çek (40 Dizi)</span>
            </button>
          </div>

          <!-- Popular TV Shows -->
          <div class="p-4 rounded-2xl bg-gray-900/60 border border-white/10 hover:border-white/20 transition flex flex-col justify-between group">
            <div class="space-y-2">
              <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg">
                <i class="fas fa-tv"></i>
              </div>
              <div class="font-black text-white text-sm">Popüler & Top Diziler</div>
              <p class="text-[11px] text-gray-400">
                Breaking Bad, Silo, Reacher, Chernobyl, Game of Thrones vb. TV dizilerini tüm sezonlarıyla çeker.
              </p>
            </div>
            <button 
              @click="runSync('popular_tv', { pages: 2, min_votes: 40 })" 
              :disabled="syncing"
              class="mt-4 w-full py-2 px-3 rounded-xl bg-white/10 hover:bg-emerald-600 text-gray-200 font-bold text-xs transition flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <i :class="activeSyncMode === 'popular_tv' ? 'fa-spinner fa-spin' : 'fa-download'" class="fas"></i>
              <span>Dizileri Çek (40 Dizi)</span>
            </button>
          </div>

          <!-- Upcoming Movies -->
          <div class="p-4 rounded-2xl bg-gray-900/60 border border-white/10 hover:border-white/20 transition flex flex-col justify-between group">
            <div class="space-y-2">
              <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-lg">
                <i class="fas fa-calendar-alt"></i>
              </div>
              <div class="font-black text-white text-sm">Yakında Vizyona Girecekler</div>
              <p class="text-[11px] text-gray-400">
                Yakında vizyona girecek filmleri, fragmanları ve afişleriyle beraber keşif için ekler.
              </p>
            </div>
            <button 
              @click="runSync('upcoming_movies', { pages: 2, min_votes: 20 })" 
              :disabled="syncing"
              class="mt-4 w-full py-2 px-3 rounded-xl bg-white/10 hover:bg-blue-600 text-gray-200 font-bold text-xs transition flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <i :class="activeSyncMode === 'upcoming_movies' ? 'fa-spinner fa-spin' : 'fa-download'" class="fas"></i>
              <span>Vizyondakileri Çek</span>
            </button>
          </div>

          <!-- Genre Filter Sync (Spans 2 columns) -->
          <div class="p-4 rounded-2xl bg-gray-900/60 border border-white/10 hover:border-white/20 transition flex flex-col justify-between group sm:col-span-2">
            <div class="space-y-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-lg">
                  <i class="fas fa-masks-theater"></i>
                </div>
                <div>
                  <div class="font-black text-white text-sm">Türe Göre Özel Çekim</div>
                  <p class="text-[11px] text-gray-400">Seçtiğiniz türe ait en popüler filmleri içeri aktarın.</p>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-2 pt-1">
                <select v-model="selectedGenre" class="bg-black/50 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:ring-1 focus:ring-cyan-500 focus:outline-none">
                  <option value="scifi">🚀 Bilim Kurgu (Sci-Fi)</option>
                  <option value="action">💥 Aksiyon (Action)</option>
                  <option value="horror">👻 Korku (Horror)</option>
                  <option value="comedy">😂 Komedi (Comedy)</option>
                  <option value="drama">🎭 Dram (Drama)</option>
                  <option value="thriller">🔪 Gerilim (Thriller)</option>
                  <option value="animation">🎨 Animasyon (Animation)</option>
                  <option value="crime">🕵️ Suç (Crime)</option>
                  <option value="adventure">🗺️ Macera (Adventure)</option>
                  <option value="fantasy">🧙 Fantastik (Fantasy)</option>
                </select>

                <select v-model="genrePages" class="bg-black/50 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:ring-1 focus:ring-cyan-500 focus:outline-none">
                  <option :value="1">1 Sayfa (20 Film)</option>
                  <option :value="2">2 Sayfa (40 Film)</option>
                  <option :value="3">3 Sayfa (60 Film)</option>
                  <option :value="5">5 Sayfa (100 Film)</option>
                </select>
              </div>
            </div>

            <button 
              @click="runSync('genre', { genre: selectedGenre, pages: genrePages, min_votes: 50 })" 
              :disabled="syncing"
              class="mt-4 w-full py-2 px-3 rounded-xl bg-white/10 hover:bg-cyan-600 text-gray-200 font-bold text-xs transition flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <i :class="activeSyncMode === 'genre' ? 'fa-spinner fa-spin' : 'fa-filter'" class="fas"></i>
              <span>Seçili Türü Çek ({{ selectedGenre.toUpperCase() }})</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Live TMDB Search & Instant Import (Canlı Arama & Tekil Ekleme) -->
      <div class="p-6 rounded-3xl bg-gray-950/80 border border-white/10 space-y-5 backdrop-blur-xl">
        <div class="border-b border-white/10 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h2 class="text-base font-black text-white flex items-center gap-2">
              <i class="fas fa-search text-cyan-400"></i>
              <span>Canlı TMDB Araması & Anında İçe Aktarma</span>
            </h2>
            <p class="text-xs text-gray-400 mt-0.5">
              İstediğiniz bir filmi veya diziyi başlığıyla arayın; fragmanı, afişi ve oyuncu kadrosuyla anında platforma ekleyin.
            </p>
          </div>
          <div class="flex items-center gap-2 bg-white/5 p-1 rounded-xl border border-white/10">
            <button 
              @click="searchMediaType = 'movie'" 
              :class="searchMediaType === 'movie' ? 'bg-netflix text-white' : 'text-gray-400 hover:text-white'"
              class="px-3 py-1 rounded-lg text-xs font-bold transition"
            >
              Film
            </button>
            <button 
              @click="searchMediaType = 'tv'" 
              :class="searchMediaType === 'tv' ? 'bg-netflix text-white' : 'text-gray-400 hover:text-white'"
              class="px-3 py-1 rounded-lg text-xs font-bold transition"
            >
              Dizi / Anime
            </button>
          </div>
        </div>

        <div class="flex gap-2">
          <div class="relative flex-1">
            <i class="fas fa-search absolute left-4 top-3.5 text-gray-500 text-sm"></i>
            <input 
              v-model="searchQuery" 
              @keydown.enter.prevent="executeSearch"
              type="text" 
              :placeholder="searchMediaType === 'movie' ? 'Örn: Gladiator, Dune, Inception...' : 'Örn: Breaking Bad, Stranger Things, Naruto...'" 
              class="w-full pl-11 pr-4 py-3 rounded-2xl bg-black/50 border border-white/10 text-white placeholder-gray-500 text-sm focus:outline-none focus:border-indigo-500 transition"
            />
          </div>
          <button 
            @click="executeSearch" 
            :disabled="searching || searchQuery.trim().length < 2"
            class="px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition flex items-center gap-2 disabled:opacity-50"
          >
            <i :class="searching ? 'fa-spinner fa-spin' : 'fa-search'" class="fas"></i>
            <span>Ara</span>
          </button>
        </div>

        <!-- Search Results Grid -->
        <div v-if="searchResults.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-8 gap-3 pt-2">
          <div 
            v-for="item in searchResults" 
            :key="item.id"
            class="p-2 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 transition flex flex-col justify-between group text-left"
          >
            <div class="space-y-1.5">
              <div class="aspect-[2/3] rounded-lg overflow-hidden bg-gray-800 relative">
                <img 
                  v-if="item.poster" 
                  :src="item.poster" 
                  :alt="item.title" 
                  class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-gray-600 text-xs">
                  Resim Yok
                </div>
                <div v-if="item.rating" class="absolute top-1 right-1 px-1.5 py-0.5 rounded bg-black/80 backdrop-blur-sm text-[10px] font-bold text-amber-400">
                  ★ {{ item.rating }}
                </div>
              </div>
              <div class="font-bold text-xs text-white truncate" :title="item.title">{{ item.title }}</div>
              <div class="text-[10px] text-gray-400">{{ item.year || 'N/A' }}</div>
            </div>

            <button 
              @click="importSingleItem(item)" 
              :disabled="importingId === item.id"
              class="mt-2 w-full py-1.5 px-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold transition flex items-center justify-center gap-1 disabled:opacity-50"
            >
              <i :class="importingId === item.id ? 'fa-spinner fa-spin' : 'fa-plus'" class="fas text-[10px]"></i>
              <span>İçe Aktar</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Automation & Scheduler Settings Form -->
      <div class="p-6 rounded-3xl bg-gray-950/80 border border-white/10 space-y-6 backdrop-blur-xl">
        <div class="border-b border-white/10 pb-4">
          <h2 class="text-base font-black text-white flex items-center gap-2">
            <i class="fas fa-sliders-h text-purple-400"></i>
            <span>Otomatik Veri Çekme & Zamanlayıcı Ayarları (Scheduler)</span>
          </h2>
          <p class="text-xs text-gray-400 mt-0.5">
            Platformun her gün belirli saatte arka planda otomatik olarak yeni trendleri ve bölümleri çekmesini yönetin.
          </p>
        </div>

        <form @submit.prevent="saveAutomationSettings" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Automation Toggle -->
            <div class="p-4 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-between">
              <div>
                <div class="font-bold text-xs text-white">Otomatik Çekim Durumu</div>
                <div class="text-[11px] text-gray-400">Zamanlanmış cron görevini aktif eder</div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="automationForm.tmdb_auto_sync_enabled" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
              </label>
            </div>

            <!-- Frequency Dropdown -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-300">Otomatik Çekim Sıklığı</label>
              <select v-model="automationForm.tmdb_auto_sync_frequency" class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                <option value="daily">Her Gün Saat 04:00 (Önerilen)</option>
                <option value="every_12_hours">12 Saatte Bir</option>
                <option value="weekly">Haftada Bir</option>
                <option value="hourly">Her Saat Başı</option>
              </select>
            </div>

            <!-- Pages Count -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-gray-300">Sayfa Başına Veri Sayısı</label>
              <select v-model="automationForm.tmdb_auto_sync_pages" class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                <option :value="1">1 Sayfa (20 Başlık)</option>
                <option :value="2">2 Sayfa (40 Başlık - Dengeli)</option>
                <option :value="3">3 Sayfa (60 Başlık)</option>
                <option :value="5">5 Sayfa (100 Başlık)</option>
              </select>
            </div>
          </div>

          <!-- Cron Info Box -->
          <div class="p-4 rounded-2xl bg-indigo-950/30 border border-indigo-500/20 flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs text-indigo-300">
            <div class="flex items-center gap-3">
              <i class="fas fa-terminal text-lg text-indigo-400"></i>
              <div>
                <span class="font-bold text-white">Sunucu Cron Komutu:</span>
                <p class="text-[11px] text-gray-400 mt-0.5">Zamanlanmış görevlerin tetiklenmesi için sunucunuzda aşağıdaki cron komutu çalışmalıdır:</p>
              </div>
            </div>
            <code class="px-3 py-1.5 rounded-lg bg-black/60 border border-white/10 font-mono text-[11px] text-amber-300 select-all">
              * * * * * php artisan schedule:run >> /dev/null 2>&1
            </code>
          </div>

          <div class="flex justify-end">
            <button 
              type="submit" 
              :disabled="savingSettings"
              class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition flex items-center gap-2 disabled:opacity-50"
            >
              <i :class="savingSettings ? 'fa-spinner fa-spin' : 'fa-save'" class="fas"></i>
              <span>Ayarları Kaydet</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Recently Added Titles Preview -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Movies -->
        <div class="p-6 rounded-3xl bg-gray-950/80 border border-white/10 space-y-4 backdrop-blur-xl">
          <div class="flex items-center justify-between border-b border-white/10 pb-3">
            <h3 class="text-sm font-black text-white flex items-center gap-2">
              <i class="fas fa-film text-indigo-400"></i>
              <span>Son Eklenen Filmler ({{ recent_movies.length }})</span>
            </h3>
            <Link href="/admin/movies" class="text-[11px] text-indigo-400 hover:underline">Tümünü Gör</Link>
          </div>
          <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
            <div v-for="movie in recent_movies" :key="movie.id" class="space-y-1 group">
              <div class="aspect-[2/3] rounded-lg overflow-hidden bg-gray-800 relative">
                <img :src="movie.poster_path || '/images/placeholder-poster.jpg'" :alt="movie.title" class="w-full h-full object-cover group-hover:scale-105 transition" />
                <div class="absolute top-1 right-1 px-1 rounded bg-black/70 text-[9px] font-bold text-amber-400">
                  ★ {{ movie.vote_average }}
                </div>
              </div>
              <div class="text-[11px] font-bold text-white truncate" :title="movie.title">{{ movie.title }}</div>
            </div>
          </div>
        </div>

        <!-- Recent TV Shows -->
        <div class="p-6 rounded-3xl bg-gray-950/80 border border-white/10 space-y-4 backdrop-blur-xl">
          <div class="flex items-center justify-between border-b border-white/10 pb-3">
            <h3 class="text-sm font-black text-white flex items-center gap-2">
              <i class="fas fa-tv text-purple-400"></i>
              <span>Son Eklenen Dizi & Animeler ({{ recent_tv_shows.length }})</span>
            </h3>
            <Link href="/admin/tv-shows" class="text-[11px] text-purple-400 hover:underline">Tümünü Gör</Link>
          </div>
          <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
            <div v-for="show in recent_tv_shows" :key="show.id" class="space-y-1 group">
              <div class="aspect-[2/3] rounded-lg overflow-hidden bg-gray-800 relative">
                <img :src="show.poster_path || '/images/placeholder-poster.jpg'" :alt="show.title" class="w-full h-full object-cover group-hover:scale-105 transition" />
                <span v-if="show.is_anime" class="absolute top-1 left-1 px-1 rounded bg-rose-600 text-[8px] font-black text-white uppercase">
                  Anime
                </span>
                <div class="absolute top-1 right-1 px-1 rounded bg-black/70 text-[9px] font-bold text-amber-400">
                  ★ {{ show.vote_average }}
                </div>
              </div>
              <div class="text-[11px] font-bold text-white truncate" :title="show.title">{{ show.title }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Pages/Admin/Layout.vue';

const props = defineProps({
  stats: Object,
  tmdb_config: Object,
  automation: Object,
  recent_movies: Array,
  recent_tv_shows: Array,
});

const syncing = ref(false);
const activeSyncMode = ref('');
const syncInBackground = ref(false);

const selectedGenre = ref('scifi');
const genrePages = ref(2);

// Live search state
const searchQuery = ref('');
const searchMediaType = ref('movie');
const searching = ref(false);
const searchResults = ref([]);
const importingId = ref(null);

// Automation Form
const automationForm = useForm({
  tmdb_auto_sync_enabled: props.automation?.enabled ?? true,
  tmdb_auto_sync_frequency: props.automation?.frequency || 'daily',
  tmdb_auto_sync_pages: props.automation?.pages || 2,
  tmdb_auto_sync_min_votes: props.automation?.min_votes || 40,
});
const savingSettings = ref(false);

const runSync = (mode, customParams = {}) => {
  if (syncing.value) return;
  syncing.value = true;
  activeSyncMode.value = mode;

  router.post('/admin/tmdb/sync', {
    mode,
    background: syncInBackground.value,
    ...customParams,
  }, {
    preserveScroll: true,
    onFinish: () => {
      syncing.value = false;
      activeSyncMode.value = '';
    }
  });
};

const triggerCuratedSync = () => {
  runSync('curated');
};

const executeSearch = async () => {
  if (!searchQuery.value || searchQuery.value.trim().length < 2) return;
  searching.value = true;

  try {
    const res = await fetch('/admin/tmdb/search', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        query: searchQuery.value.trim(),
        media: searchMediaType.value,
      }),
    });

    const data = await res.json();
    searchResults.value = data.results || [];
  } catch (e) {
    console.error('TMDB Search error', e);
  } finally {
    searching.value = false;
  }
};

const importSingleItem = (item) => {
  importingId.value = item.id;

  router.post('/admin/tmdb/import-single', {
    tmdb_id: item.id,
    media: item.media,
  }, {
    preserveScroll: true,
    onFinish: () => {
      importingId.value = null;
    }
  });
};

const saveAutomationSettings = () => {
  savingSettings.value = true;
  automationForm.post('/admin/tmdb/settings', {
    preserveScroll: true,
    onFinish: () => {
      savingSettings.value = false;
    }
  });
};
</script>
