import '@/@iconify/icons-bundle'
import App from '@/App.vue'
import ability from '@/plugins/casl/ability'
import i18n from '@/plugins/i18n'
import layoutsPlugin from '@/plugins/layouts'
import vuetify from '@/plugins/vuetify'
import { loadFonts } from '@/plugins/webfontloader'
import router from '@/router'
import { pay_token, user_info } from '@axios'
import { abilitiesPlugin } from '@casl/vue'
import '@core-scss/template/index.scss'
import VueMask from '@devindex/vue-mask'
import '@styles/styles.scss'
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import { createPinia } from 'pinia'
import { createApp } from 'vue'

/*
import VueVirtualScroller from 'vue-virtual-scroller'
import 'vue-virtual-scroller/dist/vue-virtual-scroller.css'
*/
loadFonts()

declare module '@vue/runtime-core' {
    export interface ComponentCustomProperties {
      $errorHandler: (e: any) => any;
      $formatDate: (e: Date) => string;
      $formatTime: (e: Date) => string;
    }
}

// Create vue app
const app = createApp(App)
app.provide('$errorHandler', function(e: any) {
    if(e.response.status == 401 || e.response.status == 403) {
        pay_token.value = ''
        user_info.value = {}
        location.href = '/'
    }
    return e.response
});
app.provide('$formatDate', function(date: Date) {
    if(date) {
        const year = date.getFullYear()
        const month = String(date.getMonth() + 1).padStart(2, '0')
        const day = String(date.getDate()).padStart(2, '0')
        return `${year}-${month}-${day}`;
    }
    else
        return `2023-12-01`
});
app.provide('$formatTime', function(date: Date) {
    if(date) {
        const hour = String(date.getHours()).padStart(2, '0')
        const min = String(date.getMinutes()).padStart(2, '0')
        const sec = String(date.getSeconds()).padStart(2, '0')
        return `${hour}:${min}:${sec}`;    
    }
    else
        return `00:00:00`
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
app.component('VueDatePicker', VueDatePicker)
// app.use(VueVirtualScroller)
app.use(VueMask)

// Mount vue app
app.mount('#app')
