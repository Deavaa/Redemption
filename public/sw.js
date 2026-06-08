// Redemption School Management System - Service Worker v3
// Provides offline capability, caching, and push notification support
// Performance-optimized with stale-while-revalidate for static assets

const CACHE_NAME = 'redemption-v4';
const STATIC_ASSETS = [
    './',
    './login',
    './manifest.json',
];

// Cache durations (in seconds)
const CACHE_DURATIONS = {
    html: 0,        // Never cache HTML from SW (let browser handle)
    css: 7 * 86400, // 1 week
    js: 7 * 86400,  // 1 week
    image: 30 * 86400, // 1 month
    font: 30 * 86400,  // 1 month
};

// Install event - cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch(err => {
                console.warn('[SW] Failed to cache some static assets:', err);
            });
        })
    );
    self.skipWaiting();
});

// Activate event - clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Determine asset type from URL
function getAssetType(url) {
    if (url.match(/\.(css)$/)) return 'css';
    if (url.match(/\.(js)$/)) return 'js';
    if (url.match(/\.(png|jpg|jpeg|gif|svg|webp|ico)$/)) return 'image';
    if (url.match(/\.(woff|woff2|ttf|eot)$/)) return 'font';
    return 'html';
}

// Check if URL is a CDN resource we want to cache
function isCDNResource(url) {
    return url.includes('cdn.jsdelivr.net') ||
           url.includes('cdnjs.cloudflare.com') ||
           url.includes('fonts.googleapis.com') ||
           url.includes('fonts.gstatic.com') ||
           url.includes('ui-avatars.com');
}

// Check if URL should be skipped (API calls, etc.)
function shouldSkip(url) {
    return url.includes('/api/') ||
           url.includes('/admin/attendance-api/') ||
           url.includes('/admin/transcript/api/') ||
           url.includes('/admin/keepalive') ||
           url.includes('/admin/session-diagnostic') ||
           url.includes('/horizon/') ||
           url.includes('/telescope/');
}

// Fetch event - performance-optimized caching strategies
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') return;

    const url = event.request.url;

    // Skip API and admin keepalive requests
    if (shouldSkip(url)) return;

    // Handle CDN resources with cache-first strategy
    if (!url.startsWith(self.location.origin) && isCDNResource(url)) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Return cache, but update in background (stale-while-revalidate)
                    fetchAndCache(event.request);
                    return cachedResponse;
                }
                return fetchAndCache(event.request);
            })
        );
        return;
    }

    // Skip cross-origin requests that aren't CDN
    if (!url.startsWith(self.location.origin)) return;

    // For navigation requests (HTML pages), network first
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    if (response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseClone);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    return caches.match(event.request).then((cachedResponse) => {
                        return cachedResponse || caches.match('./login');
                    });
                })
        );
        return;
    }

    // For static assets (CSS, JS, images, fonts), cache-first with background update
    const assetType = getAssetType(url);
    if (assetType !== 'html') {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) {
                    // Return cached version immediately, update in background
                    fetchAndCache(event.request);
                    return cachedResponse;
                }
                return fetchAndCache(event.request);
            })
        );
        return;
    }

    // Default: network first, fallback to cache
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                if (response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                return caches.match(event.request).then((cachedResponse) => {
                    return cachedResponse || new Response('Offline', {
                        status: 503,
                        statusText: 'Service Unavailable'
                    });
                });
            })
    );
});

// Helper: fetch and cache a request
function fetchAndCache(request) {
    return fetch(request).then((response) => {
        if (response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
                cache.put(request, responseClone);
            });
        }
        return response;
    }).catch(() => {
        return caches.match(request);
    });
}

// Push notification event
self.addEventListener('push', (event) => {
    let data = {
        title: 'Redemption School',
        body: 'You have a new notification',
        icon: './icons/icon-192x192.png',
        badge: './icons/icon-72x72.png',
        data: {
            url: './admin'
        }
    };

    if (event.data) {
        try {
            data = { ...data, ...event.data.json() };
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: data.icon,
        badge: data.badge,
        vibrate: [200, 100, 200],
        data: data.data,
        actions: [
            { action: 'open', title: 'Open' },
            { action: 'dismiss', title: 'Dismiss' }
        ],
        tag: 'redemption-notification',
        renotify: true
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification click event
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'dismiss') return;

    const urlToOpen = event.notification.data?.url || './admin';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url.includes('/admin') && 'focus' in client) {
                    return client.focus();
                }
            }
            return self.clients.openWindow(urlToOpen);
        })
    );
});

// Background sync for offline form submissions
self.addEventListener('sync', (event) => {
    if (event.tag === 'attendance-sync') {
        event.waitUntil(syncAttendanceData());
    }
    if (event.tag === 'mark-entry-sync') {
        event.waitUntil(syncMarkEntryData());
    }
    if (event.tag === 'data-sync') {
        event.waitUntil(syncAllData());
    }
});

async function syncAttendanceData() {
    // Get pending attendance from IndexedDB and submit
}

async function syncMarkEntryData() {
    // Get pending mark entries from IndexedDB and submit
}

async function syncAllData() {
    // Sync all pending data when back online
    await syncAttendanceData();
    await syncMarkEntryData();
}
