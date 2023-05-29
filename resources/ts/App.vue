<script setup lang="ts">
import { useThemeConfig } from '@core/composable/useThemeConfig';
import corp from '@corp';
import { hexToRgb } from '@layouts/utils';
import { themeConfig } from '@themeConfig';
import { useTheme } from 'vuetify'

const vuetifyTheme = useTheme()
const currentThemeName = vuetifyTheme.name.value
vuetifyTheme.themes.value[currentThemeName].colors.primary = corp.color
themeConfig.app.title = corp.name;

const { syncInitialLoaderTheme, syncVuetifyThemeWithTheme: syncConfigThemeWithVuetifyTheme, isAppRtl } = useThemeConfig()
const { global } = useTheme()

syncInitialLoaderTheme()
syncConfigThemeWithVuetifyTheme()
</script>
<template>
    <VLocaleProvider :rtl="isAppRtl">
        <!-- ℹ️ This is required to set the background color of active nav link based on currently active global theme's primary -->
        <VApp :style="`--v-global-theme-primary: ${hexToRgb(global.current.value.colors.primary)};`">
            <RouterView />
        </VApp>
    </VLocaleProvider>
</template>
