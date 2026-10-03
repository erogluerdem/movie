<template>
  <section 
    v-if="items && items.length > 0" 
    class="home-v2-hero" 
    aria-label="Featured titles"
    @touchstart="onTouchStart"
    @touchend="onTouchEnd"
  >
    <!-- Backdrops Layer -->
    <div class="home-v2-backdrops" aria-hidden="true">
      <div 
        v-for="(item, idx) in items" 
        :key="'backdrop-' + item.id + '-' + idx"
        :class="['home-v2-backdrop', activeSlide === idx ? 'is-active' : '']"
        :data-home-backdrop="idx"
      >
        <img :src="item.backdrop_path || item.backdrop_url || item.poster_path" alt="" loading="eager" />
      </div>
      <div class="home-v2-backdrop-shade"></div>
    </div>

    <!-- Hero Stage (Card Track & Arrows) -->
    <div class="home-v2-hero-stage">
      <button 
        type="button" 
        class="home-v2-hero-arrow is-prev" 
        id="home-hero-prev" 
        aria-label="Previous featured title"
        @click="prevSlide"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="m15 18-6-6 6-6"/>
        </svg>
      </button>

      <div class="home-v2-hero-track" id="home-hero-track">
        <article 
          v-for="(item, idx) in items" 
          :key="'card-' + item.id + '-' + idx"
          :class="['home-v2-hero-card', activeSlide === idx ? 'is-active' : '']"
          :style="getCardStyle(idx)"
          :data-home-slide="idx"
          :data-item-id="item.id"
          :data-item-type="item.media_type || (item.first_air_date ? 'tv_show' : 'movie')"
          :data-feed-type="item.media_type || (item.first_air_date ? 'tv' : 'movie')"
          :data-feed-id="item.id"
          @click="onCardClick($event, idx)"
        >
          <Link 
            :href="getItemUrl(item)" 
            class="home-v2-hero-poster" 
            :aria-label="`Open ${item.title}`"
            :tabindex="activeSlide === idx ? 0 : -1"
          >
            <img :src="item.poster_path || item.poster_url" :alt="item.title" />
            <span class="home-v2-poster-vignette"></span>
          </Link>

          <div class="home-v2-hero-details">
            <h1>{{ item.title }}</h1>
            <div class="home-v2-hero-meta">
              <span class="home-v2-rating">
                <i class="fas fa-star"></i>{{ formatRating(item.vote_average) }}/10
              </span>
              <span>{{ getYear(item) }}</span>
              <span>{{ getDuration(item) }}</span>
              <span class="home-v2-quality">HD</span>
            </div>
            <div class="home-v2-hero-actions">
              <Link :href="getItemUrl(item)" class="home-v2-play touch-press">
                <i class="fas fa-play"></i><span class="whitespace-nowrap">{{ $t('common.play') }}</span>
              </Link>
              <button 
                type="button" 
                :class="['home-v2-list', 'touch-press', 'js-home-watchlist', savedMap[item.id] ? 'is-added' : '']"
                @click.stop="toggleWatchlist(item)"
              >
                <i :class="savedMap[item.id] ? 'fas fa-check' : 'fas fa-plus'"></i>
                <span class="whitespace-nowrap truncate">{{ savedMap[item.id] ? $t('nav.my_list') : $t('common.add_to_list') }}</span>
              </button>
            </div>
          </div>
        </article>
      </div>

      <button 
        type="button" 
        class="home-v2-hero-arrow is-next" 
        id="home-hero-next" 
        aria-label="Next featured title"
        @click="nextSlide"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="m9 18 6-6-6-6"/>
        </svg>
      </button>
    </div>

    <!-- Indicator Dots -->
    <div class="home-v2-hero-dots" aria-label="Featured title navigation">
      <button 
        v-for="(_, idx) in items" 
        :key="'dot-' + idx"
        type="button" 
        :class="[activeSlide === idx ? 'is-active' : '']" 
        :data-home-dot="idx" 
        :aria-label="`Show slide ${idx + 1}`"
        @click="goToSlide(idx)"
      ></button>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  }
});

const activeSlide = ref(0);
const savedMap = ref({});
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1200);
let heroTimer = null;

const compact = computed(() => windowWidth.value < 768);
const step = computed(() => (compact.value ? 176 : 236));

function circularOffset(index, active, total) {
  let offset = index - active;
  if (offset > total / 2) offset -= total;
  if (offset < -total / 2) offset += total;
  return offset;
}

const getCardStyle = (cardIndex) => {
  const total = props.items.length;
  if (!total) return {};

  const offset = circularOffset(cardIndex, activeSlide.value, total);
  const distance = Math.abs(offset);
  const isCompact = compact.value;
  const visible = isCompact ? distance <= 1 : distance <= 2;
  const stepVal = step.value;

  const scale = offset === 0 ? 1 : distance === 1 ? 0.78 : 0.66;
  const opacity = visible ? (offset === 0 ? '1' : distance === 1 ? '0.82' : '0.58') : '0';
  const zIndex = String(20 - distance);
  const pointerEvents = visible ? 'auto' : 'none';

  return {
    transform: `translateX(${offset * stepVal}px) scale(${scale})`,
    opacity: opacity,
    zIndex: zIndex,
    pointerEvents: pointerEvents,
  };
};

const goToSlide = (idx) => {
  if (!props.items.length) return;
  activeSlide.value = (idx + props.items.length) % props.items.length;
  restartTimer();
};

const nextSlide = () => {
  goToSlide(activeSlide.value + 1);
};

const prevSlide = () => {
  goToSlide(activeSlide.value - 1);
};

const onCardClick = (event, cardIndex) => {
  if (cardIndex !== activeSlide.value) {
    event.preventDefault();
    event.stopPropagation();
    goToSlide(cardIndex);
  }
};

const restartTimer = () => {
  clearInterval(heroTimer);
  if (props.items.length > 1) {
    heroTimer = setInterval(() => {
      goToSlide(activeSlide.value + 1);
    }, 6500);
  }
};

const getItemUrl = (item) => {
  const isTv = item.media_type === 'tv' || Boolean(item.first_air_date) || Boolean(item.number_of_seasons);
  const prefix = isTv ? '/tv-show/' : '/movie/';
  const rawSlug = item.slug || '';
  const slug = rawSlug.startsWith('watch-') ? rawSlug : `watch-${rawSlug}`;
  return `${prefix}${slug}`;
};

const formatRating = (rating) => {
  return Number(rating || 7.0).toFixed(1);
};

const getYear = (item) => {
  const d = item.release_date || item.first_air_date;
  return d ? String(d).substring(0, 4) : '2025';
};

const getDuration = (item) => {
  if (item.number_of_seasons) {
    return `${item.number_of_seasons} Season${item.number_of_seasons > 1 ? 's' : ''}`;
  }
  if (item.runtime) {
    return typeof item.runtime === 'string' && item.runtime.includes('min') ? item.runtime : `${item.runtime} min`;
  }
  return '120 min';
};

const toggleWatchlist = async (item) => {
  try {
    const isTv = item.media_type === 'tv' || Boolean(item.first_air_date) || Boolean(item.number_of_seasons);
    const type = isTv ? 'tv' : 'movie';
    const res = await axios.post('/api/watchlist/toggle', {
      media_type: type,
      media_id: item.id
    });
    savedMap.value[item.id] = res.data.in_watchlist;
  } catch (e) {
    console.error(e);
  }
};

// Touch swipe gestures
let touchStartX = 0;
let touchEndX = 0;

const onTouchStart = (e) => {
  if (e.changedTouches && e.changedTouches[0]) {
    touchStartX = e.changedTouches[0].screenX;
  }
};

const onTouchEnd = (e) => {
  if (e.changedTouches && e.changedTouches[0]) {
    touchEndX = e.changedTouches[0].screenX;
    if (touchStartX - touchEndX > 50) {
      nextSlide();
    } else if (touchEndX - touchStartX > 50) {
      prevSlide();
    }
  }
};

const handleResize = () => {
  windowWidth.value = window.innerWidth;
};

onMounted(() => {
  window.addEventListener('resize', handleResize);
  restartTimer();
});

onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
  clearInterval(heroTimer);
});
</script>

<style scoped>
:root {
  --netflix: #e50914;
  --home-line: rgba(255, 255, 255, .11);
}

.home-v2-hero {
  position: relative;
  height: 510px;
  overflow: hidden;
  border-bottom: 1px solid rgba(255, 255, 255, .11);
  isolation: isolate;
  background: #050607;
}

.home-v2-backdrops,
.home-v2-backdrop,
.home-v2-backdrop-shade {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}

.home-v2-backdrop {
  opacity: 0;
  transform: scale(1.04);
  transition: opacity .8s ease, transform 5s ease;
  pointer-events: none;
}

.home-v2-backdrop.is-active {
  opacity: .42;
  transform: scale(1);
}

.home-v2-backdrop img {
  width: 100%;
  height: 100%;
  -o-object-fit: cover;
  object-fit: cover;
  filter: saturate(.72) contrast(1.08);
}

.home-v2-backdrop-shade {
  background: linear-gradient(90deg, #050607, #0506078c 20%, #05060738 50%, #0506079e 82%, #050607),
              linear-gradient(0deg, #050607 0%, transparent 35%, rgba(0, 0, 0, .35) 100%);
  pointer-events: none;
}

.home-v2-hero-stage {
  position: relative;
  z-index: 2;
  width: min(1440px, 100%);
  height: 455px;
  margin: 0 auto;
}

.home-v2-hero-track {
  position: absolute;
  top: 22px;
  right: 0;
  bottom: 0;
  left: 0;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.home-v2-hero-card {
  position: absolute;
  top: 54px;
  left: 50%;
  width: 290px;
  height: 354px;
  margin-left: -145px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, .18);
  border-radius: 9px;
  background: #111;
  box-shadow: 0 24px 65px #000000a6;
  transition: transform .65s cubic-bezier(.22, 1, .36, 1), opacity .45s ease, border-color .3s ease, top .4s ease, height .4s ease;
  transform-origin: center;
  cursor: pointer;
  user-select: none;
}

.home-v2-hero-card.is-active {
  top: 0;
  height: 425px;
  border-color: #e50914;
  box-shadow: 0 0 0 1px rgba(229, 9, 20, .45), 0 28px 80px #000000c7;
  cursor: default;
}

.home-v2-hero-poster,
.home-v2-hero-poster img,
.home-v2-poster-vignette {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  display: block;
  width: 100%;
  height: 100%;
}

.home-v2-hero-poster img {
  -o-object-fit: cover;
  object-fit: cover;
}

.home-v2-poster-vignette {
  background: linear-gradient(to top, rgba(3, 4, 5, .98) 0%, rgba(3, 4, 5, .75) 22%, transparent 58%);
  opacity: 0;
  transition: opacity .3s ease;
}

.home-v2-hero-card.is-active .home-v2-poster-vignette {
  opacity: 1;
}

.home-v2-hero-details {
  position: absolute;
  z-index: 3;
  right: 12px;
  bottom: 13px;
  left: 12px;
  opacity: 0;
  transform: translateY(12px);
  transition: opacity .35s ease .15s, transform .35s ease .15s;
  pointer-events: none;
}

.home-v2-hero-card.is-active .home-v2-hero-details {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}

.home-v2-hero-details h1 {
  margin: 0 0 7px;
  overflow: hidden;
  color: #fff;
  font-size: 1.35rem;
  font-weight: 700;
  line-height: 1.1;
  text-overflow: ellipsis;
  white-space: nowrap;
  text-shadow: 0 2px 8px #000;
}

.home-v2-hero-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 22px;
  color: #d1d5db;
  font-size: .76rem;
}

.home-v2-rating {
  color: #facc15;
}

.home-v2-rating i {
  margin-right: 4px;
  font-size: .68rem;
}

.home-v2-quality {
  border: 1px solid rgba(255, 255, 255, .45);
  border-radius: 4px;
  color: #fff;
  font-size: .65rem;
  font-weight: 700;
  line-height: 1;
  padding: 4px 6px;
}

.home-v2-hero-actions {
  display: grid;
  grid-template-columns: 1.1fr .9fr;
  gap: 10px;
  margin-top: 11px;
}

.home-v2-play,
.home-v2-list {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  height: 40px;
  border-radius: 5px;
  color: #fff;
  font-size: .88rem;
  font-weight: 650;
  transition: filter .2s ease, background .2s ease, border-color .2s ease;
  text-decoration: none;
}

.home-v2-play {
  background: #e50914;
}

.home-v2-play:hover {
  color: #fff;
  filter: brightness(1.14);
}

.home-v2-list {
  border: 1px solid rgba(255, 255, 255, .18);
  background: #08090bd6;
  cursor: pointer;
}

.home-v2-list:hover,
.home-v2-list.is-added {
  border-color: #ffffff5c;
  background: #ffffff1f;
  color: #fff;
}

.home-v2-hero-arrow {
  position: absolute;
  z-index: 30;
  top: 50%;
  display: grid;
  width: 44px;
  height: 44px;
  place-items: center;
  border: 1px solid rgba(255, 255, 255, .17);
  border-radius: 999px;
  background: #090a0cad;
  color: #fff;
  transform: translateY(-50%);
  transition: border-color .2s ease, background .2s ease, transform .2s ease;
  cursor: pointer;
}

.home-v2-hero-arrow:hover {
  border-color: #e50914;
  background: #090a0ceb;
  transform: translateY(-50%) scale(1.06);
}

.home-v2-hero-arrow svg {
  width: 21px;
  height: 21px;
}

.home-v2-hero-arrow.is-prev {
  left: 5.5%;
}

.home-v2-hero-arrow.is-next {
  right: 5.5%;
}

.home-v2-hero-dots {
  position: absolute;
  z-index: 4;
  bottom: 13px;
  left: 50%;
  display: flex;
  gap: 10px;
  transform: translate(-50%);
}

.home-v2-hero-dots button {
  width: 8px;
  height: 8px;
  border-radius: 99px;
  background: #ffffff61;
  transition: width .25s ease, background .25s ease;
  cursor: pointer;
  border: none;
  padding: 0;
}

.home-v2-hero-dots button.is-active {
  width: 22px;
  background: #e50914;
}

@media (max-width: 767px) {
  .home-v2-hero {
    height: 440px;
    border-bottom: 0;
    box-shadow: 0 24px 55px #0000004d;
  }

  .home-v2-hero-stage {
    height: 400px;
  }

  .home-v2-hero-track {
    top: 15px;
  }

  .home-v2-hero-card {
    top: 40px;
    width: 224px;
    height: 302px;
    margin-left: -112px;
    border-color: transparent;
    box-shadow: inset 0 1px #ffffff0f, 0 24px 65px #000000a6;
  }

  .home-v2-hero-card.is-active {
    top: 0;
    height: 365px;
    border-color: transparent;
    box-shadow: inset 0 1px #ffffff12, 0 32px 85px #000000d1;
  }

  .home-v2-hero-details h1 {
    font-size: 1.05rem;
  }

  .home-v2-hero-meta {
    gap: 8px;
    font-size: .66rem;
  }

  .home-v2-play,
  .home-v2-list {
    height: 36px;
    font-size: .78rem;
  }

  .home-v2-hero-arrow {
    width: 38px;
    height: 38px;
    box-shadow: inset 0 1px #ffffff17, 0 10px 28px #00000085;
  }

  .home-v2-hero-arrow.is-prev {
    left: 12px;
  }

  .home-v2-hero-arrow.is-next {
    right: 12px;
  }
}
</style>
