var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/offline',
    '/css/app.css',
    '/js/app.js',
    "/storage/01K0HWV90HTGCGR79TQ453502F.png",
    "/storage/01K0HYTXGYREDRJ9PVR032ZG9D.png",
    "/storage/01K0HYTXH1GK1B8R9GWTSJ48A2.png",
    "/storage/01K0HYTXH43ZVJF4CPAAZFYE8T.png",
    "/storage/01K0HYTXH6Z47DD3FMDPX9EWS4.png",
    "/storage/01K0HYTXH9GFF4T8178MM3Q6A1.png",
    "/storage/01K0HYTXHBV1BEFNW7ZH7YJQ72.png",
    "/storage/01K0HYTXHDSKM60HJ2KMBZFDBT.png"
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
    )
});

// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa-")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline');
            })
    )
});
