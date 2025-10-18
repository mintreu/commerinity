// self.addEventListener('push', (event) => {
//     const data = event.data?.json() || {};
//     const title = data.title || "VVIN Notification";
//     const options = {
//         body: data.body || "You have a new message",
//         icon: "/web-app-manifest-192x192.png",
//         badge: "/web-app-manifest-192x192.png",
//         vibrate: [200, 100, 200],
//         data: {
//             url: data.url || "/",
//         },
//     };
//
//     event.waitUntil(
//         self.registration.showNotification(title, options)
//     );
// });
//
// // Handle notification click
// self.addEventListener('notificationclick', (event) => {
//     event.notification.close();
//     event.waitUntil(
//         clients.openWindow(event.notification.data.url)
//     );
// });






/* eslint-disable no-undef */

// ============================================
// CONFIGURATION
// ============================================
const CACHE_VERSION = 'v1';
const CACHE_NAME = `vvin-cache-${CACHE_VERSION}`;
const NOTIFICATION_ICON = '/web-app-manifest-192x192.png';
const NOTIFICATION_BADGE = '/web-app-manifest-192x192.png';
const DEFAULT_NOTIFICATION_TITLE = 'VVIN Notification';
const DEFAULT_NOTIFICATION_BODY = 'You have a new message';

// ============================================
// INSTALL EVENT
// ============================================
self.addEventListener('install', (event) => {
    console.log('✅ Service Worker installing...');

    // Force the waiting service worker to become the active service worker
    self.skipWaiting();

    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('📦 Cache opened');
            // You can pre-cache critical assets here
            return cache.addAll([
                '/',
                '/web-app-manifest-192x192.png',
                // Add other critical assets
            ]).catch(err => {
                console.warn('⚠️ Some assets failed to cache:', err);
            });
        })
    );
});

// ============================================
// ACTIVATE EVENT
// ============================================
self.addEventListener('activate', (event) => {
    console.log('✅ Service Worker activating...');

    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    // Delete old caches
                    if (cacheName !== CACHE_NAME) {
                        console.log('🗑️ Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            // Take control of all pages immediately
            return self.clients.claim();
        })
    );
});

// ============================================
// PUSH EVENT (Web Push Notifications)
// ============================================
self.addEventListener('push', (event) => {
    console.log('📩 Push notification received');

    let notificationData = {
        title: DEFAULT_NOTIFICATION_TITLE,
        body: DEFAULT_NOTIFICATION_BODY,
        icon: NOTIFICATION_ICON,
        badge: NOTIFICATION_BADGE,
        url: '/',
        image: null,
        tag: null,
        requireInteraction: false,
        actions: [],
        data: {}
    };

    // Parse push data
    if (event.data) {
        try {
            const payload = event.data.json();
            console.log('📦 Push payload:', payload);

            notificationData = {
                title: payload.title || notificationData.title,
                body: payload.body || notificationData.body,
                icon: payload.icon || notificationData.icon,
                badge: payload.badge || notificationData.badge,
                url: payload.data?.url || payload.url || notificationData.url,
                image: payload.image || null,
                tag: payload.tag || `notification-${Date.now()}`,
                requireInteraction: payload.requireInteraction || false,
                actions: payload.actions || [],
                data: payload.data || {}
            };
        } catch (error) {
            console.error('❌ Error parsing push data:', error);
        }
    }

    // Notification options
    const options = {
        body: notificationData.body,
        icon: notificationData.icon,
        badge: notificationData.badge,
        image: notificationData.image,
        tag: notificationData.tag,
        requireInteraction: notificationData.requireInteraction,
        vibrate: [200, 100, 200, 100, 200],
        timestamp: Date.now(),
        data: {
            url: notificationData.url,
            dateOfArrival: Date.now(),
            ...notificationData.data
        },
        actions: notificationData.actions.length > 0 ? notificationData.actions : [
            { action: 'open', title: 'Open', icon: notificationData.icon },
            { action: 'close', title: 'Close', icon: notificationData.icon }
        ]
    };

    // Show notification
    event.waitUntil(
        self.registration.showNotification(notificationData.title, options)
            .then(() => {
                console.log('✅ Notification displayed successfully');
            })
            .catch(error => {
                console.error('❌ Error displaying notification:', error);
            })
    );
});

// ============================================
// NOTIFICATION CLICK EVENT
// ============================================
self.addEventListener('notificationclick', (event) => {
    console.log('🖱️ Notification clicked');

    event.notification.close();

    const urlToOpen = event.notification.data?.url || '/';
    const action = event.action;

    // Handle action buttons
    if (action === 'close') {
        console.log('❌ User closed notification');
        return;
    }

    // Open URL
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // Check if there's already a window open
                for (let i = 0; i < clientList.length; i++) {
                    const client = clientList[i];

                    // If same origin, focus and navigate
                    if (client.url.startsWith(self.location.origin) && 'focus' in client) {
                        console.log('🎯 Focusing existing window');
                        return client.focus().then(() => {
                            if (client.navigate) {
                                return client.navigate(urlToOpen);
                            }
                        });
                    }
                }

                // No window found, open new one
                if (clients.openWindow) {
                    console.log('🆕 Opening new window');
                    return clients.openWindow(urlToOpen);
                }
            })
            .catch(error => {
                console.error('❌ Error handling notification click:', error);
            })
    );
});

// ============================================
// NOTIFICATION CLOSE EVENT
// ============================================
self.addEventListener('notificationclose', (event) => {
    console.log('🔕 Notification closed by user');

    // Optional: Track notification close events
    const notificationData = event.notification.data;

    // You can send analytics here if needed
    if (notificationData) {
        console.log('📊 Notification data on close:', notificationData);
    }
});

// ============================================
// FETCH EVENT (Optional: Add caching strategies)
// ============================================
self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip cross-origin requests
    if (!event.request.url.startsWith(self.location.origin)) {
        return;
    }

    // Network first, falling back to cache
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Clone the response
                const responseToCache = response.clone();

                // Cache successful responses
                if (response.status === 200) {
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }

                return response;
            })
            .catch(() => {
                // If network fails, try cache
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }

                    // Return a custom offline page if available
                    return caches.match('/offline.html');
                });
            })
    );
});

// ============================================
// BACKGROUND SYNC (Optional: For offline actions)
// ============================================
self.addEventListener('sync', (event) => {
    console.log('🔄 Background sync triggered');

    if (event.tag === 'sync-notifications') {
        event.waitUntil(
            // Sync logic here
            Promise.resolve()
        );
    }
});

// ============================================
// MESSAGE EVENT (Communication with pages)
// ============================================
self.addEventListener('message', (event) => {
    console.log('💬 Message received from page:', event.data);

    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => caches.delete(cacheName))
                );
            }).then(() => {
                event.ports[0].postMessage({ success: true });
            })
        );
    }
});

// ============================================
// ERROR HANDLING
// ============================================
self.addEventListener('error', (event) => {
    console.error('❌ Service Worker error:', event.error);
});

self.addEventListener('unhandledrejection', (event) => {
    console.error('❌ Unhandled promise rejection in SW:', event.reason);
});

// ============================================
// LOG READY STATE
// ============================================
console.log('🚀 VVIN Service Worker loaded and ready!');
