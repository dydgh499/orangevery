import VueI18n from '@intlify/vite-plugin-vue-i18n'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import dotenv from 'dotenv'
import laravel from 'laravel-vite-plugin'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'
import DefineOptions from 'unplugin-vue-define-options/vite'
import { fileURLToPath } from 'url'
import { defineConfig } from 'vite'
import Pages from 'vite-plugin-pages'
import { VitePWA } from 'vite-plugin-pwa'
import Layouts from 'vite-plugin-vue-layouts'
import vuetify from 'vite-plugin-vuetify'

dotenv.config()
// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/ts/main.ts'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vueJsx(),
        // https://github.com/vuetifyjs/vuetify-loader/tree/next/packages/vite-plugin
        vuetify({
            styles: {
                configFile: 'resources/styles/variables/_vuetify.scss',
            },
        }),
        Pages({
            dirs: ['./resources/ts/pages'],

            // ℹ️ We need three routes using single routes so we will ignore generating route for this SFC file
            onRoutesGenerated: routes => [
                ...routes,
            ],
        }),
        Layouts({
            layoutsDirs: './resources/ts/layouts/',
        }),
        Components({
            dirs: ['resources/ts/@core/components', 'resources/ts/views/demos'],
            dts: true,
        }),
        AutoImport({
            imports: ['vue', 'vue-router', '@vueuse/core', '@vueuse/math', 'vue-i18n', 'pinia'],
            vueTemplate: true,
        }),
        VueI18n({
            runtimeOnly: true,
            compositionOnly: true,
            include: [
                fileURLToPath(new URL('./resources/ts/plugins/i18n/locales/**', import.meta.url)),
            ],
        }),
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: 'auto',
            devOptions: {
                enabled: true
            }
        }),
        DefineOptions(),
    ],
    server: {
        hmr: {
            host: "127.0.0.1",
        },
    },
    define: {
        'process.env': {
            MAIN_BRAND_ID: process.env.MAIN_BRAND_ID,
            APP_URL: process.env.APP_URL,
            NOTI_URL: process.env.NOTI_URL,
        },
    },
    resolve: {
        alias: {
            '@core-scss': fileURLToPath(new URL('./resources/styles/@core', import.meta.url)),
            '@': fileURLToPath(new URL('./resources/ts', import.meta.url)),
            '@themeConfig': fileURLToPath(new URL('./themeConfig.ts', import.meta.url)),
            '@core': fileURLToPath(new URL('./resources/ts/@core', import.meta.url)),
            '@layouts': fileURLToPath(new URL('./resources/ts/@layouts', import.meta.url)),
            '@images': fileURLToPath(new URL('./resources/images/', import.meta.url)),
            '@styles': fileURLToPath(new URL('./resources/styles/', import.meta.url)),
            '@configured-variables': fileURLToPath(new URL('./resources/styles/variables/_template.scss', import.meta.url)),
            '@axios': fileURLToPath(new URL('./resources/ts/plugins/axios', import.meta.url)),
            '@corp': fileURLToPath(new URL('./resources/ts/plugins/corp', import.meta.url)),
            '@validators': fileURLToPath(new URL('./resources/ts/@core/utils/validators', import.meta.url)),
            'apexcharts': fileURLToPath(new URL('node_modules/apexcharts-clevision', import.meta.url)),
        },
    },
    build: {
        outDir: 'public/build',
        chunkSizeWarningLimit: 5000,
    },
    optimizeDeps: {
        exclude: ['vuetify'],
        entries: [
            './resources/ts/**/*.vue',
        ],
    },
})
