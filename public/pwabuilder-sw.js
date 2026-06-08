const CACHE_NAME = 'smart-solar-irrigation-v2';
const SHELL_ASSETS = [
    '/offline.html',
    '/manifest.json',
    '/favicon.ico',
    '/icons/irrigation-icon-192.png',
    '/icons/irrigation-icon-512.png',
    '/icons/irrigation-icon.svg',
    '/icons/irrigation-icon-maskable.svg'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(SHELL_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => Promise.all(
            cacheNames
                .filter((cacheName) => cacheName !== CACHE_NAME)
                .map((cacheName) => caches.delete(cacheName))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match('/offline.html'))
        );

        return;
    }

    const requestUrl = new URL(event.request.url);

    if (requestUrl.origin !== self.location.origin) {
        return;
    }

    if (requestUrl.pathname.startsWith('/dashboard') || requestUrl.pathname.startsWith('/admin')) {
        event.respondWith(fetch(event.request));

        return;
    }

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(event.request).then((networkResponse) => {
                if (!networkResponse.ok) {
                    return networkResponse;
                }

                const responseClone = networkResponse.clone();

                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, responseClone);
                });

                return networkResponse;
            });
        })
    );
});
