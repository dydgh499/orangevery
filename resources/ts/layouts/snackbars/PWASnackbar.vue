<script lang="ts" setup>
import corp from '@corp'
import { createCookie, readCookie } from '@/layouts/snackbars/pwa'

const shortcut = corp.dns + '-shortcut'
const visible = ref(false)
const service_worker = ref(false)
const before_install_prompt = ref(false)

const isMobile = {
    Android: function () { return navigator.userAgent.match(/Android/i); },
    iOS: function () { return navigator.userAgent.match(/iPhone|iPad|iPod/i); },
    Windows: function () {
        var filter = "win16|win32|win64|mac|macintel";
        return filter.indexOf(navigator.platform.toLowerCase()) > -1 ? true : false;
    }
};
let deferredPrompt: any;

const createShortcut = async () => {
    deferredPrompt.prompt();
    const choiceResult = await deferredPrompt.userChoice
    if (choiceResult.outcome === 'accepted') {
        createCookie(shortcut, "install", 9999)
        console.log('User accepted the A2HS prompt');
    }
    else {
        console.log('User dismissed the A2HS prompt');
    }
    deferredPrompt = null;
    visible.value = false
}

const close = () => {
    createCookie(shortcut, "no", 30)
    visible.value = false
}

onMounted(() => {
    const loadManifest = () => {
        const manifest = {
            "version": "2.1",
            "comment": corp.name,
            "lang": "ko",
            "name": corp.name,
            "scope": "https://" + corp.dns,
            "display": "fullscreen",
            "start_url": "https://" + corp.dns+"/build",
            "short_name": corp.name,
            "description" : "",
            "orientation" : "portrait",
            "background_color": 'white',
            "theme_color": corp.theme_css['main_color'],
            "generated": "true",
            "icons": [
                {
                    "src": corp.logo_img,
                    "sizes": "192x192",
                },
                {
                    "src": corp.logo_img,
                    "sizes": "512x512",
                },                
            ]
        };
        const stringManifest = JSON.stringify(manifest);
        const blob = new Blob([stringManifest], { type: 'application/json' });
        const manifestURL = URL.createObjectURL(blob);
        document.querySelector('#my-manifest').setAttribute('href', manifestURL);
    }

    const loadBeforeInstallPrompt = () => {
        // deferredPrompt
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log("beforeinstallprompt registration succeeded:", e);
            e.preventDefault();
            deferredPrompt = e;
            before_install_prompt.value = true
        });
    }

    const loadServiceWorker = async () => {
        // service Worker
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.register('/service-worker.js').then((registration) => {
                console.log("Service worker registration succeeded:", registration);
                service_worker.value = true
            });
        } 
        else
            console.log("Service workers are not supported.");
    }
    const deleteWorkboxRuntimeCashes = () => {
        caches.delete('workbox-runtime').then(function () {
        });
        caches.keys().then(cacheNames => {
            cacheNames.forEach(cacheName => {
                caches.delete(cacheName)
            });
        });
    }
    loadManifest()
    loadBeforeInstallPrompt()
    loadServiceWorker()
    deleteWorkboxRuntimeCashes()

    watchEffect(() => {
        if(before_install_prompt.value && service_worker.value) {
            if (!readCookie(shortcut) ) {
                setTimeout(function () {
                    visible.value = true
                }, 5000)
            }
        }
    })
})

</script>
<template>
    <VSnackbar v-model="visible" vertical transaction="scroll-y-reverse-transition" :timeout="60000" variant="flat">
        <div class="pwa-container">
            <img :src="corp.logo_img || ''" width="100" height="100">
            <span style="font-weight: bold;">바로가기를 생성할까요?</span>
            <br>
            <span>전산을 빠르게 접근할 수 있는 바로가기가 홈스크린에 생성됩니다.</span>
        </div>
        <template #actions>
            <VBtn color="success" @click="createShortcut()">
                생성하기
            </VBtn>
            <VBtn color="error" @click="close()">
                30일간 안보기
            </VBtn>
        </template>
    </VSnackbar>
</template>
<style scoped>
.pwa-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

:deep(.v-snackbar__content) {
  inline-size: 100%;
}
</style>
