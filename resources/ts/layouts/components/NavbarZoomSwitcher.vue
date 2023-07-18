<script setup lang="ts">
import { useThemeConfig } from '@core/composable/useThemeConfig'
import { themeConfig } from '@themeConfig'

const snackbar = <any>(inject('snackbar'))
const { isLessThanOverlayNavBreakpoint } = useThemeConfig()
const { width: windowWidth } = useWindowSize()


const zoomIn = () => {
   zoom.value += 20
    if(zoom.value > 140) {
        snackbar.value.show('더이상 확대할 수 없습니다', 'warning')
        zoom.value = 140
    }
}

const zoomOut = () => {
    zoom.value -= 20
    if(zoom.value < 60) {
        snackbar.value.show('더이상 축소할 수 없습니다', 'warning')
        zoom.value = 60
    }
}

const getZoom = () => {
    return Number(localStorage.getItem(`${themeConfig.app.title}-zoom`) ?? 100)
}

const setZoom = () => {
    document.body.style.zoom = zoom.value + "%";
    localStorage.setItem(`${themeConfig.app.title}-zoom`, zoom.value.toString())
}

const zoom = ref(<number>(getZoom()))
watchEffect(() => {
    setZoom()
})
</script>
<template>
    <VChip color="primary" variant="elevated" size="default" style="padding: 0 0.1em; margin-right: 0.5em;" v-if="isLessThanOverlayNavBreakpoint(windowWidth) == false">
        <VBtn color="primary" icon="line-md:plus" @click="zoomIn()" size="small" />
        <span style="margin: 0 0.2em;">
            {{ zoom }}%
        </span>
        <VBtn color="primary" icon="line-md:minus" @click="zoomOut()" size="small" />
    </VChip>
</template>
