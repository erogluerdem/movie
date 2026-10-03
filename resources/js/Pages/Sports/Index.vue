<template>
  <AppLayout>
    <Head :title="$t('sports_section.page_title')" />

    <main class="sports-v2">
      <!-- Hidden Accessible Header -->
      <header class="sr-only">
        <h1>{{ $t('sports_section.sr_title') }}</h1>
        <p>{{ $t('sports_section.sr_desc') }}</p>
      </header>

      <!-- Hero Section -->
      <section class="sports-v2-hero" aria-label="Live sports overview">
        <div class="sports-v2-hero-media">
          <img src="/images/sports/background.jpg" alt="" fetchpriority="high" />
        </div>
        <div class="sports-v2-hero-shade"></div>

        <div class="sports-v2-shell sports-v2-hero-layout">
          <!-- Hero Copy -->
          <div class="sports-v2-hero-copy">
            <p class="sports-v2-kicker"><span></span> {{ $t('home.live_sports') }}</p>

            <a 
              :href="featuredItem?.slug ? `/sports/event/${featuredItem.slug}` : '#sports-live-now'" 
              class="sports-v2-featured-label"
              @click.prevent="featuredItem && selectMatch(featuredItem)"
            >
              <span class="sports-v2-live-dot"></span>
              <small>{{ $t('sports_section.on_now') }}</small>
              <strong>{{ featuredItem?.title || 'NFL Network' }}</strong>
              <i class="fas fa-chevron-right"></i>
            </a>

            <h1>{{ $t('sports_section.hero_title_1') }}<br><em>{{ $t('sports_section.hero_title_2') }}</em></h1>
            <p class="sports-v2-hero-intro">
              {{ $t('sports_section.hero_intro') }}
            </p>

            <div class="sports-v2-hero-meta">
              <span><i class="fas fa-circle"></i> {{ $t('sports_section.live_now', { count: liveMatchesCount }) }}</span>
              <span><i class="fas fa-signal"></i> {{ $t('sports_section.multiple_sources') }}</span>
              <span><b>HD</b> {{ $t('home.quality') || 'quality' }}</span>
            </div>

            <div class="sports-v2-hero-actions">
              <a 
                href="#sports-player-section" 
                class="sports-v2-primary-action"
                @click.prevent="featuredItem && selectMatch(featuredItem)"
              >
                <i class="fas fa-play"></i><span>{{ $t('sports_section.watch_live') }}</span>
              </a>
              <a href="#sports-live-now" class="sports-v2-secondary-action">
                <i class="fas fa-th-large"></i><span>{{ $t('sports_section.explore_sports') }}</span>
              </a>
            </div>
          </div>

          <!-- Live Spotlight -->
          <aside class="sports-v2-spotlight" :aria-label="$t('sports_section.live_spotlight')">
            <header>
              <div><span class="sports-v2-live-dot"></span><strong>{{ $t('sports_section.live_spotlight') }}</strong></div>
              <span>{{ $t('sports_section.on_now') }}</span>
            </header>

            <div class="sports-v2-spotlight-stage">
              <div 
                v-for="(item, idx) in spotlightList" 
                :key="item.slug || idx"
                class="sports-v2-spotlight-slide"
                :class="idx === currentSpotlightIdx ? 'is-active' : ''"
                :aria-hidden="idx !== currentSpotlightIdx"
                @click="selectMatch(item)"
                style="cursor: pointer;"
              >
                <img :src="item.image" :alt="item.title" loading="eager" />
                <div class="sports-v2-spotlight-shade"></div>
                <span class="sports-v2-spotlight-live"><i></i> {{ $t('home.live_badge') }}</span>
                <div class="sports-v2-spotlight-copy">
                  <p>{{ item.category }}</p>
                  <h2>{{ item.title }}</h2>
                  <div>
                    <span>{{ $t('home.watch_now') }}</span>
                    <small>{{ $t('sports_section.sources_count', { count: item.sourcesCount }) }}</small>
                  </div>
                </div>
              </div>
            </div>

            <footer>
              <div class="sports-v2-spotlight-dots">
                <button 
                  v-for="(_, idx) in spotlightList" 
                  :key="'dot-' + idx"
                  type="button" 
                  :class="idx === currentSpotlightIdx ? 'is-active' : ''"
                  @click="setSpotlight(idx)"
                  :aria-label="'Show spotlight ' + (idx + 1)"
                ></button>
              </div>
              <div>
                <button type="button" @click="prevSpotlight" aria-label="Previous live event">
                  <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" @click="nextSpotlight" aria-label="Next live event">
                  <i class="fas fa-chevron-right"></i>
                </button>
              </div>
            </footer>
          </aside>
        </div>

        <!-- Score Strip -->
        <div class="sports-v2-shell sports-v2-score-strip">
          <div class="sports-v2-score-status">
            <span class="sports-v2-live-dot"></span>
            <div><small>{{ $t('sports_section.live_centre') }}</small><strong>{{ $t('sports_section.events_count', { count: matches.length }) }}</strong></div>
          </div>

          <button 
            type="button" 
            class="sports-v2-score-refresh" 
            @click="refreshData"
            aria-label="Refresh sports data"
            title="Refresh sports data"
          >
            <i class="fas fa-sync-alt" :class="isRefreshing ? 'animate-spin' : ''"></i>
            <span>{{ $t('common.refresh') || 'Refresh' }}</span>
          </button>
        </div>
      </section>

      <!-- Main Catalogue Section -->
      <div class="sports-v2-shell sports-v2-catalogue">
        <!-- Active Video Player if selected -->
        <div v-if="currentActiveMatch" id="sports-player-section" class="mb-12 scroll-mt-24">
          <div class="bg-gray-900/90 border border-white/10 rounded-2xl p-4 sm:p-6 shadow-2xl backdrop-blur">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 border-b border-white/10 pb-4">
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-[11px] font-bold uppercase tracking-wider text-netflix">{{ currentActiveMatch.league }}</span>
                  <span v-if="currentActiveMatch.is_live" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-red-600/20 text-red-500 border border-red-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-ping"></span> {{ $t('home.live_badge') }}
                  </span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-white">{{ currentActiveMatch.title }}</h2>
              </div>
              <button 
                @click="currentActiveMatch = null" 
                class="self-start sm:self-auto text-xs text-gray-400 hover:text-white px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 transition border border-white/10 flex items-center gap-1.5"
              >
                <i class="fas fa-times"></i> {{ $t('sports_section.close_player') }}
              </button>
            </div>
            <VideoPlayer :sources="activeMatchSources" />
          </div>
        </div>

        <!-- Search and Category Filters -->
        <div class="sports-v2-tools">
          <label class="sports-v2-search">
            <i class="fas fa-search"></i>
            <span class="sr-only">{{ $t('sports_section.search_placeholder') }}</span>
            <input 
              type="search" 
              v-model="searchQuery" 
              :placeholder="$t('sports_section.search_placeholder')"
            />
          </label>

          <div class="sports-v2-category-wrap">
            <button type="button" @click="scrollCategoryRail(-1)" aria-label="Previous sports">
              <i class="fas fa-chevron-left"></i>
            </button>
            <nav id="sports-category-rail" class="sports-v2-categories" aria-label="Sports categories">
              <button 
                v-for="cat in categories" 
                :key="cat.slug"
                type="button" 
                :class="selectedCategory === cat.slug ? 'is-active' : ''"
                @click="selectedCategory = cat.slug"
              >
                {{ cat.name }}
              </button>
            </nav>
            <button type="button" @click="scrollCategoryRail(1)" aria-label="Next sports">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>

        <!-- Live Sports Networks Rail -->
        <section class="sports-v2-section sports-v2-channels" id="sports-live-now">
          <header class="sports-v2-section-head">
            <div>
              <span></span>
              <div><p>{{ $t('sports_section.always_on') }}</p><h2>{{ $t('sports_section.networks_title') }}</h2></div>
            </div>
            <div class="sports-v2-rail-actions">
              <button type="button" @click="scrollChannelRail(-1)" aria-label="Previous channels">
                <i class="fas fa-chevron-left"></i>
              </button>
              <button type="button" @click="scrollChannelRail(1)" aria-label="Next channels">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </header>

          <div id="sports-channel-rail" class="sports-v2-channel-rail">
            <article 
              v-for="ch in networkChannels" 
              :key="ch.slug" 
              class="sports-v2-card is-compact"
            >
              <a :href="`/sports/event/${ch.slug}`" @click.prevent="selectMatch(ch)">
                <div class="sports-v2-card-media">
                  <img :src="ch.image" :alt="ch.title" loading="lazy" />
                  <div class="sports-v2-card-shade"></div>
                  <span class="sports-v2-card-live"><i></i> {{ $t('home.live_badge') }}</span>
                  <span class="sports-v2-card-quality">HD</span>
                  <span class="sports-v2-card-play"><i class="fas fa-play"></i></span>
                </div>
                <div class="sports-v2-card-copy">
                  <p>{{ ch.category }}</p>
                  <h3>{{ ch.title }}</h3>
                  <div class="sports-v2-card-meta">
                    <span>{{ $t('sports_section.on_now') }}</span>
                    <small>{{ $t('sports_section.sources_count', { count: ch.sourcesCount }) }}</small>
                  </div>
                </div>
              </a>
            </article>
          </div>
        </section>

        <!-- Popular Matches & Upcoming Events Grid -->
        <section class="sports-v2-section">
          <header class="sports-v2-section-head">
            <div>
              <span></span>
              <div><p>{{ $t('sports_section.popular_title') }}</p><h2>{{ $t('sports_section.popular_title') }}</h2></div>
            </div>
            <small>{{ displayedMatches.length }} events</small>
          </header>

          <div v-if="displayedMatches.length > 0" class="sports-v2-grid">
            <article 
              v-for="match in displayedMatches" 
              :key="match.id" 
              class="sports-v2-card"
            >
              <a :href="`/sports/event/${match.slug}`" @click.prevent="selectMatch(match)">
                <div class="sports-v2-card-media">
                  <img 
                    :src="match.home_logo || '/images/sports/background.jpg'" 
                    :alt="match.title" 
                    loading="lazy" 
                  />
                  <div class="sports-v2-card-shade"></div>
                  <span v-if="match.is_live" class="sports-v2-card-live"><i></i> {{ $t('home.live_badge') }}</span>
                  <span v-else class="sports-v2-card-time">
                    <small>{{ $t('sports_section.starts') }}</small>
                    <strong>{{ match.match_time }}</strong>
                  </span>
                  <span class="sports-v2-card-quality">HD</span>
                  <span class="sports-v2-card-play"><i class="fas fa-play"></i></span>
                </div>
                <div class="sports-v2-card-copy">
                  <p>{{ match.league }}</p>
                  <h3>{{ match.title }}</h3>
                  <div class="sports-v2-card-meta">
                    <span>{{ match.team_home }} <b>vs</b> {{ match.team_away }}</span>
                    <small>{{ $t('sports_section.sources_count', { count: (match.stream_servers && match.stream_servers.length) || 1 }) }}</small>
                  </div>
                </div>
              </a>
            </article>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-16 bg-white/5 rounded-xl border border-white/10 my-8">
            <i class="fas fa-futbol text-4xl text-gray-600 mb-3 block"></i>
            <p class="text-gray-300 font-semibold">{{ $t('sports_section.no_matches') }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $t('sports_section.no_matches_desc') }}</p>
          </div>
        </section>

        <!-- Footer Notice -->
        <footer class="sports-v2-updated">
          <span class="sports-v2-live-dot"></span> Live catalogue updated just now
        </footer>
      </div>
    </main>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import VideoPlayer from '@/Components/VideoPlayer.vue';

const props = defineProps({
  matches: {
    type: Array,
    default: () => [],
  },
  activeMatch: {
    type: Object,
    default: null,
  },
  leagues: {
    type: Array,
    default: () => [],
  },
  currentLeague: {
    type: String,
    default: null,
  },
});

const currentActiveMatch = ref(props.activeMatch);
const searchQuery = ref('');
const selectedCategory = ref('all');
const isRefreshing = ref(false);
const currentSpotlightIdx = ref(0);

// Live Spotlight Data
const spotlightList = ref([
  {
    slug: 'ppv-nfl-network',
    title: 'NFL Network',
    category: 'American football',
    image: '/images/sports/nfl-network.jpg',
    sourcesCount: 1,
    league: 'American Football',
    stream_servers: [
      { name: 'Server 1 (HD)', url: 'https://multiembed.mov/?video_id=nfl-net-1' }
    ]
  },
  {
    slug: 'ppv-nfl-red-zone',
    title: 'NFL RedZone',
    category: 'American football',
    image: '/images/sports/nfl-redzone.jpg',
    sourcesCount: 2,
    league: 'American Football',
    stream_servers: [
      { name: 'Server 1 (HD)', url: 'https://multiembed.mov/?video_id=nfl-rz-1' },
      { name: 'Server 2', url: 'https://vidsrcme.ru' }
    ]
  },
  {
    slug: 'ppv-wwe-friday-night-smackdown',
    title: 'WWE Friday Night Smackdown',
    category: 'Wrestling',
    image: '/images/sports/wwe.jpg',
    sourcesCount: 1,
    league: 'Fight (UFC, Boxing)',
    stream_servers: [
      { name: 'Server 1 (HD)', url: 'https://multiembed.mov/?video_id=wwe-smackdown' }
    ]
  },
  {
    slug: 'ppv-wwe-nxt',
    title: 'WWE NXT',
    category: 'Wrestling',
    image: '/images/sports/wwe.jpg',
    sourcesCount: 1,
    league: 'Fight (UFC, Boxing)',
    stream_servers: [
      { name: 'Server 1 (HD)', url: 'https://multiembed.mov/?video_id=wwe-nxt' }
    ]
  },
]);

// 24/7 Live Network Channels
const networkChannels = ref([
  {
    slug: 'ppv-nfl-network',
    title: 'NFL Network',
    category: 'American football',
    image: '/images/sports/nfl-network.jpg',
    sourcesCount: 1,
    league: 'American Football',
    stream_servers: [
      { name: 'Server 1 (HD)', url: 'https://multiembed.mov/?video_id=nfl-net-1' }
    ]
  },
  {
    slug: 'ppv-nfl-red-zone',
    title: 'NFL RedZone',
    category: 'American football',
    image: '/images/sports/nfl-redzone.jpg',
    sourcesCount: 2,
    league: 'American Football',
    stream_servers: [
      { name: 'Server 1 (HD)', url: 'https://multiembed.mov/?video_id=nfl-rz-1' },
      { name: 'Server 2', url: 'https://vidsrcme.ru' }
    ]
  },
  {
    slug: 'ppv-wwe-friday-night-smackdown',
    title: 'WWE Friday Night Smackdown',
    category: 'Wrestling',
    image: '/images/sports/wwe.jpg',
    sourcesCount: 1,
    league: 'Fight (UFC, Boxing)',
    stream_servers: [
      { name: 'Server 1 (HD)', url: 'https://multiembed.mov/?video_id=wwe-smackdown' }
    ]
  },
  {
    slug: 'ppv-wwe-nxt',
    title: 'WWE NXT',
    category: 'Wrestling',
    image: '/images/sports/wwe.jpg',
    sourcesCount: 1,
    league: 'Fight (UFC, Boxing)',
    stream_servers: [
      { name: 'Server 1 (HD)', url: 'https://multiembed.mov/?video_id=wwe-nxt' }
    ]
  },
  {
    slug: 'admin-tennis-channel',
    title: 'Tennis Channel',
    category: 'Tennis',
    image: '/images/sports/tennis-channel.jpg',
    sourcesCount: 1,
    league: 'Tennis',
    stream_servers: [
      { name: 'Server 1 (HD)', url: 'https://multiembed.mov/?video_id=tennis-ch-1' }
    ]
  },
  {
    slug: 'admin-rally-tv',
    title: 'Rally TV',
    category: 'Motor sports',
    image: '/images/sports/background.jpg',
    sourcesCount: 1,
    league: 'Motor Sports',
    stream_servers: [
      { name: 'Server 1 (HD)', url: 'https://vidsrcme.ru' }
    ]
  }
]);

// Categories Rail
const categories = [
  { slug: 'all', name: 'All Sports' },
  { slug: 'basketball', name: 'Basketball' },
  { slug: 'football', name: 'Football' },
  { slug: 'american-football', name: 'American Football' },
  { slug: 'hockey', name: 'Hockey' },
  { slug: 'baseball', name: 'Baseball' },
  { slug: 'motor-sports', name: 'Motor Sports' },
  { slug: 'fight', name: 'Fight (UFC, Boxing)' },
  { slug: 'tennis', name: 'Tennis' },
  { slug: 'rugby', name: 'Rugby' },
  { slug: 'golf', name: 'Golf' },
  { slug: 'cricket', name: 'Cricket' },
  { slug: 'other', name: 'Other' },
];

const featuredItem = computed(() => spotlightList.value[0]);
const liveMatchesCount = computed(() => props.matches.filter(m => m.is_live).length);

const displayedMatches = computed(() => {
  let list = props.matches;
  if (selectedCategory.value !== 'all') {
    const targetCat = selectedCategory.value.toLowerCase().replace(/-/g, ' ');
    list = list.filter(m => {
      const lg = (m.league || '').toLowerCase();
      return lg.includes(targetCat) || targetCat.includes(lg);
    });
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(m => 
      (m.title && m.title.toLowerCase().includes(q)) ||
      (m.team_home && m.team_home.toLowerCase().includes(q)) ||
      (m.team_away && m.team_away.toLowerCase().includes(q)) ||
      (m.league && m.league.toLowerCase().includes(q))
    );
  }
  return list;
});

const activeMatchSources = computed(() => {
  if (!currentActiveMatch.value) return [];
  if (currentActiveMatch.value.stream_servers && currentActiveMatch.value.stream_servers.length > 0) {
    return currentActiveMatch.value.stream_servers;
  }
  return [
    { name: 'Server 1 (HD)', url: currentActiveMatch.value.stream_url || 'https://vidsrcme.ru' },
    { name: 'Server 2', url: 'https://multiembed.mov' }
  ];
});

const selectMatch = (match) => {
  // Find in props.matches or use provided object
  const found = props.matches.find(m => m.slug === match.slug) || match;
  currentActiveMatch.value = found;
  nextTick(() => {
    const el = document.getElementById('sports-player-section');
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' });
    }
  });
};

const setSpotlight = (idx) => {
  currentSpotlightIdx.value = idx;
};

const prevSpotlight = () => {
  currentSpotlightIdx.value = (currentSpotlightIdx.value - 1 + spotlightList.value.length) % spotlightList.value.length;
};

const nextSpotlight = () => {
  currentSpotlightIdx.value = (currentSpotlightIdx.value + 1) % spotlightList.value.length;
};

const scrollCategoryRail = (dir) => {
  const el = document.getElementById('sports-category-rail');
  if (el) el.scrollBy({ left: dir * 200, behavior: 'smooth' });
};

const scrollChannelRail = (dir) => {
  const el = document.getElementById('sports-channel-rail');
  if (el) el.scrollBy({ left: dir * 300, behavior: 'smooth' });
};

const refreshData = () => {
  isRefreshing.value = true;
  router.reload({
    only: ['matches'],
    onFinish: () => {
      isRefreshing.value = false;
    }
  });
};

onMounted(() => {
  // Auto rotate spotlight every 5s
  setInterval(() => {
    nextSpotlight();
  }, 5000);

  if (props.activeMatch) {
    nextTick(() => {
      const el = document.getElementById('sports-player-section');
      if (el) el.scrollIntoView({ behavior: 'smooth' });
    });
  }
});
</script>
