// Redemption School Management System - Service Worker
// Provides offline capability, caching, and push notification support

const CACHE_NAME = 'redemption-v1';
const STATIC_ASSETS = [
    '/school-of-redemption/public/',
    '/school-of-redemption/public/manifest.json',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
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

// Fetch event - network first, fallback to cache
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') return;

    // Skip cross-origin requests
    if (!event.request.url.startsWith(self.location.origin)) return;

    // For API calls, always use network
    if (event.request.url.includes('/api/') || event.request.url.includes('/admin/attendance-api/') || event.request.url.includes('/admin/transcript/api/')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Clone the response and cache it
                if (response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                // Network failed, try cache
                return caches.match(event.request).then((cachedResponse) => {
                    return cachedResponse || new Response('Offline', {
                        status: 503,
                        statusText: 'Service Unavailable'
                    });
                });
            })
    );
});

// Push notification event
self.addEventListener('push', (event) => {
    let data = {
        title: 'Redemption School',
        body: 'You have a new notification',
        icon: '/school-of-redemption/public/icons/icon-192x192.png',
        badge: '/school-of-redemption/public/icons/icon-72x72.png',
        data: {
            url: '/school-of-redemption/public/admin'
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

    const urlToOpen = event.notification.data?.url || '/school-of-redemption/public/admin';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // If there's already a window open, focus it
            for (const client of clientList) {
                if (client.url.includes('/admin') && 'focus' in client) {
                    return client.focus();
                }
            }
            // Otherwise open new window
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
});

async function syncAttendanceData() {
    // Get pending attendance from IndexedDB and submit
    // This will be implemented when offline support is needed
}

async function syncMarkEntryData() {
    // Get pending mark entries from IndexedDB and submit
    // This will be implemented when offline support is needed
}
