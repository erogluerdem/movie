const CACHE_NAME = 'movie-v3';
const STATIC_ASSETS = [
  '/',
  '/manifest.json',
  '/images/favicons/favicon_1771172753.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  // Only cache GET requests
  if (event.request.method !== 'GET') return;

  // Don't intercept API, admin, dashboard, or logos
  const url = new URL(event.request.url);
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/admin/') || url.pathname.startsWith('/dashboard/') || url.pathname.includes('/logos/')) {
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse;
      }
      return fetch(event.request).then((response) => {
        if (!response || response.status !== 200 || response.type !== 'basic') {
          return response;
        }
        const responseToCache = response.clone();
        caches.open(CACHE_NAME).then((cache) => {
          // Cache fonts or static assets
          if (url.pathname.startsWith('/images/') || url.pathname.startsWith('/build/')) {
            cache.put(event.request, responseToCache);
          }
        });
        return response;
      }).catch(() => {
        return caches.match('/');
      });
    })
  );
});
