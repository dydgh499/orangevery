import '@/@fake-db/db'
import '@/@iconify/icons-bundle'
import App from '@/App.vue'
import ability from '@/plugins/casl/ability'
import i18n from '@/plugins/i18n'
import layoutsPlugin from '@/plugins/layouts'
import vuetify from '@/plugins/vuetify'
import { loadFonts } from '@/plugins/webfontloader'
import router from '@/router'
import { pay_token } from '@axios'
import { abilitiesPlugin } from '@casl/vue'
import '@core-scss/template/index.scss'
import '@styles/styles.scss'
import { createPinia } from 'pinia'
import { createApp } from 'vue'
loadFonts()


declare module '@vue/runtime-core' {
    export interface ComponentCustomProperties {
      $errorHandler: (e: any) => any;
      $formatDate: (e: Date) => string;
    }
}

// Create vue app
const app = createApp(App)
app.provide('$errorHandler', function(e: any) {
    if(e.response.status == 401 || e.response.status == 403) {
        pay_token.value = '';
        localStorage.removeItem('payvery-token')
        router.replace('/login')
    }
    return e.response
});
app.provide('$formatDate', function(date: Date) {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`;
});

// Use plugins
app.use(vuetify)
app.use(createPinia())
app.use(router)
app.use(layoutsPlugin)
app.use(i18n)
app.use(abilitiesPlugin, ability, {
  useGlobalProperties: true,
})

// Mount vue app
app.mount('#app')
