// TokenGlade Service Worker for Browser Push Notifications

self.addEventListener('push', function (event) {
  if (!event.data) return;

  let payload = {};
  try {
    payload = event.data.json();
  } catch (e) {
    payload = {
      title: 'TokenGlade Price Alert',
      body: event.data.text(),
      url: 'https://tokenglade.com',
    };
  }

  const title = payload.title || 'TokenGlade Alert';
  const options = {
    body: payload.body || 'A price alert has been triggered on TokenGlade.',
    icon: payload.icon || '/src/assets/token-glade-logo.png',
    badge: payload.badge || '/src/assets/token-glade-logo.png',
    tag: payload.tag || 'tokenglade-alert-' + Date.now(),
    data: payload.data || { url: payload.url || '/' },
    requireInteraction: true,
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();

  const targetUrl = event.notification.data?.url || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
      for (let i = 0; i < clientList.length; i++) {
        const client = clientList[i];
        if (client.url.includes(targetUrl) && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});
