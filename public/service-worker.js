importScripts('https://storage.googleapis.com/workbox-cdn/releases/4.3.0/workbox-sw.js');
//Workbox Config
workbox.setConfig({
    debug: false //set to true if you want to see SW in action.
});

workbox.routing.registerRoute(
    new RegExp('/storage/images/.*\\.pdf$'), 
    new workbox.strategies.NetworkFirst({
      cacheName: 'pdf-cache',
      networkTimeoutSeconds: 10, 
      plugins: [
        new workbox.cacheableResponse.Plugin({
          statuses: [0, 200],
        }),
        new workbox.expiration.Plugin({
          maxEntries: 50,
          maxAgeSeconds: 7 * 24 * 60 * 60, 
        }),
      ],
    })
);

// 모든 리소스를 CacheFirst 전략으로 캐시
workbox.routing.registerRoute(
    new RegExp('.*'),
    new workbox.strategies.CacheFirst({
      cacheName: 'general-cache',
      plugins: [
        new workbox.expiration.Plugin({
          maxEntries: 100, // 최대 캐시 항목 수
          maxAgeSeconds: 30 * 24 * 60 * 60, // 30일 동안 캐시 유지
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
