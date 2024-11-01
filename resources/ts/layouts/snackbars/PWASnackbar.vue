<script lang="ts" setup>
import { createCookie, readCookie } from '@/layouts/snackbars/pwa'
import corp from '@corp'

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
        if(corp.logo_img) {
            const extension = (corp.logo_img as string).split('.').pop();
            let type = '';
            if (extension === 'svg')
                type = 'image/svg+xml'
            else if (extension === 'webp')
                type = 'image/webp'
            else
                type = 'image/png'

            const manifest = {
                "version": "2.1",
                "comment": corp.name,
                "lang": "ko",
                "name": corp.name,
                "scope": "https://" + corp.dns,
                "display": "fullscreen",
                "start_url": "https://" + corp.dns,
                "short_name": corp.name,
                "description": "",
                "orientation": "portrait",
                "background_color": 'white',
                "theme_color": 'white',
                "generated": "true",
                "icons": [
                    {
                        "src": corp.logo_img,
                        "sizes": "48x48 72x72 96x96 128x128 256x256",
                        "type": type,
                        "purpose": "any maskable"
                    },
                    {
                        "src": corp.logo_img,
                        "sizes": "512x512",
                        "type": "image/png",
                        "purpose": "any maskable"
                    },
                    {
                        "src": corp.logo_img,
                        "sizes": "512x512",
                        "type": "image/png",
                        "purpose": "any maskable"
                    },
                ]
            };
            const stringManifest = JSON.stringify(manifest);
            const blob = new Blob([stringManifest], { type: 'application/json' });
            const manifestURL = URL.createObjectURL(blob);
            document.querySelector('#my-manifest').setAttribute('href', manifestURL);
        }
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
        if ((before_install_prompt.value && service_worker.value) || isMobile.iOS()) {
            if (!readCookie(shortcut)) {
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
        <template v-if="isMobile.iOS()">
            <div class="pwa-container">
                <img :src="corp.logo_img || ''" width="80">
                <span style="margin: 1em 0;font-weight: bold;">앱 설치 없이 홈 화면에서 바로 이용해 보세요!</span>
            </div>
            <div style="text-align: center;">
                <VChip label>
                    <b>하단</b>
                    <VIcon icon="material-symbols:ios-share" style=" margin: 0.1em;color: #1b72db;" size="24"/>
                    <b>버튼</b>
                </VChip>
                <VIcon icon="material-symbols:arrow-forward-ios" style="margin: 0.5em;" />
                <VChip label>
                    <b style=" margin-top: 0.1em;float: inline-start;">홈 화면에 추가</b>
                    <span style="margin: 1em;"></span>
                    <VIcon style="float: inline-end;" icon="material-symbols:add-box-outline" />
                </VChip>
            </div>
        </template>
        <template v-else>
            <div class="pwa-container">
                <img :src="corp.logo_img || ''" width="100">
                <span style="margin: 1em 0;font-weight: bold;">바로가기를 생성할까요?</span>
                <span>앱 설치 없이 홈 화면에서 바로 이용할 수 있습니다. 지금 추가해 보세요!</span>
            </div>
        </template>
        <template #actions>
            <template v-if="isMobile.iOS()">
                <VBtn color="error" @click="close()">
                    30일간 안보기
                </VBtn>
            </template>
            <template v-else>
                <VBtn color="success" @click="createShortcut()">
                    생성하기
                </VBtn>
                <VBtn color="error" @click="close()">
                    30일간 안보기
                </VBtn>
            </template>
        </template>
        <div class="ios-share-arrow" v-if="isMobile.iOS()">
            <VIcon icon="ic:baseline-keyboard-double-arrow-down" size="45" />
        </div>
    </VSnackbar>
</template>
<style scoped>
.ios-share-arrow {
  position: absolute;
  inline-size: 90%;
  inset-block-end: 0;
  text-align: center;
}

@keyframes bounce {
  0%,
  20%,
  50%,
  80%,
  100% {
    transform: translateY(0);
  }

  40% {
    transform: translateY(-10px);
  }

  60% {
    transform: translateY(-5px);
  }
}

/* .ios-share-arrow 클래스에 애니메이션 적용 */
.ios-share-arrow {
  animation: bounce 2s infinite;
}

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
