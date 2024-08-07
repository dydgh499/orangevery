<script setup lang="ts">
import { useThemeConfig } from '@core/composable/useThemeConfig'
import { useZoomProperty } from '@layouts/composable/useZoomProperty'
import { useDisplay } from 'vuetify'

const { zoom, zoomIn,zoomOut } = useZoomProperty()
const { isLessThanOverlayNavBreakpoint } = useThemeConfig()
const { width: windowWidth } = useWindowSize()
const { mobile } = useDisplay()
onMounted(() => {
    /*
    watchEffect(() => {
        if(mobile && getUserLevel() < 35)
            zoom.value = 100
    })
    */
})
</script>
<template>
    <VChip color="primary" variant="elevated" size="default" style="padding: 0 0.1em; margin-right: 0.5em;" v-if="isLessThanOverlayNavBreakpoint(windowWidth) == false">
        <VBtn color="primary" icon="line-md:plus" @click="zoomIn()" size="small" flat/>
        <span style="margin: 0 0.2em;">
            {{ zoom }}%
        </span>
        <VBtn color="primary" icon="line-md:minus" @click="zoomOut()" size="small" flat/>
    </VChip>
</template>
