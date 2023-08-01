<script setup lang="ts">
import { useZoomProperty } from '@layouts/composable/useZoomProperty'
import { useThemeConfig } from '@core/composable/useThemeConfig'
import { useLayouts } from '@layouts/composable/useLayouts'

const { zoom, zoomIn,zoomOut } = useZoomProperty()
const { isLessThanOverlayNavBreakpoint } = useThemeConfig()
const { width: windowWidth } = useWindowSize()
const { isAppRtl, appContentWidth } = useLayouts()

watchEffect(() => {


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
