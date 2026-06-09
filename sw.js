// ProSensia Service Worker v1 — offline-capable PWA
const CACHE = 'prosensia-v1';
const STATIC = [
  '/assets/css/style.css',
  '/assets/css/characters.css',
  '/assets/js/characters.js',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css',
];

// Install: cache static assets
self.addEventListener('install', e => {
  self.skipWaiting();
  e.waitUntil(
    caches.open(CACHE).then(c => c.addAll(STATIC).catch(() => {}))
  );
});

// Activate: clean old caches
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

// Fetch: cache-first for static, network-first for PHP pages
self.addEventListener('fetch', e => {
  const url = new URL(e.request.url);

  // Skip non-GET, cross-origin, and POST requests
  if (e.request.method !== 'GET') return;
  if (!url.origin.startsWith(self.location.origin.slice(0,10)) && !url.hostname.includes('cdn.jsdelivr')) return;

  // Network-first for PHP pages (always fresh data)
  if (url.pathname.endsWith('.php') || url.pathname === '/') {
    e.respondWith(
      fetch(e.request)
        .then(r => { if (r.ok) { const clone = r.clone(); caches.open(CACHE).then(c=>c.put(e.request,clone)); } return r; })
        .catch(() => caches.match(e.request).then(r => r || new Response('<h2>You are offline</h2><p>Please reconnect to use ProSensia.</p>', {headers:{'Content-Type':'text/html'}})))
    );
    return;
  }

  // Cache-first for static assets (CSS, JS, fonts, images)
  e.respondWith(
    caches.match(e.request).then(cached => {
      if (cached) return cached;
      return fetch(e.request).then(r => {
        if (r.ok && r.type !== 'opaque') {
          const clone = r.clone();
          caches.open(CACHE).then(c => c.put(e.request, clone));
        }
        return r;
      });
    })
  );
});
