/* Face Kiosk service worker — installability only.
   No caching: every request passes straight through to the network,
   so ERP pages and APIs always behave exactly as without a worker. */
self.addEventListener('install', function () {
    self.skipWaiting();
});
self.addEventListener('activate', function (event) {
    event.waitUntil(self.clients.claim());
});
self.addEventListener('fetch', function () {
    /* Passthrough — required fetch handler for PWA installability. */
});
