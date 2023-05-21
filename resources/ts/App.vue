<script setup lang="ts">
import { useThemeConfig } from '@core/composable/useThemeConfig';
import corp from '@corp';
import { hexToRgb } from '@layouts/utils';
import { themeConfig } from '@themeConfig';
import { useTheme } from 'vuetify';
import AlertDialog from '@/views/utils/AlertDialog.vue';
import LoadingDialog from '@/views/utils/LoadingDialog.vue';

//themeConfig.app.logo = corp.logo_img;
themeConfig.app.title = corp.name;
const { syncInitialLoaderTheme, syncVuetifyThemeWithTheme: syncConfigThemeWithVuetifyTheme, isAppRtl } = useThemeConfig()
const { global } = useTheme()
//global.current.value.colors.primary = corp.color;
// ℹ️ Sync current theme with initial loader theme
syncInitialLoaderTheme()
syncConfigThemeWithVuetifyTheme()
</script>
<template>
    <VLocaleProvider :rtl="isAppRtl">
        <!-- ℹ️ This is required to set the background color of active nav link based on currently active global theme's primary -->
        <VApp :style="`--v-global-theme-primary: ${hexToRgb(corp.color)};`">
            <RouterView />
        </VApp>
    </VLocaleProvider>
</template>
