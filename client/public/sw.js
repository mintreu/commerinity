/**
 * Service Worker for Push Notifications
 */

// Listen for push events
self.addEventListener('push', function (event) {
  if (!event.data) {
    return
  }

  let data
  try {
    data = event.data.json()
  }
  catch {
    data = {
      title: 'New Notification',
      body: event.data.text()
    }
  }

  const options = {
    body: data.body || data.message || '',
    icon: data.icon || '/icon-192x192.png',
    badge: '/badge-72x72.png',
    vibrate: [100, 50, 100],
    data: {
      url: data.action_url || data.url || '/',
      ...data
    },
    actions: data.actions || []
  }

  event.waitUntil(
    self.registration.showNotification(data.title || 'Notification', options)
  )
})

// Handle notification click
self.addEventListener('notificationclick', function (event) {
  event.notification.close()

  const urlToOpen = event.notification.data?.url || '/'

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
      // Check if there's already a window open
      for (const client of clientList) {
        if (client.url === urlToOpen && 'focus' in client) {
          return client.focus()
        }
      }
      // Open new window if none found
      if (clients.openWindow) {
        return clients.openWindow(urlToOpen)
      }
    })
  )
})

// Service worker activation
self.addEventListener('activate', function (event) {
  event.waitUntil(self.clients.claim())
})
