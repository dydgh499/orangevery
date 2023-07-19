<script lang="ts" setup>
import navItems from '@/navigation/vertical'
import { useThemeConfig } from '@core/composable/useThemeConfig'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavbarNotifications from '@/layouts/components/NavbarNotifications.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import NavbarZoomSwitcher from '@/layouts/components/NavbarZoomSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'

// @layouts plugin
import { VerticalNavLayout } from '@layouts'

import AlertDialog from '@/layouts/dialogs/AlertDialog.vue'
import Snackbar from '@/layouts/snackbars/Snackbar.vue'
import LoadingDialog from '@/layouts/dialogs/LoadingDialog.vue'
import PayLinkDialog from '@/layouts/dialogs/PayLinkDialog.vue'

import { user_info } from '@axios'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { config } from '@layouts/config'

const router = useRouter()
const alert = ref(null)
const snackbar = ref(null)
const loading = ref(null)
const payLink = ref(null)

const is_pay_link = ref(router.currentRoute.value.path.includes('/pay/'))

provide('alert', alert)
provide('snackbar', snackbar)
provide('loading', loading)
provide('payLink', payLink)

const { appRouteTransition, isLessThanOverlayNavBreakpoint } = useThemeConfig()
const { width: windowWidth } = useWindowSize()
</script>

<template>
    <VerticalNavLayout :nav-items="navItems" v-if="is_pay_link === false">
        <!-- 👉 navbar -->
        <template #navbar="{ toggleVerticalOverlayNavActive }">
            <div class="d-flex h-100 align-center">
                <VBtn v-if="isLessThanOverlayNavBreakpoint(windowWidth)" icon variant="text" color="default" class="ms-n3"
                    size="small" @click="toggleVerticalOverlayNavActive(true)">
                    <VIcon icon="tabler-menu-2" size="24" />
                </VBtn>

                <div v-if="isLessThanOverlayNavBreakpoint(windowWidth)" style="display: flex;">
                    <VNodeRenderer :nodes="config.app.logo" />
                </div>
                <div v-else>
                    <span class="text-primary font-weight-bold">{{ user_info.user_name }}</span>님 안녕하세요!
                </div>
                <VSpacer />
                <NavbarZoomSwitcher />
                <NavbarThemeSwitcher />
                <NavbarNotifications class="me-2" v-if="user_info.level >= 35"/>
                <UserProfile />
            </div>
        </template>

        <!-- 👉 Pages -->
        <RouterView v-slot="{ Component }">
            <Transition :name="appRouteTransition" mode="out-in">
                <Component :is="Component" />
            </Transition>
            <PayLinkDialog ref="payLink" />
            <Snackbar ref="snackbar" />
            <AlertDialog ref="alert" />
            <LoadingDialog ref="loading" />
        </RouterView>

        <!-- 👉 Footer -->
        <template #footer>
            <Footer />
        </template>

        <!-- 👉 Customizer -->
        <TheCustomizer />
    </VerticalNavLayout>
    <div v-else style="height: 100%;" class="d-flex justify-center align-center">
        <RouterView v-slot="{ Component }">
            <Transition :name="appRouteTransition" mode="out-in">
                <Component :is="Component" />
            </Transition>
            <Snackbar ref="snackbar" />
            <AlertDialog ref="alert" />
            <LoadingDialog ref="loading" />
        </RouterView>
    </div>
</template>
