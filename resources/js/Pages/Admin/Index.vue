<template>
  <AdminLayout>
    <div class="space-y-8">
      <!-- 1. Header & Quick Actions -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-white/10">
        <div>
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
            <span>Yönetim Kontrol Merkezi</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
              <span>Canlı Sistem</span>
            </span>
          </h1>
          <p class="text-xs text-gray-400 mt-1">Platform analitiği, içerik yönetimi, kullanıcı bildirimleri ve anlık operasyon verileri.</p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex items-center gap-2.5 flex-wrap">
          <button 
            @click="clearCache" 
            :disabled="clearingCache"
            class="px-3.5 py-2 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] text-gray-300 hover:text-white text-xs font-semibold transition border border-white/10 flex items-center gap-2 shadow-sm"
            title="Uygulama ve rota önbelleğini temizler"
          >
            <i :class="clearingCache ? 'fas fa-spinner fa-spin text-amber-400' : 'fas fa-broom text-amber-400'"></i>
            <span>Önbelleği Temizle</span>
          </button>

          <Link 
            href="/admin/movies" 
            class="px-4 py-2 rounded-xl bg-gradient-to-r from-netflix to-red-700 hover:from-red-600 hover:to-red-800 text-white text-xs font-bold transition shadow-lg shadow-red-600/30 flex items-center gap-2"
          >
            <i class="fas fa-film"></i>
            <span>Film Yönetimi</span>
          </Link>

          <Link 
            href="/admin/tv-shows" 
            class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition shadow-lg shadow-purple-600/30 flex items-center gap-2"
          >
            <i class="fas fa-tv"></i>
            <span>Dizi Yönetimi</span>
          </Link>
        </div>
      </div>

      <!-- 2. Kurumsal Yönetim Merkezi (Hızlı Erişim - 6 Temel Modül) -->
      <section class="space-y-3">
        <div class="flex items-center justify-between">
          <h2 class="text-xs font-extrabold uppercase tracking-wider text-gray-400 flex items-center gap-2">
            <i class="fas fa-cubes text-netflix"></i>
            <span>Kurumsal Yönetim Merkezi (Hızlı Erişim)</span>
          </h2>
          <span class="text-[11px] text-gray-500">6 Gelişmiş Modül</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
          <!-- Modül 1: Yayın Analitiği -->
          <Link 
            href="/admin/analytics" 
            class="group p-3.5 rounded-2xl bg-white/[0.03] hover:bg-white/[0.07] border border-white/10 hover:border-indigo-500/40 transition-all duration-200 flex flex-col justify-between space-y-3 relative overflow-hidden"
          >
            <div class="flex items-center justify-between">
              <div class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-400 flex items-center justify-center text-sm shadow-sm group-hover:scale-110 transition-transform">
                <i class="fas fa-chart-line"></i>
              </div>
              <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                Canlı
              </span>
            </div>
            <div>
              <div class="text-xs font-bold text-white group-hover:text-indigo-400 transition-colors">Yayın Analitiği</div>
              <div class="text-[10px] text-gray-400 mt-0.5 line-clamp-2">Oynatma ve tamamlama verileri</div>
            </div>
          </Link>

          <!-- Modül 2: Toplu TMDB Aktarımı -->
          <Link 
            href="/admin/tmdb/bulk" 
            class="group p-3.5 rounded-2xl bg-white/[0.03] hover:bg-white/[0.07] border border-white/10 hover:border-emerald-500/40 transition-all duration-200 flex flex-col justify-between space-y-3 relative overflow-hidden"
          >
            <div class="flex items-center justify-between">
              <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center text-sm shadow-sm group-hover:scale-110 transition-transform">
                <i class="fas fa-cloud-download-alt"></i>
              </div>
              <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                Toplu
              </span>
            </div>
            <div>
              <div class="text-xs font-bold text-white group-hover:text-emerald-400 transition-colors">Toplu TMDB</div>
              <div class="text-[10px] text-gray-400 mt-0.5 line-clamp-2">Tek tıkla dizi & film aktarımı</div>
            </div>
          </Link>

          <!-- Modül 3: Vitrin Koleksiyonları -->
          <Link 
            href="/admin/collections" 
            class="group p-3.5 rounded-2xl bg-white/[0.03] hover:bg-white/[0.07] border border-white/10 hover:border-amber-500/40 transition-all duration-200 flex flex-col justify-between space-y-3 relative overflow-hidden"
          >
            <div class="flex items-center justify-between">
              <div class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-400 flex items-center justify-center text-sm shadow-sm group-hover:scale-110 transition-transform">
                <i class="fas fa-layer-group"></i>
              </div>
              <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-400 border border-amber-500/20">
                Vitrin
              </span>
            </div>
            <div>
              <div class="text-xs font-bold text-white group-hover:text-amber-400 transition-colors">Koleksiyonlar</div>
              <div class="text-[10px] text-gray-400 mt-0.5 line-clamp-2">Özel raf & liste yöneticisi</div>
            </div>
          </Link>

          <!-- Modül 4: Kırık Link & Hata Raporları -->
          <Link 
            href="/admin/reports" 
            class="group p-3.5 rounded-2xl bg-white/[0.03] hover:bg-white/[0.07] border border-white/10 hover:border-rose-500/40 transition-all duration-200 flex flex-col justify-between space-y-3 relative overflow-hidden"
          >
            <div class="flex items-center justify-between">
              <div class="w-8 h-8 rounded-xl bg-rose-500/15 text-rose-400 flex items-center justify-center text-sm shadow-sm group-hover:scale-110 transition-transform">
                <i class="fas fa-flag"></i>
              </div>
              <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-md bg-rose-500/10 text-rose-400 border border-rose-500/20">
                Kuyruk
              </span>
            </div>
            <div>
              <div class="text-xs font-bold text-white group-hover:text-rose-400 transition-colors">Hata Raporları</div>
              <div class="text-[10px] text-gray-400 mt-0.5 line-clamp-2">Kırık link ve oynatıcı bildirimleri</div>
            </div>
          </Link>

          <!-- Modül 5: Global Duyurular -->
          <Link 
            href="/admin/announcements" 
            class="group p-3.5 rounded-2xl bg-white/[0.03] hover:bg-white/[0.07] border border-white/10 hover:border-sky-500/40 transition-all duration-200 flex flex-col justify-between space-y-3 relative overflow-hidden"
          >
            <div class="flex items-center justify-between">
              <div class="w-8 h-8 rounded-xl bg-sky-500/15 text-sky-400 flex items-center justify-center text-sm shadow-sm group-hover:scale-110 transition-transform">
                <i class="fas fa-bullhorn"></i>
              </div>
              <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-md bg-sky-500/10 text-sky-400 border border-sky-500/20">
                Duyuru
              </span>
            </div>
            <div>
              <div class="text-xs font-bold text-white group-hover:text-sky-400 transition-colors">Duyuru Paneli</div>
              <div class="text-[10px] text-gray-400 mt-0.5 line-clamp-2">Site geneli banner ve bildirimler</div>
            </div>
          </Link>

          <!-- Modül 6: Güvenlik & Denetim Logları -->
          <Link 
            href="/admin/logs" 
            class="group p-3.5 rounded-2xl bg-white/[0.03] hover:bg-white/[0.07] border border-white/10 hover:border-teal-500/40 transition-all duration-200 flex flex-col justify-between space-y-3 relative overflow-hidden"
          >
            <div class="flex items-center justify-between">
              <div class="w-8 h-8 rounded-xl bg-teal-500/15 text-teal-400 flex items-center justify-center text-sm shadow-sm group-hover:scale-110 transition-transform">
                <i class="fas fa-shield-halved"></i>
              </div>
              <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-md bg-teal-500/10 text-teal-400 border border-teal-500/20">
                Güvenlik
              </span>
            </div>
            <div>
              <div class="text-xs font-bold text-white group-hover:text-teal-400 transition-colors">Denetim & Loglar</div>
              <div class="text-[10px] text-gray-400 mt-0.5 line-clamp-2">IP engelleme ve işlem kayıtları</div>
            </div>
          </Link>
        </div>
      </section>

      <!-- 3. KPI Metrik Tablosu (6 Kart) -->
      <section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <!-- Filmler -->
        <div class="p-4 rounded-2xl bg-white/[0.03] border border-white/10 hover:border-netflix/40 transition-all flex flex-col justify-between relative overflow-hidden group">
          <div class="absolute top-0 inset-x-0 h-0.5 bg-gradient-to-r from-red-500/0 via-red-500 to-red-500/0 opacity-60"></div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-gray-400">Filmler</span>
            <div class="w-7 h-7 rounded-lg bg-red-500/10 text-netflix flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
              <i class="fas fa-film"></i>
            </div>
          </div>
          <div>
            <div class="text-2xl font-black text-white">{{ stats.totalMovies || 0 }}</div>
            <div class="text-[10px] text-gray-400 mt-0.5 flex items-center gap-1">
              <span class="text-netflix font-bold">{{ stats.trendingMovies || 0 }}</span>
              <span>trendde</span>
            </div>
          </div>
        </div>

        <!-- Diziler & Bölümler -->
        <div class="p-4 rounded-2xl bg-white/[0.03] border border-white/10 hover:border-purple-500/40 transition-all flex flex-col justify-between relative overflow-hidden group">
          <div class="absolute top-0 inset-x-0 h-0.5 bg-gradient-to-r from-purple-500/0 via-purple-500 to-purple-500/0 opacity-60"></div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-gray-400">Diziler</span>
            <div class="w-7 h-7 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
              <i class="fas fa-tv"></i>
            </div>
          </div>
          <div>
            <div class="text-2xl font-black text-white">{{ stats.totalShows || 0 }}</div>
            <div class="text-[10px] text-gray-400 mt-0.5">
              <span>{{ stats.totalEpisodes || 0 }} bölüm ({{ stats.animeCount || 0 }} anime)</span>
            </div>
          </div>
        </div>

        <!-- İncelemeler -->
        <div class="p-4 rounded-2xl bg-white/[0.03] border border-white/10 hover:border-amber-500/40 transition-all flex flex-col justify-between relative overflow-hidden group">
          <div class="absolute top-0 inset-x-0 h-0.5 bg-gradient-to-r from-amber-500/0 via-amber-500 to-amber-500/0 opacity-60"></div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-gray-400">İncelemeler</span>
            <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
              <i class="fas fa-star"></i>
            </div>
          </div>
          <div>
            <div class="text-2xl font-black text-white">{{ stats.totalReviews || 0 }}</div>
            <div class="text-[10px] text-amber-400 font-bold mt-0.5 flex items-center gap-1">
              <span>★ {{ stats.avgRating || '0.0' }} / 10 puan</span>
            </div>
          </div>
        </div>

        <!-- Talepler -->
        <div 
          :class="stats.pendingRequests > 0 ? 'border-amber-500/40 bg-amber-500/[0.06]' : 'border-white/10 bg-white/[0.03]'"
          class="p-4 rounded-2xl border transition-all flex flex-col justify-between relative overflow-hidden group"
        >
          <div class="absolute top-0 inset-x-0 h-0.5 bg-gradient-to-r from-amber-500/0 via-amber-500 to-amber-500/0 opacity-60"></div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-gray-400">Talepler</span>
            <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
              <i class="fas fa-envelope-open-text"></i>
            </div>
          </div>
          <div>
            <div class="text-2xl font-black text-white flex items-center gap-2">
              <span>{{ stats.pendingRequests || 0 }}</span>
              <span v-if="stats.pendingRequests > 0" class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
            </div>
            <div class="text-[10px] text-gray-400 mt-0.5">{{ stats.totalRequests || 0 }} toplam talep</div>
          </div>
        </div>

        <!-- Spor Karşılaşmaları -->
        <div class="p-4 rounded-2xl bg-white/[0.03] border border-white/10 hover:border-emerald-500/40 transition-all flex flex-col justify-between relative overflow-hidden group">
          <div class="absolute top-0 inset-x-0 h-0.5 bg-gradient-to-r from-emerald-500/0 via-emerald-500 to-emerald-500/0 opacity-60"></div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-gray-400">Spor Maçları</span>
            <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
              <i class="fas fa-futbol"></i>
            </div>
          </div>
          <div>
            <div class="text-2xl font-black text-white">{{ stats.totalSports || 0 }}</div>
            <div class="text-[10px] text-emerald-400 font-bold mt-0.5">{{ stats.liveSports || 0 }} canlı yayın</div>
          </div>
        </div>

        <!-- Kayıtlı Kullanıcılar -->
        <div class="p-4 rounded-2xl bg-white/[0.03] border border-white/10 hover:border-blue-500/40 transition-all flex flex-col justify-between relative overflow-hidden group">
          <div class="absolute top-0 inset-x-0 h-0.5 bg-gradient-to-r from-blue-500/0 via-blue-500 to-blue-500/0 opacity-60"></div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-gray-400">Kullanıcılar</span>
            <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
              <i class="fas fa-users"></i>
            </div>
          </div>
          <div>
            <div class="text-2xl font-black text-white">{{ stats.totalUsers || 0 }}</div>
            <div class="text-[10px] text-gray-400 mt-0.5">{{ stats.totalStreams || 0 }} toplam oynatma</div>
          </div>
        </div>
      </section>

      <!-- 4. Katalog İçerik Dağılımı (Görsel Dağılım Çubuğu) -->
      <section class="p-5 rounded-2xl bg-white/[0.03] border border-white/10 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <i class="fas fa-chart-pie text-netflix text-sm"></i>
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-white">Katalog İçerik Dağılım Oranı</h2>
          </div>
          <div class="text-xs text-gray-400">
            Toplam Arşiv: <strong class="text-white">{{ totalContentCount }}</strong> medya içeriği
          </div>
        </div>

        <!-- Multi-segment progress bar -->
        <div class="w-full h-3 rounded-full bg-white/10 overflow-hidden flex shadow-inner">
          <div 
            :style="{ width: percentMovies + '%' }" 
            class="bg-gradient-to-r from-netflix to-red-600 transition-all duration-500" 
            :title="'Filmler: ' + percentMovies + '%'"
          ></div>
          <div 
            :style="{ width: percentShows + '%' }" 
            class="bg-gradient-to-r from-purple-600 to-indigo-600 transition-all duration-500" 
            :title="'Diziler: ' + percentShows + '%'"
          ></div>
          <div 
            :style="{ width: percentSports + '%' }" 
            class="bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-500" 
            :title="'Spor: ' + percentSports + '%'"
          ></div>
        </div>

        <!-- Dağılım Lejantı -->
        <div class="flex items-center flex-wrap gap-4 pt-1 text-xs">
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-netflix"></span>
            <span class="text-gray-300">Filmler:</span>
            <strong class="text-white">{{ stats.totalMovies || 0 }} (%{{ percentMovies }})</strong>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
            <span class="text-gray-300">Diziler:</span>
            <strong class="text-white">{{ stats.totalShows || 0 }} (%{{ percentShows }})</strong>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            <span class="text-gray-300">Spor Karşılaşmaları:</span>
            <strong class="text-white">{{ stats.totalSports || 0 }} (%{{ percentSports }})</strong>
          </div>
          <div class="flex items-center gap-1.5 ml-auto text-gray-400 text-[11px]">
            <i class="fas fa-fire-alt text-amber-400"></i>
            <span>{{ stats.animeCount || 0 }} Anime serisi dahil</span>
          </div>
        </div>
      </section>

      <!-- 5. Ana Bölümler (Sol: Talepler & İncelemeler | Sağ: TMDB & Kullanıcılar & Sunucu) -->
      <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sol Taraf: İşlem Bekleyen Talepler & İncelemeler (2 Kolon) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Bekleyen İçerik Talepleri -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="fas fa-envelope-open-text text-amber-400 text-sm"></i>
                <span>İşlem Bekleyen İçerik Talepleri</span>
              </h2>
              <Link 
                href="/admin/requests" 
                class="text-xs text-netflix hover:underline font-semibold"
              >
                Tüm Talepleri Gör
              </Link>
            </div>

            <div v-if="recentRequests && recentRequests.length > 0" class="rounded-2xl border border-white/10 bg-gray-950/60 overflow-hidden divide-y divide-white/5 shadow-xl">
              <div 
                v-for="req in recentRequests" 
                :key="req.id"
                class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-white/[0.04] transition"
              >
                <div class="space-y-1 min-w-0">
                  <div class="flex items-center gap-2">
                    <span 
                      :class="req.type === 'tv' ? 'bg-purple-500/20 text-purple-400 border-purple-500/30' : 'bg-blue-500/20 text-blue-400 border-blue-500/30'"
                      class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded border"
                    >
                      {{ req.type === 'tv' ? 'Dizi' : 'Film' }}
                    </span>
                    <span 
                      :class="statusBadge(req.status)" 
                      class="text-[9px] font-extrabold uppercase px-2 py-0.2 rounded-full border"
                    >
                      {{ translateStatus(req.status) }}
                    </span>
                    <span v-if="req.release_year" class="text-xs text-gray-500">({{ req.release_year }})</span>
                  </div>

                  <div class="text-sm font-bold text-white truncate">{{ req.title }}</div>
                  <div class="text-[11px] text-gray-400 flex items-center gap-2">
                    <span>Talep Eden: <strong class="text-gray-300">{{ req.user?.name || 'Kullanıcı' }}</strong></span>
                    <span>•</span>
                    <span>{{ formatDate(req.created_at) }}</span>
                  </div>
                </div>

                <!-- Hızlı Durum Güncelleme Butonları -->
                <div class="flex items-center gap-1.5 sm:flex-shrink-0 pt-2 sm:pt-0">
                  <button 
                    v-if="req.status !== 'available'"
                    @click="updateStatus(req.id, 'available')"
                    class="px-2.5 py-1 rounded-lg bg-green-500/20 hover:bg-green-500/30 text-green-400 text-xs font-bold border border-green-500/30 transition"
                    title="Kataloğa Eklendi Olarak İşaretle"
                  >
                    <i class="fas fa-check mr-1"></i> Eklendi
                  </button>
                  <button 
                    v-if="req.status === 'pending'"
                    @click="updateStatus(req.id, 'in_review')"
                    class="px-2.5 py-1 rounded-lg bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 text-xs font-bold border border-blue-500/30 transition"
                    title="İnceleniyor Olarak İşaretle"
                  >
                    <i class="fas fa-search mr-1"></i> İncele
                  </button>
                  <button 
                    v-if="req.status !== 'rejected'"
                    @click="updateStatus(req.id, 'rejected')"
                    class="px-2.5 py-1 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-400 text-xs font-bold border border-red-500/30 transition"
                    title="Talebi Reddet"
                  >
                    <i class="fas fa-times mr-1"></i> Reddet
                  </button>
                </div>
              </div>
            </div>

            <div v-else class="p-8 rounded-2xl bg-white/[0.02] border border-white/10 text-center text-xs text-gray-400">
              Şu anda bekleyen içerik talebi bulunmuyor.
            </div>
          </div>

          <!-- Son Topluluk İncelemeleri -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <h2 class="text-sm font-bold text-white flex items-center gap-2">
                <i class="fas fa-star text-amber-400 text-sm"></i>
                <span>Son Topluluk İncelemeleri & Yorumlar</span>
              </h2>
              <Link 
                href="/admin/reviews" 
                class="text-xs text-netflix hover:underline font-semibold"
              >
                İnceleme Moderatörü
              </Link>
            </div>

            <div v-if="recentReviews && recentReviews.length > 0" class="rounded-2xl border border-white/10 bg-gray-950/60 overflow-hidden divide-y divide-white/5 shadow-xl">
              <div 
                v-for="rev in recentReviews" 
                :key="rev.id" 
                class="p-4 flex flex-col sm:flex-row sm:items-start justify-between gap-3 hover:bg-white/[0.04] transition"
              >
                <div class="flex items-start gap-3 min-w-0">
                  <img 
                    :src="rev.user?.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop'" 
                    class="w-7 h-7 rounded-full object-cover flex-shrink-0 mt-0.5 ring-1 ring-white/20" 
                  />
                  <div class="space-y-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="font-bold text-xs text-white">{{ rev.user?.name || 'Kullanıcı' }}</span>
                      <span class="text-[10px] text-gray-500">•</span>
                      <span class="text-xs font-medium text-gray-400 truncate max-w-[160px]">{{ rev.reviewable?.title || 'İçerik' }}</span>
                      <div class="px-2 py-0.2 rounded text-[10px] font-black bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center gap-1">
                        <i class="fas fa-star text-[9px]"></i>
                        <span>{{ rev.rating }}/10</span>
                      </div>
                      <span v-if="rev.has_spoiler" class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                        SPOILER
                      </span>
                    </div>
                    <p class="text-xs text-gray-300 line-clamp-2 leading-relaxed whitespace-pre-wrap">
                      {{ rev.content }}
                    </p>
                  </div>
                </div>

                <div class="flex items-center gap-2 sm:flex-shrink-0 pt-1 sm:pt-0">
                  <button 
                    @click="deleteReviewInline(rev.id)" 
                    class="px-2.5 py-1 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-semibold border border-red-500/20 transition"
                    title="İncelemeyi Kaldır"
                  >
                    <i class="fas fa-trash-alt text-[10px]"></i>
                  </button>
                </div>
              </div>
            </div>

            <div v-else class="p-8 rounded-2xl bg-white/[0.02] border border-white/10 text-center text-xs text-gray-400">
              Henüz kaydedilmiş bir inceleme bulunmuyor.
            </div>
          </div>
        </div>

        <!-- Sağ Taraf: TMDB API Bağlantısı, Yeni Kullanıcılar & Sunucu Ortamı (1 Kolon) -->
        <div class="space-y-6">
          <!-- TMDB API Bağlantı Testi -->
          <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/10 space-y-4 shadow-xl">
            <div class="flex items-center justify-between">
              <h3 class="text-xs font-extrabold uppercase tracking-wider text-gray-300 flex items-center gap-2">
                <i class="fas fa-cloud text-cyan-400"></i>
                <span>TMDB Entegrasyon Durumu</span>
              </h3>
              <span 
                :class="systemInfo.tmdb_configured ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border-amber-500/30'"
                class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full border"
              >
                {{ systemInfo.tmdb_configured ? 'Operasyonel' : 'Anahtar Eksik' }}
              </span>
            </div>

            <p class="text-[11px] text-gray-400 leading-relaxed">
              TMDB API bağlantısını test edin ve canlı meta verilerini anında doğrulayın.
            </p>

            <div class="space-y-2">
              <div class="flex gap-2">
                <select 
                  v-model="testTmdbType" 
                  class="bg-black/60 border border-white/10 rounded-xl px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-netflix"
                >
                  <option value="movie">Film</option>
                  <option value="tv">Dizi</option>
                </select>
                <input 
                  v-model="testTmdbId" 
                  type="text" 
                  placeholder="örn. 550 (Fight Club)" 
                  class="flex-1 bg-black/60 border border-white/10 rounded-xl px-3 py-1.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-netflix"
                  @keyup.enter="runTmdbTest"
                />
                <button 
                  @click="runTmdbTest" 
                  :disabled="testingTmdb || !testTmdbId"
                  class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition disabled:opacity-40"
                  title="Test Et"
                >
                  <i :class="testingTmdb ? 'fas fa-spinner fa-spin' : 'fas fa-plug'"></i>
                </button>
              </div>

              <!-- Test Sonuç Kutusu -->
              <div 
                v-if="tmdbTestFeedback" 
                :class="tmdbTestFeedback.status === 'success' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-300' : 'bg-red-500/10 border-red-500/20 text-red-300'"
                class="p-2.5 rounded-xl border text-xs flex items-center justify-between gap-2 animate-fade-in"
              >
                <div class="truncate">
                  <strong v-if="tmdbTestFeedback.title">{{ tmdbTestFeedback.title }}</strong>
                  <span v-else>{{ tmdbTestFeedback.message }}</span>
                  <span v-if="tmdbTestFeedback.release_date" class="text-[10px] text-gray-400 block">({{ tmdbTestFeedback.release_date }})</span>
                </div>
                <button @click="tmdbTestFeedback = null" class="opacity-60 hover:opacity-100 text-xs">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Yeni Kayıt Olan Kullanıcılar -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <h2 class="text-xs font-extrabold uppercase tracking-wider text-gray-400 flex items-center gap-2">
                <i class="fas fa-user-plus text-blue-400"></i>
                <span>Son Kayıtlı Kullanıcılar</span>
              </h2>
              <Link href="/admin/users" class="text-xs text-netflix hover:underline font-semibold">Tümünü Gör</Link>
            </div>

            <div class="p-4 rounded-2xl bg-gray-950/60 border border-white/10 divide-y divide-white/5 space-y-2 shadow-xl">
              <div 
                v-for="u in recentUsers" 
                :key="u.id"
                class="pt-2 first:pt-0 flex items-center justify-between text-xs"
              >
                <div class="flex items-center gap-2.5 min-w-0">
                  <img 
                    :src="u.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop'" 
                    class="w-7 h-7 rounded-full object-cover ring-1 ring-white/20" 
                  />
                  <div class="min-w-0">
                    <div class="font-bold text-white truncate">{{ u.name }}</div>
                    <div class="text-[10px] text-gray-400 truncate">{{ u.email }}</div>
                  </div>
                </div>
                <span 
                  :class="u.role === 'admin' ? 'bg-red-500/20 text-red-400 border-red-500/30' : 'bg-white/10 text-gray-400 border-white/10'"
                  class="text-[9px] font-black uppercase px-2 py-0.5 rounded border flex-shrink-0"
                >
                  {{ u.role === 'admin' ? 'Yönetici' : 'Üye' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Sunucu & Sistem Ortamı -->
          <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/10 space-y-3 shadow-xl">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-gray-400 flex items-center gap-2">
              <i class="fas fa-server text-netflix"></i>
              <span>Sunucu & Sistem Ortamı</span>
            </h3>

            <div class="space-y-2 text-xs">
              <div class="flex justify-between py-1 border-b border-white/5">
                <span class="text-gray-400">PHP Sürümü</span>
                <span class="font-mono font-bold text-white">{{ systemInfo.php_version }}</span>
              </div>
              <div class="flex justify-between py-1 border-b border-white/5">
                <span class="text-gray-400">Laravel Framework</span>
                <span class="font-mono font-bold text-white">{{ systemInfo.laravel_version }}</span>
              </div>
              <div class="flex justify-between py-1 border-b border-white/5">
                <span class="text-gray-400">Veritabanı Sürücüsü</span>
                <span class="font-mono font-bold text-white uppercase">{{ systemInfo.db_driver }}</span>
              </div>
              <div class="flex justify-between py-1">
                <span class="text-gray-400">Sunucu Zamanı</span>
                <span class="font-mono text-gray-300">{{ systemInfo.server_time }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from './Layout.vue';

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({}),
  },
  recentRequests: {
    type: Array,
    default: () => [],
  },
  recentUsers: {
    type: Array,
    default: () => [],
  },
  recentReviews: {
    type: Array,
    default: () => [],
  },
  systemInfo: {
    type: Object,
    default: () => ({}),
  },
});

const clearingCache = ref(false);
const testTmdbId = ref('');
const testTmdbType = ref('movie');
const testingTmdb = ref(false);
const tmdbTestFeedback = ref(null);

const totalContentCount = computed(() => {
  const m = props.stats.totalMovies || 0;
  const t = props.stats.totalShows || 0;
  const s = props.stats.totalSports || 0;
  return m + t + s || 1;
});

const percentMovies = computed(() => {
  return Math.round(((props.stats.totalMovies || 0) / totalContentCount.value) * 100);
});

const percentShows = computed(() => {
  return Math.round(((props.stats.totalShows || 0) / totalContentCount.value) * 100);
});

const percentSports = computed(() => {
  return Math.round(((props.stats.totalSports || 0) / totalContentCount.value) * 100);
});

const clearCache = () => {
  clearingCache.value = true;
  router.post('/admin/clear-cache', {}, {
    preserveScroll: true,
    onFinish: () => {
      clearingCache.value = false;
    },
  });
};

const updateStatus = (id, status) => {
  router.put(`/admin/requests/${id}`, {
    status: status,
  }, {
    preserveScroll: true,
  });
};

const deleteReviewInline = (id) => {
  if (confirm('Bu incelemeyi silmek istediğinize emin misiniz?')) {
    router.delete(`/admin/reviews/${id}`, {
      preserveScroll: true,
    });
  }
};

const runTmdbTest = async () => {
  if (!testTmdbId.value) return;
  testingTmdb.value = true;
  tmdbTestFeedback.value = null;

  try {
    const res = await fetch('/admin/tmdb/fetch', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        tmdb_id: testTmdbId.value,
        type: testTmdbType.value,
      }),
    });

    const data = await res.json();
    if (data.status === 'success') {
      tmdbTestFeedback.value = {
        status: 'success',
        title: data.data?.title || data.data?.name || 'İçerik Bulundu',
        release_date: data.data?.release_date || data.data?.first_air_date,
      };
    } else {
      tmdbTestFeedback.value = {
        status: 'error',
        message: data.message || 'TMDB üzerinde içerik bulunamadı.',
      };
    }
  } catch (err) {
    tmdbTestFeedback.value = {
      status: 'error',
      message: 'TMDB uç noktasına bağlanılamadı.',
    };
  } finally {
    testingTmdb.value = false;
  }
};

const statusBadge = (status) => {
  switch (status) {
    case 'available':
      return 'bg-green-500/20 text-green-400 border-green-500/30';
    case 'in_review':
      return 'bg-blue-500/20 text-blue-400 border-blue-500/30';
    case 'rejected':
      return 'bg-red-500/20 text-red-400 border-red-500/30';
    default:
      return 'bg-amber-500/20 text-amber-400 border-amber-500/30';
  }
};

const translateStatus = (status) => {
  switch (status) {
    case 'available':
      return 'Eklendi';
    case 'in_review':
      return 'İnceleniyor';
    case 'rejected':
      return 'Reddedildi';
    default:
      return 'Beklemede';
  }
};

const formatDate = (str) => {
  if (!str) return 'Yakın zamanda';
  return new Date(str).toLocaleDateString('tr-TR', { month: 'short', day: 'numeric' });
};
</script>
