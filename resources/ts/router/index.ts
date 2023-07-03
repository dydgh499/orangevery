import { axios, pay_token, user_info } from '@axios'
import { canNavigate } from '@layouts/plugins/casl'
import { setupLayouts } from 'virtual:generated-layouts'
import { createRouter, createWebHistory } from 'vue-router'
import routes from '~pages'

const getUserLevel = () => {
    if(user_info.value) {
        if(user_info.value.mcht_name) {
            user_info.value.level = 10
        }
        return user_info.value.level
    }
    return 0
}
const getViewType = () => {
    const level = getUserLevel()
    if(level == 10)
        return 'quick-view'
    else if(level <= 30 && user_info.value.view_type == 0)
        return 'quick-view'
    else
        return 'dashboards-home'
}
const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        // ℹ️ We are redirecting to different pages based on role.
        // NOTE: Role is just for UI purposes. ACL is based on abilities.
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
    axios.defaults.headers.common['Authorization'] = `Bearer ${pay_token.value}`;
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
})

export default router
