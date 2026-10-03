<template>
  <AppLayout>
    <div class="home-v2 pb-16">
      <!-- 1. Hero Carousel Banner -->
      <HeroBanner :items="heroMovies" />

      <!-- 2. Centered Cinema Category Rail & "Ne İzlesem?" -->
      <div class="px-3 sm:px-8 max-w-7xl mx-auto my-3 sm:my-6 relative z-20">
        <div class="flex items-center justify-start sm:justify-center gap-2 overflow-x-auto no-scrollbar py-1 px-1">
          <!-- "Ne İzlesem?" Highlight Pill -->
          <button
            type="button"
            @click="showRandomModal = true"
            class="px-4 py-2 rounded-full bg-netflix/20 hover:bg-netflix text-red-400 hover:text-white border border-netflix/40 hover:border-netflix font-bold text-xs flex items-center gap-2 transition-all duration-200 cursor-pointer shadow-lg shadow-netflix/20 flex-shrink-0 group"
          >
            <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3L12 3z"/>
            </svg>
            <span class="tracking-wide">{{ $t('home.surprise_me') }}</span>
          </button>

          <!-- Category Pills -->
          <button
            v-for="opt in filterOptions"
            :key="opt.id"
            type="button"
            :class="[
              'px-4 py-2 rounded-full text-xs font-semibold whitespace-nowrap transition-all duration-200 cursor-pointer flex-shrink-0 backdrop-blur-md',
              activeFilter === opt.id
                ? 'bg-white text-black font-bold shadow-md shadow-white/10'
                : 'bg-white/[0.06] hover:bg-white/[0.14] text-zinc-300 hover:text-white border border-white/10 hover:border-white/20'
            ]"
            @click="handleFilterClick(opt)"
          >
            <span>{{ opt.label }}</span>
          </button>
        </div>
      </div>

      <!-- 3. Home Main Content Container -->
      <div class="home-v2-content">
        <!-- Top Ad Banner -->
        <AdSlot placement="header_banner" />

        <!-- Browse by Studio Section -->
        <section class="home-v2-section home-v2-studios" aria-labelledby="studio-heading">
          <div class="home-v2-heading">
            <h2 id="studio-heading">{{ $t('home.studios_title') }}</h2>
          </div>

          <!-- Studio Logos Grid -->
          <div class="home-v2-studio-grid">
            <button 
              v-for="studio in initialStudios" 
              :key="studio.name"
              type="button" 
              :class="['home-v2-studio', studio.light ? 'needs-light-logo' : '']"
              @click="filterByStudio(studio.name)"
            >
              <img :src="studio.logo" :alt="studio.name" loading="lazy" />
            </button>

            <!-- Show More Toggle -->
            <button 
              type="button" 
              class="home-v2-studio home-v2-studio-more" 
              @click="showAllStudios = !showAllStudios"
            >
              <i class="fas fa-grip"></i>
              <span>{{ showAllStudios ? $t('home.studios_less') : $t('home.studios_more') }}</span>
            </button>
          </div>

          <!-- Extended Studios Grid -->
          <div v-if="showAllStudios" class="home-v2-studio-grid home-v2-studio-extra mt-3">
            <button 
              v-for="studio in extraStudios" 
              :key="studio.name"
              type="button" 
              :class="['home-v2-studio', studio.light ? 'needs-light-logo' : '']"
              @click="filterByStudio(studio.name)"
            >
              <img :src="studio.logo" :alt="studio.name" loading="lazy" />
            </button>
          </div>
        </section>

        <!-- Continue Watching (Kaldığın Yerden Devam Et) -->
        <section v-if="continueWatching && continueWatching.length" class="home-v2-section mb-8" aria-labelledby="continue-watching-heading">
          <div class="home-v2-heading">
            <h2 id="continue-watching-heading" class="flex items-center gap-2">
              <i class="fas fa-play-circle text-netflix"></i>
              <span>{{ $t('home.continue_watching') }}</span>
            </h2>
            <span class="text-xs text-gray-400">{{ $t('home.incomplete_items', { count: continueWatching.length }) }}</span>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4 mt-2">
            <Link 
              v-for="item in continueWatching" 
              :key="item.id"
              :href="item.url"
              class="group relative rounded-2xl overflow-hidden bg-gray-900/90 border border-white/10 hover:border-netflix transition-all duration-300 shadow-xl flex flex-col"
            >
              <div class="aspect-[16/9] w-full relative overflow-hidden bg-black">
                <img 
                  :src="item.backdrop_path || item.poster_path || '/images/placeholder-poster.jpg'" 
                  :alt="item.title" 
                  class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent"></div>
                
                <!-- Play button hover overlay -->
                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-200">
                  <div class="w-10 h-10 rounded-full bg-netflix text-white flex items-center justify-center shadow-lg shadow-netflix/50">
                    <i class="fas fa-play text-xs pl-0.5"></i>
                  </div>
                </div>

                <!-- Progress Bar at bottom of image -->
                <div class="absolute bottom-0 inset-x-0 h-1.5 bg-white/20">
                  <div class="h-full bg-netflix transition-all duration-300" :style="`width: ${item.progress_percent}%`"></div>
                </div>

                <span v-if="item.season_number" class="absolute top-2 left-2 px-1.5 py-0.5 rounded bg-black/80 backdrop-blur-sm text-[10px] font-black text-amber-400">
                  S{{ item.season_number }}:B{{ item.episode_number }}
                </span>
              </div>

              <div class="p-2.5 flex flex-col justify-between flex-1">
                <div class="font-bold text-xs text-white truncate group-hover:text-netflix transition">
                  {{ item.title }}
                </div>
                <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                  <span>{{ $t('home.watched_percent', { percent: item.progress_percent }) }}</span>
                  <span class="text-netflix font-bold flex items-center gap-1">
                    <span>{{ $t('home.resume') }}</span>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                  </span>
                </div>
              </div>
            </Link>
          </div>
        </section>

        <!-- 3D Netflix-Style "Günün Top 10 Listesi" -->
        <section v-if="trending && trending.length" id="trending-section" class="home-v2-section mb-10" aria-labelledby="trending-heading">
          <div class="home-v2-heading flex items-center justify-between">
            <h2 id="trending-heading" class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
              <span>{{ $t('home.top_10_title') }}</span>
              <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-netflix/20 border border-netflix/40 text-netflix font-bold hidden sm:inline-block">
                {{ $t('home.top_10_badge_today') }}
              </span>
            </h2>
            <Link href="/trending" class="text-xs font-bold text-gray-400 hover:text-white flex items-center gap-1.5 group transition whitespace-nowrap flex-shrink-0">
              <span>{{ $t('home.view_all') }}</span>
              <i class="fas fa-chevron-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
            </Link>
          </div>

          <div class="relative group/shelf">
            <!-- Left Chevron -->
            <button 
              type="button" 
              @click="scrollShelf(trendingRowRef, 'left')"
              class="hidden md:flex absolute -left-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-black/85 hover:bg-netflix border border-white/20 hover:border-netflix text-white shadow-2xl items-center justify-center opacity-0 group-hover/shelf:opacity-100 transition-all duration-300 hover:scale-110 backdrop-blur-md cursor-pointer"
              :aria-label="$t('common.back')"
            >
              <i class="fas fa-chevron-left text-base"></i>
            </button>

            <!-- 3D Card Row -->
            <div ref="trendingRowRef" class="flex gap-4 sm:gap-6 overflow-x-auto py-4 px-1 no-scrollbar scroll-smooth">
              <Link 
                v-for="(item, idx) in trending" 
                :key="item.id"
                :href="`/movie/${item.slug}`"
                class="group relative flex-shrink-0 flex items-end select-none transition-transform duration-300 hover:scale-105"
              >
                <!-- 3D Big Numerals (1, 2, 3... 10) -->
                <div class="relative z-0 -mr-6 sm:-mr-8 select-none pointer-events-none">
                  <span 
                    class="top10-num font-black leading-none"
                    :class="idx === 9 ? 'text-7xl sm:text-8xl' : 'text-8xl sm:text-9xl'"
                  >
                    {{ idx + 1 }}
                  </span>
                </div>

                <!-- Poster Card -->
                <div class="relative z-10 w-32 sm:w-44 aspect-[2/3] rounded-2xl overflow-hidden bg-gray-900 border border-white/10 group-hover:border-netflix group-hover:shadow-2xl group-hover:shadow-red-600/30 transition-all duration-300">
                  <img 
                    :src="item.poster_url || item.poster_path" 
                    :alt="item.title" 
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" 
                    loading="lazy" 
                  />
                  <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent opacity-60 group-hover:opacity-85 transition-opacity"></div>
                  
                  <!-- Top Left "TOP 10" Badge -->
                  <div class="absolute top-2 left-2 flex items-center">
                    <span class="px-1.5 py-0.5 rounded bg-netflix text-[9px] font-black text-white tracking-tighter uppercase shadow-md">
                      TOP 10
                    </span>
                  </div>

                  <!-- Quality Badge -->
                  <div class="absolute top-2 right-2">
                    <span class="px-1.5 py-0.5 rounded bg-black/70 border border-white/20 text-[9px] font-bold text-gray-200 backdrop-blur-xs">
                      4K UHD
                    </span>
                  </div>

                  <!-- Hover Play Center -->
                  <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <div class="w-12 h-12 rounded-full bg-netflix text-white flex items-center justify-center shadow-xl shadow-netflix/60 scale-75 group-hover:scale-100 transition-transform duration-300">
                      <i class="fas fa-play text-sm pl-0.5"></i>
                    </div>
                  </div>

                  <!-- Bottom Info Overlay -->
                  <div class="absolute bottom-0 inset-x-0 p-2.5 bg-gradient-to-t from-black via-black/90 to-transparent">
                    <div class="font-black text-xs text-white truncate group-hover:text-netflix transition-colors">
                      {{ item.title }}
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-gray-300 mt-1">
                      <span class="flex items-center gap-1 text-amber-400 font-bold">
                        <i class="fas fa-star text-[9px]"></i>
                        <span>{{ item.vote_average || '7.8' }}</span>
                      </span>
                      <span class="text-emerald-400 font-extrabold">{{ $t('home.match') }}</span>
                    </div>
                  </div>
                </div>
              </Link>
            </div>

            <!-- Right Chevron -->
            <button 
              type="button" 
              @click="scrollShelf(trendingRowRef, 'right')"
              class="hidden md:flex absolute -right-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-black/85 hover:bg-netflix border border-white/20 hover:border-netflix text-white shadow-2xl items-center justify-center opacity-0 group-hover/shelf:opacity-100 transition-all duration-300 hover:scale-110 backdrop-blur-md cursor-pointer"
              :aria-label="$t('common.play')"
            >
              <i class="fas fa-chevron-right text-base"></i>
            </button>
          </div>
        </section>

        <!-- Curated Featured Collections -->
        <section v-for="col in curatedCollections" :key="'col-' + col.id" class="home-v2-section mb-8">
          <div class="home-v2-heading">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-amber-400"></span>
              <h2 class="text-white text-lg font-bold">{{ col.title }}</h2>
              <span class="text-xs text-gray-400 font-normal hidden sm:inline" v-if="col.description">— {{ col.description }}</span>
            </div>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
            <Link 
              v-for="item in col.items" 
              :key="item.id"
              :href="item.collectible_type.includes('Movie') ? `/movie/${item.collectible?.slug}` : `/tv-show/${item.collectible?.slug}`"
              class="home-v2-poster-card group"
            >
              <div class="aspect-[2/3] rounded-xl overflow-hidden bg-gray-900 relative">
                <img 
                  :src="item.collectible?.poster_url || '/images/placeholders/poster.png'" 
                  :alt="item.collectible?.title"
                  class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                  loading="lazy" 
                />
                <span class="absolute top-2 left-2 px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-black/70 text-amber-400 border border-white/10">
                  {{ item.collectible_type.includes('Movie') ? $t('home.movie') : $t('home.tv_show') }}
                </span>
              </div>
              <div class="mt-2 text-xs font-bold text-white truncate group-hover:text-netflix transition">
                {{ item.collectible?.title }}
              </div>
            </Link>
          </div>
        </section>

        <!-- Popular Movies Shelf with Smart Chevrons -->
        <section v-if="popularMovies && popularMovies.length" id="shelf-pop-movies" class="home-v2-section" aria-labelledby="shelf-pop-movies-heading">
          <div class="home-v2-heading">
            <h2 id="shelf-pop-movies-heading">{{ $t('home.popular_movies') }}</h2>
            <Link href="/movies">{{ $t('home.view_all') }} <i class="fas fa-chevron-right"></i></Link>
          </div>

          <div class="relative group/shelf">
            <button 
              type="button" 
              @click="scrollShelf(popMoviesRowRef, 'left')"
              class="hidden md:flex absolute -left-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/80 hover:bg-netflix border border-white/20 hover:border-netflix text-white shadow-2xl items-center justify-center opacity-0 group-hover/shelf:opacity-100 transition-all duration-300 hover:scale-110 backdrop-blur-md cursor-pointer"
              :aria-label="$t('common.back')"
            >
              <i class="fas fa-chevron-left text-sm"></i>
            </button>

            <div ref="popMoviesRowRef" class="home-v2-shelf scroll-smooth">
              <article 
                v-for="movie in popularMovies" 
                :key="movie.id"
                class="home-v2-content-card"
              >
                <Link :href="`/movie/${movie.slug}`" class="home-v2-content-image">
                  <img :src="movie.backdrop_url || movie.backdrop_path || movie.poster_url" :alt="movie.title" loading="lazy" />
                  <span class="home-v2-card-overlay"></span>
                  <span class="home-v2-card-play"><i class="fas fa-play"></i></span>
                  <span class="home-v2-card-quality">HD</span>
                </Link>
                <div class="home-v2-card-copy">
                  <Link :href="`/movie/${movie.slug}`">{{ movie.title }}</Link>
                  <div>
                    <span>{{ movie.release_date ? movie.release_date.substring(0, 4) : '2025' }}</span>
                    <span><i class="fas fa-star"></i>{{ movie.vote_average }}</span>
                  </div>
                </div>
              </article>
            </div>

            <button 
              type="button" 
              @click="scrollShelf(popMoviesRowRef, 'right')"
              class="hidden md:flex absolute -right-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/80 hover:bg-netflix border border-white/20 hover:border-netflix text-white shadow-2xl items-center justify-center opacity-0 group-hover/shelf:opacity-100 transition-all duration-300 hover:scale-110 backdrop-blur-md cursor-pointer"
              :aria-label="$t('common.play')"
            >
              <i class="fas fa-chevron-right text-sm"></i>
            </button>
          </div>
        </section>

        <!-- 🌟 Haftanın Spotlight Sinematik Billboardu -->
        <section v-if="spotlightMovie" id="spotlight-section" class="my-10 relative rounded-3xl overflow-hidden border border-white/15 bg-gradient-to-r from-black via-gray-950 to-black shadow-2xl group">
          <!-- Dynamic Backdrop with Cinema Vignette -->
          <div class="absolute inset-0 z-0">
            <img 
              :src="spotlightMovie.backdrop_url || spotlightMovie.backdrop_path || spotlightMovie.poster_url" 
              :alt="spotlightMovie.title" 
              class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700 opacity-40 sm:opacity-50" 
            />
            <div class="absolute inset-0 bg-gradient-to-r from-[#07090e] via-[#07090e]/85 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#07090e] via-transparent to-transparent"></div>
          </div>

          <!-- Billboard Content -->
          <div class="relative z-10 p-6 sm:p-10 lg:p-14 max-w-2xl flex flex-col justify-center min-h-[360px] sm:min-h-[420px]">
            <div class="flex flex-wrap items-center gap-2.5 mb-3">
              <span class="px-3 py-1 rounded-full bg-gradient-to-r from-amber-500 to-amber-600 text-black font-black text-[11px] tracking-wider uppercase shadow-lg shadow-amber-500/30 flex items-center gap-1.5">
                <i class="fas fa-crown text-[10px]"></i>
                <span>{{ $t('home.spotlight_badge') }}</span>
              </span>
              <span class="px-2.5 py-0.5 rounded bg-black/60 border border-white/20 text-xs font-bold text-gray-300">
                4K ULTRA HD
              </span>
              <span class="px-2.5 py-0.5 rounded bg-black/60 border border-white/20 text-xs font-bold text-gray-300">
                {{ spotlightMovie.release_date ? spotlightMovie.release_date.substring(0, 4) : '2025' }}
              </span>
            </div>

            <h3 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-none mb-3 drop-shadow-lg">
              {{ spotlightMovie.title }}
            </h3>

            <div class="flex items-center gap-3 text-sm mb-4">
              <span class="flex items-center gap-1 text-amber-400 font-extrabold">
                <i class="fas fa-star"></i>
                <span>{{ spotlightMovie.vote_average || '8.8' }}</span>
                <span class="text-gray-400 text-xs font-normal">/ 10</span>
              </span>
              <span class="text-gray-400">•</span>
              <span class="text-emerald-400 font-bold text-xs">{{ $t('home.match') }}</span>
              <span v-if="spotlightMovie.runtime" class="text-gray-400 text-xs">• {{ spotlightMovie.runtime }} {{ $t('home.minutes') }}</span>
            </div>

            <p class="text-gray-300 text-xs sm:text-sm line-clamp-3 mb-6 leading-relaxed">
              {{ spotlightMovie.overview || $t('home.spotlight_critics') }}
            </p>

            <div class="flex flex-wrap items-center gap-3">
              <Link 
                :href="`/movie/${spotlightMovie.slug}`"
                class="px-6 py-3 rounded-xl bg-netflix hover:bg-red-700 text-white font-black text-sm transition-all duration-300 shadow-xl shadow-netflix/40 hover:scale-105 flex items-center gap-2 cursor-pointer"
              >
                <i class="fas fa-play text-xs"></i>
                <span>{{ $t('home.watch_now') }}</span>
              </Link>
              <Link 
                :href="`/movie/${spotlightMovie.slug}`"
                class="px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-sm transition-all backdrop-blur-md flex items-center gap-2 cursor-pointer"
              >
                <i class="fas fa-circle-info"></i>
                <span>{{ $t('home.details') }}</span>
              </Link>
            </div>
          </div>
        </section>

        <!-- Popular TV Shows Shelf with Smart Chevrons -->
        <section v-if="popularShows && popularShows.length" id="shelf-pop-shows" class="home-v2-section" aria-labelledby="shelf-pop-shows-heading">
          <div class="home-v2-heading">
            <h2 id="shelf-pop-shows-heading">{{ $t('home.popular_tv') }}</h2>
            <Link href="/tv-shows">{{ $t('home.view_all') }} <i class="fas fa-chevron-right"></i></Link>
          </div>

          <div class="relative group/shelf">
            <button 
              type="button" 
              @click="scrollShelf(popShowsRowRef, 'left')"
              class="hidden md:flex absolute -left-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/80 hover:bg-netflix border border-white/20 hover:border-netflix text-white shadow-2xl items-center justify-center opacity-0 group-hover/shelf:opacity-100 transition-all duration-300 hover:scale-110 backdrop-blur-md cursor-pointer"
              :aria-label="$t('common.back')"
            >
              <i class="fas fa-chevron-left text-sm"></i>
            </button>

            <div ref="popShowsRowRef" class="home-v2-shelf scroll-smooth">
              <article 
                v-for="show in popularShows" 
                :key="show.id"
                class="home-v2-content-card"
              >
                <Link :href="`/tv-show/${show.slug}`" class="home-v2-content-image">
                  <img :src="show.backdrop_url || show.backdrop_path || show.poster_url" :alt="show.title" loading="lazy" />
                  <span class="home-v2-card-overlay"></span>
                  <span class="home-v2-card-play"><i class="fas fa-play"></i></span>
                  <span class="home-v2-card-quality">HD</span>
                </Link>
                <div class="home-v2-card-copy">
                  <Link :href="`/tv-show/${show.slug}`">{{ show.title }}</Link>
                  <div>
                    <span>{{ show.first_air_date ? show.first_air_date.substring(0, 4) : '2024' }}</span>
                    <span><i class="fas fa-star"></i>{{ show.vote_average }}</span>
                  </div>
                </div>
              </article>
            </div>

            <button 
              type="button" 
              @click="scrollShelf(popShowsRowRef, 'right')"
              class="hidden md:flex absolute -right-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/80 hover:bg-netflix border border-white/20 hover:border-netflix text-white shadow-2xl items-center justify-center opacity-0 group-hover/shelf:opacity-100 transition-all duration-300 hover:scale-110 backdrop-blur-md cursor-pointer"
              :aria-label="$t('common.play')"
            >
              <i class="fas fa-chevron-right text-sm"></i>
            </button>
          </div>
        </section>

        <!-- Popüler Anime Dünyası Dedicated Shelf -->
        <!-- Popüler Anime Dünyası Dedicated Shelf -->
        <section v-if="animeList && animeList.length" id="anime-section" class="home-v2-section mb-10" aria-labelledby="shelf-anime-heading">
          <div class="home-v2-heading">
            <div class="flex items-center gap-3">
              <span class="px-2.5 py-1 rounded-lg bg-pink-500/10 border border-pink-500/25 text-pink-400 font-bold text-xs tracking-wider flex items-center gap-1.5">
                <span>ANIME</span>
                <span class="text-[10px] opacity-70">アニメ</span>
              </span>
              <h2 id="shelf-anime-heading" class="text-xl sm:text-2xl font-black text-white tracking-tight">{{ $t('home.anime_hub') }}</h2>
            </div>
            <Link href="/anime" class="text-xs font-bold text-pink-400 hover:text-pink-300 flex items-center gap-1.5 group transition">
              <span>{{ $t('home.view_all') }}</span>
              <i class="fas fa-chevron-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
            </Link>
          </div>

          <div class="relative group/shelf">
            <button 
              type="button" 
              @click="scrollShelf(animeRowRef, 'left')"
              class="hidden md:flex absolute -left-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/80 hover:bg-pink-600 border border-white/20 hover:border-pink-500 text-white shadow-2xl items-center justify-center opacity-0 group-hover/shelf:opacity-100 transition-all duration-300 hover:scale-110 backdrop-blur-md cursor-pointer"
              :aria-label="$t('common.back')"
            >
              <i class="fas fa-chevron-left text-sm"></i>
            </button>

            <div ref="animeRowRef" class="home-v2-shelf scroll-smooth">
              <article 
                v-for="anime in animeList" 
                :key="anime.id"
                class="home-v2-content-card group"
              >
                <Link :href="`/tv-show/${anime.slug}`" class="home-v2-content-image relative overflow-hidden rounded-xl">
                  <img :src="anime.backdrop_url || anime.backdrop_path || anime.poster_url" :alt="anime.title" loading="lazy" />
                  <span class="home-v2-card-overlay"></span>
                  <span class="home-v2-card-play !bg-pink-600 shadow-lg shadow-pink-600/50"><i class="fas fa-play"></i></span>
                  <span class="home-v2-card-quality border-pink-500/50 text-pink-300">SUB / DUB</span>
                  <span v-if="anime.number_of_episodes" class="absolute top-2 left-2 px-1.5 py-0.5 rounded bg-black/80 backdrop-blur-sm text-[9px] font-extrabold text-pink-400 border border-pink-500/30">
                    {{ anime.number_of_episodes }} {{ $t('home.episodes') }}
                  </span>
                </Link>
                <div class="home-v2-card-copy">
                  <Link :href="`/tv-show/${anime.slug}`" class="hover:!text-pink-400 transition-colors">{{ anime.title }}</Link>
                  <div class="flex items-center justify-between">
                    <span class="text-gray-400 text-[11px]">{{ anime.first_air_date ? anime.first_air_date.substring(0, 4) : '2024' }}</span>
                    <span class="text-amber-400 text-[11px] font-bold flex items-center gap-1">
                      <i class="fas fa-star text-[9px]"></i>{{ anime.vote_average }}
                    </span>
                  </div>
                </div>
              </article>
            </div>

            <button 
              type="button" 
              @click="scrollShelf(animeRowRef, 'right')"
              class="hidden md:flex absolute -right-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/80 hover:bg-pink-600 border border-white/20 hover:border-pink-500 text-white shadow-2xl items-center justify-center opacity-0 group-hover/shelf:opacity-100 transition-all duration-300 hover:scale-110 backdrop-blur-md cursor-pointer"
              :aria-label="$t('common.play')"
            >
              <i class="fas fa-chevron-right text-sm"></i>
            </button>
          </div>
        </section>

        <!-- Latest Movies Shelf with Smart Chevrons -->
        <section v-if="latestMovies && latestMovies.length" class="home-v2-section" aria-labelledby="shelf-latest-movies-heading">
          <div class="home-v2-heading">
            <h2 id="shelf-latest-movies-heading">{{ $t('home.new_releases') }}</h2>
            <Link href="/movies?sort=newest">{{ $t('home.view_all') }} <i class="fas fa-chevron-right"></i></Link>
          </div>

          <div class="relative group/shelf">
            <button 
              type="button" 
              @click="scrollShelf(latestMoviesRowRef, 'left')"
              class="hidden md:flex absolute -left-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/80 hover:bg-netflix border border-white/20 hover:border-netflix text-white shadow-2xl items-center justify-center opacity-0 group-hover/shelf:opacity-100 transition-all duration-300 hover:scale-110 backdrop-blur-md cursor-pointer"
              :aria-label="$t('common.back')"
            >
              <i class="fas fa-chevron-left text-sm"></i>
            </button>

            <div ref="latestMoviesRowRef" class="home-v2-shelf scroll-smooth">
              <article 
                v-for="movie in latestMovies" 
                :key="movie.id"
                class="home-v2-content-card"
              >
                <Link :href="`/movie/${movie.slug}`" class="home-v2-content-image">
                  <img :src="movie.backdrop_url || movie.backdrop_path || movie.poster_url" :alt="movie.title" loading="lazy" />
                  <span class="home-v2-card-overlay"></span>
                  <span class="home-v2-card-play"><i class="fas fa-play"></i></span>
                  <span class="home-v2-card-quality">HD</span>
                </Link>
                <div class="home-v2-card-copy">
                  <Link :href="`/movie/${movie.slug}`">{{ movie.title }}</Link>
                  <div>
                    <span>{{ movie.release_date ? movie.release_date.substring(0, 4) : '2025' }}</span>
                    <span><i class="fas fa-star"></i>{{ movie.vote_average }}</span>
                  </div>
                </div>
              </article>
            </div>

            <button 
              type="button" 
              @click="scrollShelf(latestMoviesRowRef, 'right')"
              class="hidden md:flex absolute -right-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/80 hover:bg-netflix border border-white/20 hover:border-netflix text-white shadow-2xl items-center justify-center opacity-0 group-hover/shelf:opacity-100 transition-all duration-300 hover:scale-110 backdrop-blur-md cursor-pointer"
              :aria-label="$t('common.play')"
            >
              <i class="fas fa-chevron-right text-sm"></i>
            </button>
          </div>
        </section>

        <!-- Latest TV Shows Shelf with Smart Chevrons -->
        <section v-if="latestShows && latestShows.length" class="home-v2-section home-v2-last-section" aria-labelledby="shelf-latest-shows-heading">
          <div class="home-v2-heading">
            <h2 id="shelf-latest-shows-heading">{{ $t('home.popular_tv') }}</h2>
            <Link href="/tv-shows?sort=newest">{{ $t('home.view_all') }} <i class="fas fa-chevron-right"></i></Link>
          </div>

          <div class="relative group/shelf">
            <button 
              type="button" 
              @click="scrollShelf(latestShowsRowRef, 'left')"
              class="hidden md:flex absolute -left-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/80 hover:bg-netflix border border-white/20 hover:border-netflix text-white shadow-2xl items-center justify-center opacity-0 group-hover/shelf:opacity-100 transition-all duration-300 hover:scale-110 backdrop-blur-md cursor-pointer"
              :aria-label="$t('common.back')"
            >
              <i class="fas fa-chevron-left text-sm"></i>
            </button>

            <div ref="latestShowsRowRef" class="home-v2-shelf scroll-smooth">
              <article 
                v-for="show in latestShows" 
                :key="show.id"
                class="home-v2-content-card"
              >
                <Link :href="`/tv-show/${show.slug}`" class="home-v2-content-image">
                  <img :src="show.backdrop_url || show.backdrop_path || show.poster_url" :alt="show.title" loading="lazy" />
                  <span class="home-v2-card-overlay"></span>
                  <span class="home-v2-card-play"><i class="fas fa-play"></i></span>
                  <span class="home-v2-card-quality">HD</span>
                </Link>
                <div class="home-v2-card-copy">
                  <Link :href="`/tv-show/${show.slug}`">{{ show.title }}</Link>
                  <div>
                    <span>{{ show.first_air_date ? show.first_air_date.substring(0, 4) : '2024' }}</span>
                    <span><i class="fas fa-star"></i>{{ show.vote_average }}</span>
                  </div>
                </div>
              </article>
            </div>

            <button 
              type="button" 
              @click="scrollShelf(latestShowsRowRef, 'right')"
              class="hidden md:flex absolute -right-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-black/80 hover:bg-netflix border border-white/20 hover:border-netflix text-white shadow-2xl items-center justify-center opacity-0 group-hover/shelf:opacity-100 transition-all duration-300 hover:scale-110 backdrop-blur-md cursor-pointer"
              :aria-label="$t('common.play')"
            >
              <i class="fas fa-chevron-right text-sm"></i>
            </button>
          </div>
        </section>

        <!-- Canlı Spor Müsabakaları & Canlı Ticker -->
        <section v-if="liveSports && liveSports.length" id="sports-section" class="home-v2-section mt-10 mb-12" aria-labelledby="shelf-sports-heading">
          <div class="home-v2-heading">
            <div class="flex items-center gap-2.5">
              <span class="flex h-2.5 w-2.5 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
              </span>
              <h2 id="shelf-sports-heading" class="text-xl sm:text-2xl font-black text-white tracking-tight">{{ $t('home.live_sports') }}</h2>
            </div>
            <Link href="/sports" class="text-xs font-bold text-red-400 hover:text-white flex items-center gap-1.5 group transition">
              <span>{{ $t('home.view_all') }}</span>
              <i class="fas fa-chevron-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
            </Link>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mt-4">
            <div 
              v-for="sport in liveSports" 
              :key="sport.id"
              class="bg-gray-950/80 rounded-2xl p-4 border border-white/10 hover:border-red-500/50 hover:shadow-xl hover:shadow-red-950/30 transition-all duration-300 flex flex-col justify-between group"
            >
              <div>
                <!-- Top Status Bar -->
                <div class="flex items-center justify-between text-xs mb-3">
                  <span class="px-2 py-0.5 rounded bg-white/10 text-gray-300 font-bold uppercase text-[10px] tracking-wider">
                    {{ sport.league }}
                  </span>
                  <span v-if="sport.is_live" class="px-2 py-0.5 rounded-full bg-red-500/20 border border-red-500/40 text-red-400 font-extrabold flex items-center gap-1.5 text-[10px] animate-pulse">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    <span>{{ $t('home.live_badge') }}</span>
                  </span>
                  <span v-else class="text-gray-400 text-[11px] font-medium flex items-center gap-1">
                    <i class="far fa-clock text-[10px]"></i>
                    <span>{{ sport.match_time || $t('home.today') }}</span>
                  </span>
                </div>

                <!-- Match Title -->
                <h4 class="font-bold text-sm text-white group-hover:text-red-400 transition-colors line-clamp-1 mb-3">
                  {{ sport.title }}
                </h4>

                <!-- Teams Display -->
                <div class="flex items-center justify-between p-3 rounded-xl bg-white/[0.03] border border-white/5 my-2">
                  <div class="flex items-center gap-2 min-w-0 flex-1">
                    <div class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0">
                      {{ sport.team_home ? sport.team_home.substring(0, 1).toUpperCase() : 'A' }}
                    </div>
                    <span class="text-xs font-semibold text-gray-200 truncate">{{ sport.team_home }}</span>
                  </div>

                  <span class="text-[11px] font-bold text-gray-500 uppercase px-2">
                    vs
                  </span>

                  <div class="flex items-center gap-2 min-w-0 flex-1 justify-end">
                    <span class="text-xs font-semibold text-gray-200 truncate text-right">{{ sport.team_away }}</span>
                    <div class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0">
                      {{ sport.team_away ? sport.team_away.substring(0, 1).toUpperCase() : 'B' }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer Action -->
              <div class="pt-3 mt-2 border-t border-white/5 flex justify-between items-center">
                <span class="text-[11px] text-gray-400 flex items-center gap-1">
                  <i class="fas fa-signal text-[9px] text-emerald-400"></i>
                  <span>{{ sport.status || $t('home.hd_quality') }}</span>
                </span>
                <Link 
                  :href="`/sports?watch=${sport.slug}`"
                  class="px-3.5 py-1.5 bg-netflix hover:bg-red-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-netflix/30 hover:scale-105 flex items-center gap-1.5 cursor-pointer"
                >
                  <i class="fas fa-play text-[9px]"></i>
                  <span>{{ sport.is_live ? $t('home.watch_now') : $t('home.details') }}</span>
                </Link>
              </div>
            </div>
          </div>
        </section>
      </div>

      <!-- Floating "Ne İzlesem?" Subtle Pill -->
      <button 
        type="button"
        @click="showRandomModal = true"
        class="fixed bottom-20 sm:bottom-8 right-4 sm:right-8 z-30 flex items-center gap-2 px-3.5 py-2 rounded-full bg-zinc-950/90 hover:bg-zinc-900 border border-white/10 hover:border-netflix/50 text-zinc-300 hover:text-white text-xs font-semibold shadow-xl shadow-black/80 backdrop-blur-md transition-all duration-200 group cursor-pointer"
        :aria-label="$t('home.surprise_me')"
        :title="$t('home.surprise_me_desc')"
      >
        <svg class="w-3.5 h-3.5 text-netflix transition-transform duration-300 group-hover:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3L12 3z"/>
        </svg>
        <span class="tracking-wide hidden sm:inline">{{ $t('home.surprise_me') }}</span>
      </button>

      <!-- 🎲 Random Pick Modal Component -->
      <RandomPickModal 
        :is-open="showRandomModal" 
        @close="showRandomModal = false" 
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import HeroBanner from '@/Components/HeroBanner.vue';
import RandomPickModal from '@/Components/RandomPickModal.vue';
import AdSlot from '@/Components/AdSlot.vue';
import { useI18n } from '@/Composables/useI18n';

const { t } = useI18n();

defineProps({
  heroMovies: Array,
  continueWatching: Array,
  trending: Array,
  popularMovies: Array,
  popularShows: Array,
  latestMovies: Array,
  latestShows: Array,
  animeList: Array,
  liveSports: Array,
  curatedCollections: Array,
  spotlightMovie: Object,
});

// Modal state
const showRandomModal = ref(false);

// Active filter state
const activeFilter = ref('all');

// Studios toggle
const showAllStudios = ref(false);

// Carousel refs for smooth scroll chevrons
const trendingRowRef = ref(null);
const popMoviesRowRef = ref(null);
const popShowsRowRef = ref(null);
const animeRowRef = ref(null);
const latestMoviesRowRef = ref(null);
const latestShowsRowRef = ref(null);

const scrollShelf = (shelfRef, direction) => {
  if (!shelfRef) return;
  const scrollAmount = direction === 'left' ? -650 : 650;
  shelfRef.scrollBy({ left: scrollAmount, behavior: 'smooth' });
};

const scrollToSection = (id) => {
  const el = document.getElementById(id);
  if (el) {
    const yOffset = -80;
    const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset;
    window.scrollTo({ top: y, behavior: 'smooth' });
  }
};

const filterOptions = computed(() => [
  { id: 'all', label: t('home.genres_all'), action: () => window.scrollTo({ top: 0, behavior: 'smooth' }) },
  { id: 'trending', label: t('home.top_10_title'), action: () => scrollToSection('trending-section') },
  { id: 'spotlight', label: t('home.spotlight_badge'), action: () => scrollToSection('spotlight-section') },
  { id: 'pop-movies', label: t('home.popular_movies'), action: () => scrollToSection('shelf-pop-movies') },
  { id: 'pop-shows', label: t('home.popular_tv'), action: () => scrollToSection('shelf-pop-shows') },
  { id: 'anime', label: t('home.anime_hub'), action: () => scrollToSection('anime-section') },
  { id: 'sports', label: t('home.live_sports'), action: () => scrollToSection('sports-section') },
]);

const handleFilterClick = (opt) => {
  activeFilter.value = opt.id;
  if (opt.action) {
    opt.action();
  }
};

const initialStudios = [
  { name: 'ABC', logo: 'https://image.tmdb.org/t/p/w154/2uy2ZWcplrSObIyt4x0Y9rkG6qO.png', light: false },
  { name: 'Acorn TV', logo: 'https://image.tmdb.org/t/p/w154/dwHtgdgA2G69Anoq1zWxcrzYUGi.png', light: true },
  { name: 'AMC', logo: 'https://image.tmdb.org/t/p/w154/pmvRmATOCaDykE6JrVoeYxlFHw3.png', light: true },
  { name: 'Apple TV', logo: 'https://image.tmdb.org/t/p/w154/bngHRFi794mnMq34gfVcm9nDxN1.png', light: true },
  { name: 'BBC America', logo: 'https://image.tmdb.org/t/p/w154/5sFjaa1lYQgcrfYHScn2GaezmN.png', light: true },
  { name: 'BBC One', logo: 'https://image.tmdb.org/t/p/w154/uJjcCg3O4DMEjM0xtno9OWFciRP.png', light: false },
];

const extraStudios = [
  { name: 'CBS', logo: 'https://image.tmdb.org/t/p/w154/wju8KhOUsR5y4bH9p3Jc50hhaLO.png', light: true },
  { name: 'CBS All Access', logo: 'https://image.tmdb.org/t/p/w154/rSWD2WoY26KJmOw7QHvZjC5OEWG.png', light: true },
  { name: 'Disney+', logo: 'https://image.tmdb.org/t/p/w154/1edZOYAfoyZyZ3rklNSiUpXX30Q.png', light: false },
  { name: 'FOX', logo: 'https://image.tmdb.org/t/p/w154/1DSpHrWyOORkL9N2QHX7Adt31mQ.png', light: true },
  { name: 'HBO', logo: 'https://image.tmdb.org/t/p/w154/tuomPhY2UtuPTqqFnKMVHvSb724.png', light: true },
  { name: 'Hulu', logo: 'https://image.tmdb.org/t/p/w154/pqUTCleNUiTLAVlelGxUgWn1ELh.png', light: false },
  { name: 'NBC', logo: 'https://image.tmdb.org/t/p/w154/cm111bsDVlYaC1foL0itvEI4yLG.png', light: true },
  { name: 'Netflix', logo: 'https://image.tmdb.org/t/p/w154/wwemzKWzjKYJFfCeiB57q3r4Bcm.png', light: false },
  { name: 'Prime Video', logo: 'https://image.tmdb.org/t/p/w154/w7HfLNm9CWwRmAMU58udl2L7We7.png', light: false },
  { name: 'Showtime', logo: 'https://image.tmdb.org/t/p/w154/Allse9kbjiP6ExaQrnSpIhkurEi.png', light: false },
  { name: 'STARZ', logo: 'https://image.tmdb.org/t/p/w154/qx3Y9LCaK4mq1ykFuDIfjshlo3U.png', light: false },
  { name: 'The CW', logo: 'https://image.tmdb.org/t/p/w154/hEpcdJ4O6eitG9ADSnDXNUrlovS.png', light: false },
  { name: 'YouTube Premium', logo: 'https://image.tmdb.org/t/p/w154/9nCeig9m4R1HoDJYBZnt9flureo.png', light: true },
];

const filterByStudio = (studioName) => {
  router.visit(`/search?q=${encodeURIComponent(studioName)}`);
};
</script>

<style scoped>
/* 3D Netflix-Style Top 10 Typography */
.top10-num {
  color: #07090e;
  -webkit-text-stroke: 4px #4b5563;
  text-shadow: 0 4px 15px rgba(0, 0, 0, 0.9);
  font-family: 'Bebas Neue', ui-sans-serif, system-ui, sans-serif;
  letter-spacing: -2px;
  display: block;
  transition: all 0.3s ease;
}

.group:hover .top10-num {
  -webkit-text-stroke: 4px #e50914;
  color: #1a0305;
  filter: drop-shadow(0 0 16px rgba(229, 9, 20, 0.7));
}

/* Scrollbar Hiding Utility */
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
