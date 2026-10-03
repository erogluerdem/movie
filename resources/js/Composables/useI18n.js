import { ref, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import tr from '../locales/tr.json';
import en from '../locales/en.json';

const dictionaries = {
  tr,
  en,
};

function resolveInitialLocale() {
  if (typeof window !== 'undefined') {
    try {
      const stored = localStorage.getItem('movie_locale');
      if (stored === 'tr' || stored === 'en') {
        return stored;
      }
      const match = document.cookie.match(/(?:^|;\s*)locale=([^;]+)/);
      if (match && (match[1] === 'tr' || match[1] === 'en')) {
        return match[1];
      }
    } catch (e) {}
  }
  return 'tr';
}

// Global reactive reference shared across the entire Vue instance
export const currentLocale = ref(resolveInitialLocale());

/**
 * Resolve nested object property by dot-notation path
 */
function getNestedValue(obj, path) {
  if (!obj || !path) return undefined;
  return path.split('.').reduce((prev, curr) => (prev && prev[curr] !== undefined ? prev[curr] : undefined), obj);
}

/**
 * Replace placeholders like {name} or :name in a string
 */
function interpolate(text, replacements = {}) {
  if (typeof text !== 'string') return text;
  return Object.keys(replacements).reduce((acc, key) => {
    const val = replacements[key];
    return acc
      .replace(new RegExp(`\\{${key}\\}`, 'g'), val)
      .replace(new RegExp(`:${key}\\b`, 'g'), val);
  }, text);
}

/**
 * Pure translation function for use in template or code
 */
export function translate(key, locale = 'tr', replacements = {}) {
  const targetLocale = locale === 'en' ? 'en' : 'tr';
  const activeDict = dictionaries[targetLocale] || dictionaries.tr;
  const fallbackDict = targetLocale === 'tr' ? dictionaries.en : dictionaries.tr;

  let val = getNestedValue(activeDict, key);
  if (val === undefined) {
    val = getNestedValue(fallbackDict, key);
  }

  if (val === undefined) {
    return key;
  }

  return interpolate(val, replacements);
}

export function useI18n() {
  try {
    const page = usePage();
    if (page?.props?.locale && (page.props.locale === 'tr' || page.props.locale === 'en')) {
      if (currentLocale.value !== page.props.locale) {
        currentLocale.value = page.props.locale;
      }
    }
  } catch (e) {
    // Outside setup context or test runner
  }

  const locale = computed(() => currentLocale.value);

  function t(key, replacements = {}) {
    return translate(key, currentLocale.value, replacements);
  }

  function setLocale(newLocale) {
    if (newLocale !== 'tr' && newLocale !== 'en') return;

    // 1. Immediately update global reactive state for instant 0ms UI update
    currentLocale.value = newLocale;

    // 2. Persist in localStorage and Cookie immediately
    if (typeof window !== 'undefined') {
      try {
        localStorage.setItem('movie_locale', newLocale);
        document.cookie = `locale=${newLocale};path=/;max-age=31536000;SameSite=Lax`;
      } catch (e) {}
    }

    // 3. Notify server via GET visit (bulletproof across all browsers & sessions, no CSRF issues)
    router.visit(`/locale/${newLocale}`, {
      preserveScroll: true,
      preserveState: false,
      replace: true,
    });
  }

  return {
    t,
    locale,
    setLocale,
    supportedLocales: [
      { code: 'tr', name: 'Türkçe', flag: '🇹🇷' },
      { code: 'en', name: 'English', flag: '🇬🇧' },
    ],
  };
}

export default useI18n;

