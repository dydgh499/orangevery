importScripts('https://storage.googleapis.com/workbox-cdn/releases/4.3.0/workbox-sw.js');
//Workbox Config
workbox.setConfig({
    debug: false //set to true if you want to see SW in action.
});

// 정적 리소스: StaleWhileRevalidate 전략 적용
workbox.routing.registerRoute(
  ({ request }) => 
    request.destination === 'style' ||
    request.destination === 'script' ||
    request.destination === 'image',
  new workbox.strategies.StaleWhileRevalidate({
    cacheName: 'static-cache',
    plugins: [
      new workbox.expiration.Plugin({
        maxEntries: 100, // 최대 100개의 항목 캐시
        maxAgeSeconds: 3 * 24 * 60 * 60, // 3일 동안 캐시 유지
      }),
    ],
  })
);
  // install 이벤트
  self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installed');
    event.waitUntil(self.skipWaiting()); // 즉시 활성화
  });
  
  // activate 이벤트
  self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activated');
    event.waitUntil(
      caches.keys().then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cache) => {
            if (cache !== 'pdf-cache' && cache !== 'general-cache') {
              console.log('[Service Worker] Deleting old cache:', cache);
              return caches.delete(cache);
            }
          })
        );
      })
    );
    return self.clients.claim(); // 활성화 후 제어권 가져오기
  });

// fetch event
self.addEventListener("fetch", (e) => {
    console.log("[Service Worker] fetched resource " + e.request.url);
});
