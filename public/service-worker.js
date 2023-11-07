importScripts('https://storage.googleapis.com/workbox-cdn/releases/4.3.0/workbox-sw.js');
//Workbox Config
workbox.setConfig({
    debug: false //set to true if you want to see SW in action.
});

//Caching Everything Inside the Folder of our Item
workbox.routing.registerRoute(
    new RegExp('.*'),
    new workbox.strategies.NetworkFirst()
);

self.addEventListener("install", (e) => {
    console.log("[Service Worker] installed");
});

// activate event
self.addEventListener("activate", (e) => {
    console.log("[Service Worker] actived", e);
});

// fetch event
self.addEventListener("fetch", (e) => {
    console.log("[Service Worker] fetched resource " + e.request.url);
});
