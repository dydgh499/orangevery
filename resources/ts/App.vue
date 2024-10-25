<script setup lang="ts">
import { useThemeConfig } from '@core/composable/useThemeConfig'
import corp from '@corp'
import { hexToRgb } from '@layouts/utils'
import { themeConfig } from '@themeConfig'
import { useTheme } from 'vuetify'

const vuetifyTheme = useTheme()
const currentThemeName = vuetifyTheme.name.value
vuetifyTheme.themes.value[currentThemeName].colors.primary = corp.theme_css.main_color
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
<style>
/*
  transform: scale(0.8);
.v-overlay > .v-autocomplete__content,
.v-overlay > .v-select__content {
  background-color: rgb(var(--v-theme-surface)) !important;
  inset-inline-start: 0 !important;
}
*/
.v-table__wrapper {
  overflow-y: auto !important;
}

div::-webkit-scrollbar {
  block-size: 15px;
  inline-size: 15px;
}
.v-table__wrapper::-webkit-scrollbar-thumb { background: rgb(var(--v-global-theme-primary)); }
.v-table__wrapper::-webkit-scrollbar-thumb:hover { background: rgb(var(--v-global-theme-primary), 0.9); }

</style>
