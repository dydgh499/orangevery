import corp from '@/plugins/corp';
import { axios, pay_token } from '@axios';
import { canNavigate } from '@layouts/plugins/casl';
import { setupLayouts } from 'virtual:generated-layouts';
import { createRouter, createWebHistory } from 'vue-router';
import routes from '~pages';

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/',
            redirect: to => {
                const isLoggedIn = pay_token.value != ''
                if (isLoggedIn) {
                    return { name: 'virtuals-bank-accounts', query: to.query }
                }
                else
                    return { name: 'login', query: to.query }
            },
        },
        {
            path: '/services/brands',
            redirect: to => { return `/services/brands/edit/${corp.id}` }
        },
        ...setupLayouts(routes),
    ],
})

router.beforeEach(to => {
    const isLoggedIn = pay_token.value != ''
    axios.defaults.headers.common['Authorization'] = `Bearer ${pay_token.value}`
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
router.onError((error, to) => {
    if (error.message.includes('Failed to fetch dynamically imported module')) {
        window.location = to.fullPath
    }
})
export default router
