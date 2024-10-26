import { axios, getViewType, pay_token } from '@axios'
import { canNavigate } from '@layouts/plugins/casl'
import { setupLayouts } from 'virtual:generated-layouts'
import { createRouter, createWebHistory } from 'vue-router'
import routes from '~pages'

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/',
            redirect: to => {
                const isLoggedIn = pay_token.value != ''
                if (isLoggedIn)
                    return { name: getViewType(), query: to.query }
                else
                    return { name: 'login', query: to.query }
            },
        },
        {
            path: '/merchandises/terminals/edit/:id',
            redirect: to => { return `/merchandises/pay-modules/edit/${to.params.id}` }
        },
        {
            path: '/merchandises/terminals/create',
            redirect: to => { return `/merchandises/pay-modules/create` }
        },
        {
            path: '/dashboards/home',
            redirect: to => {                
                return { name: getViewType(), query: to.query }
            }
        },
        ...setupLayouts(routes),
    ],
})

// Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards
router.beforeEach(to => {
    const isLoggedIn = pay_token.value != ''
    axios.defaults.headers.common['Authorization'] = `Bearer ${pay_token.value}`

    if(to.path.startsWith('/pay/') === false) {
        if (canNavigate(to)) {
            if (to.meta.redirectIfLoggedIn && isLoggedIn)
                return '/'
        }
        else {
            if (isLoggedIn)
                return { name: 'not-authorized' }
            else
                return { name: 'login', query: { to: to.name !== 'index' ? to.fullPath : undefined } }
        }    
    }
})
router.onError((error, to) => {
    if (error.message.includes('Failed to fetch dynamically imported module')) {
        window.location = to.fullPath
    }
})
export default router
