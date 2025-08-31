self.addEventListener('push', (event) => {
    const data = event.data?.json() || {};
    const title = data.title || "VVIN Notification";
    const options = {
        body: data.body || "You have a new message",
        icon: "/web-app-manifest-192x192.png",
        badge: "/web-app-manifest-192x192.png",
        vibrate: [200, 100, 200],
        data: {
            url: data.url || "/",
        },
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data.url)
    );
});
