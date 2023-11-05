<script lang="ts" setup>
import corp from '@corp'

const visible = ref(false)
const show = () => {
    visible.value = true
};


const loadManifest = () => {
    const logo = new Image();
    logo.src = corp.logo_img;
    const manifest = {
        "version": "2.1",
        "lang": "ko",
        "name": corp.name,
        "scope": "https://" + corp.dns,
        "display": "fullscreen",
        "start_url": "https://" + corp.dns,
        "short_name": corp.name,
        "description": "",
        "orientation": "portrait",
        "background_color": corp.theme_css['main_color'],
        "theme_color": corp.theme_css['main_color'],
        "generated": "true",
        "icons": [
            {
                "src": corp.logo_img,
                "sizes": "72x72 96x96 128x128 144x144 152x152 192x192 384x384 512x512 120x120 180x180",
            },
        ]
    };
    const stringManifest = JSON.stringify(manifest);
    const blob = new Blob([stringManifest], { type: 'application/json' });
    const manifestURL = URL.createObjectURL(blob);
    document.querySelector('#my-manifest').setAttribute('href', manifestURL);
}
const loadServiceWorker = () => {
    if ("serviceWorker" in navigator) {
        navigator.serviceWorker
            .register("/service-worker.js")
            .then((registration) => {
                console.log("Service worker registration succeeded:", registration);
            })
            .catch((err) => {
                console.log("Service worker registration failed:", error);
            });
    } else {
        console.log("Service workers are not supported.");
    }
}
loadManifest()
loadServiceWorker()

defineExpose({
    show
});
</script>
<template>
    <VSnackbar v-model="visible" vertical transaction="fade-transition" :timeout="60000" variant="flat">
        <div class="pwa-container">
            <img :src="corp.logo_img || ''" width="100" height="100">
            <span style="font-weight: bold;">바로가기를 생성할까요?</span>
            <br>
            <span>홈화면에 전산을 빠르게 접근할 수 있는 바로가기가 생성됩니다.</span>
        </div>
        <template #actions>
            <VBtn color="success" @click="visible = false">
                생성하기
            </VBtn>
            <VBtn color="error" @click="visible = false">
                한달간 안보기
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
