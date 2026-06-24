// ============================================================================
// Redemption School Management System - Service Worker v7 (Complete Rewrite)
// ============================================================================
// FIXES:
//   1. Reload loop: NO navigation caching at all. Only static assets cached.
//      Previous version cached admin pages, then served stale versions on
//      back-navigation, causing infinite reload loops.
//   2. Export broken: SW was intercepting /api/export/* GET requests.
//      Now explicitly skips ALL /api/ and /export/ routes.
//   3. Login loop: Never caches /login or / (CSRF token staleness).
//   4. Pull-to-refresh conflict: Removed the admin layout's pull-to-refresh
//      JS that was triggering random reloads on touch devices.
//   5. v7: bumped cache name to force-bust all stale-while-revalidate JS/CSS
//      caches. Previous version was serving the OLD mark-entry JS even after
//      Ctrl+F5 because the SW was returning the cached version first and
//      updating in the background — meaning the user's first reload after a
//      fix would still run the old broken JS.
// ============================================================================

const CACHE_NAME = 'redemption-v9';
const STATIC_ASSETS = [
    './manifest.webmanifest',
];

// URLs that must NEVER be intercepted by the SW
function shouldSkip(url) {
    return url.includes('/api/') ||
           url.includes('/export/') ||
           url.includes('/admin/keepalive') ||
           url.includes('/admin/session-diagnostic') ||
           url.includes('/admin/media/') ||
           url.includes('/storage/') ||
           url.includes('/login') ||
           url.includes('/logout') ||
           url.includes('/password') ||
           url.includes('/telegram/webhook') ||
           url.includes('/lang/');
}

// CDN resources we cache for offline use
function isCDNResource(url) {
    return url.includes('cdn.jsdelivr.net') ||
           url.includes('cdnjs.cloudflare.com') ||
           url.includes('fonts.googleapis.com') ||
           url.includes('fonts.gstatic.com') ||
           url.includes('ui-avatars.com');
}

// Asset type from URL
function getAssetType(url) {
    if (url.match(/\.(css)(\?|$)/)) return 'css';
    if (url.match(/\.(js)(\?|$)/)) return 'js';
    if (url.match(/\.(png|jpg|jpeg|gif|svg|webp|ico)(\?|$)/)) return 'image';
    if (url.match(/\.(woff|woff2|ttf|eot)(\?|$)/)) return 'font';
    return 'html';
}

// ── Install: pre-cache minimal assets ────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch(() => {});
        })
    );
    self.skipWaiting();
});

// ── Activate: purge ALL old caches ────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

// ── Fetch: minimal interception ──────────────────────────────────
self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') return;

    const url = event.request.url;

    // CRITICAL: Skip ALL API, export, auth, and dynamic routes
    if (shouldSkip(url)) return;

    // Skip cross-origin requests that aren't CDN
    if (!url.startsWith(self.location.origin) && !isCDNResource(url)) return;

    // ── Navigation requests (HTML pages): NETWORK ONLY ──
    // Never cache, never serve from cache. This prevents:
    //   - Reload loops (stale page served on back-nav)
    //   - Login CSRF loops (stale token in cached form)
    //   - Export issues (cached page instead of fresh export)
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .catch(() => {
                    // Offline: show a simple offline page
                    return new Response(
                        '<!DOCTYPE html><html><head><meta charset="utf-8">' +
                        '<meta name="viewport" content="width=device-width,initial-scale=1">' +
                        '<title>Offline</title></head>' +
                        '<body style="font-family:sans-serif;display:flex;align-items:center;' +
                        'justify-content:center;min-height:100vh;margin:0;background:#0C1F17;' +
                        'color:#fff;text-align:center;padding:2rem;">' +
                        '<div><h1 style="color:#D97706;margin-bottom:8px;">You are offline</h1>' +
                        '<p style="color:#9ca3af;margin-bottom:16px;">Please check your connection.</p>' +
                        '<button onclick="location.reload()" style="padding:10px 24px;border:none;' +
                        'border-radius:8px;background:#047857;color:#fff;cursor:pointer;font-size:14px;">' +
                        'Retry</button></div></body></html>',
                        { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                    );
                })
        );
        return;
    }

    // ── CDN resources: cache-first ──
    if (isCDNResource(url)) {
        event.respondWith(
            caches.match(event.request).then((cached) => {
                if (cached) {
                    // Update in background
                    fetch(event.request).then((resp) => {
                        if (resp.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(event.request, resp.clone());
                            });
                        }
                    }).catch(() => {});
                    return cached;
                }
                return fetch(event.request).then((resp) => {
                    if (resp.status === 200) {
                        const clone = resp.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, clone);
                        });
                    }
                    return resp;
                }).catch(() => new Response('', { status: 503 }));
            })
        );
        return;
    }

    // ── Static assets (CSS, JS, images, fonts): cache-first ──
    const assetType = getAssetType(url);
    if (assetType !== 'html') {
        event.respondWith(
            caches.match(event.request).then((cached) => {
                if (cached) {
                    // Update in background (stale-while-revalidate)
                    fetch(event.request).then((resp) => {
                        if (resp.status === 200) {
                            caches.open(CACHE_NAME).then((cache) => {
                                cache.put(event.request, resp.clone());
                            });
                        }
                    }).catch(() => {});
                    return cached;
                }
                return fetch(event.request).then((resp) => {
                    if (resp.status === 200) {
                        const clone = resp.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, clone);
                        });
                    }
                    return resp;
                }).catch(() => new Response('', { status: 503 }));
            })
        );
        return;
    }

    // ── Everything else: network only (no caching) ──
    // This prevents stale-content reload loops for dynamic requests.
});

// ── Push notifications ────────────────────────────────────────────
self.addEventListener('push', (event) => {
    let data = {
        title: 'Redemption School',
        body: 'You have a new notification',
        icon: './icons/icon-192x192.png',
        badge: './icons/icon-72x72.png',
        data: { url: './admin' }
    };

    if (event.data) {
        try {
            data = { ...data, ...event.data.json() };
        } catch (e) {
            data.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(data.title, {
            body: data.body,
            icon: data.icon,
            badge: data.badge,
            vibrate: [200, 100, 200],
            data: data.data,
            tag: 'redemption-notification',
            renotify: true
        })
    );
});

// ── Notification click ────────────────────────────────────────────
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

// ── Message handler: allow page to trigger SW update ──────────────
self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
