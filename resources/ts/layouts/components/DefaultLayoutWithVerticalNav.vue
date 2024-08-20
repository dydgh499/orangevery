<script lang="ts" setup>
import navItems from '@/navigation/vertical'
import { useThemeConfig } from '@core/composable/useThemeConfig'

// Components
import Footer from '@/layouts/components/Footer.vue'
import NavbarNotifications from '@/layouts/components/NavbarNotifications.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import NavbarZoomSwitcher from '@/layouts/components/NavbarZoomSwitcher.vue'
import NavTokenableExpireTime from '@/layouts/components/NavTokenableExpireTime.vue'

import UserProfile from '@/layouts/components/UserProfile.vue'
import router from '@/router'
import { VerticalNavLayout } from '@layouts'

import PayWindowCreateDialog from '@/layouts/dialogs/transactions/PayWindowCreateDialog.vue'
import PayWindowShowDialog from '@/layouts/dialogs/transactions/PayWindowShowDialog.vue'
import PasswordChangeNoticeDialog from '@/layouts/dialogs/users/PasswordChangeNoticeDialog.vue'
import AlertDialog from '@/layouts/dialogs/utils/AlertDialog.vue'
import LoadingDialog from '@/layouts/dialogs/utils/LoadingDialog.vue'
import PopupDialog from '@/layouts/dialogs/utils/PopupDialog.vue'

import PWASnackbar from '@/layouts/snackbars/PWASnackbar.vue'
import Snackbar from '@/layouts/snackbars/Snackbar.vue'

import corp from '@/plugins/corp'
import { isFixplus } from '@/plugins/fixplus'
import { axios, getUserLevel, user_info } from '@axios'

const popup = ref()
const alert = ref()
const snackbar = ref()
const loading = ref()
const payLink = ref()
const payShow = ref()
const pwaSnackbar = ref()
const passwordChangeNoticeDialog = ref()

const is_pay_link = ref(router.currentRoute.value.path.includes('/pay/'))

provide('popup', popup)
provide('alert', alert)
provide('snackbar', snackbar)
provide('loading', loading)
provide('payLink', payLink)
provide('payShow', payShow)

const { appRouteTransition, isLessThanOverlayNavBreakpoint } = useThemeConfig()
const { width: windowWidth } = useWindowSize()

const passwordChangeWarningValidate = () => {
    const last_change_at = new Date(user_info.value.password_change_at ?? '2024-06-09 12:00:00')
    const now = new Date()
    const diff = now.getTime() - last_change_at.getTime()

    const diffInDays = diff / (1000 * 3600 * 24)
    if(diffInDays >= 90) 
        passwordChangeNoticeDialog.value.show()
}

const fa2RequireNotification = () => {
    if(getUserLevel() >= 35 && getUserLevel() < 50) {
        if(user_info.value.is_2fa_use === false) {
            if(corp.pv_options.paid.use_head_office_withdraw)
                alert.value.show('휴대폰 인증대신 구글 OTP 인증으로 전환하세요.')
            else
                alert.value.show('2FA 인증을 활성화하여 계정의 보안등급을 높일 수 있습니다.<br>안전한 운영을 위해 <b>우측 상단 프로필에서 2차인증</b>을 설정해주세요.')
        }
    }
}

onMounted(() => {
    if(is_pay_link.value !== false) {
        axios.get('/api/v1/manager/popups/currently', {
            params: {
                page_size : 10,
                page : 1,
            }
        })
        .then(r => { 
            if(r.data.content.length)
                popup.value.show(r.data.content)
        })
        .catch(e => { 
            console.log(e) 
        })
        passwordChangeWarningValidate()
        fa2RequireNotification()
    }
})
</script>

<template>
    <VerticalNavLayout 
        :nav-items="navItems" 
        v-if="is_pay_link === false">
        <!-- 👉 navbar -->
        <template #navbar="{ toggleVerticalOverlayNavActive }">
            <div class="d-flex h-100 align-center">
                <VBtn v-if="isLessThanOverlayNavBreakpoint(windowWidth)" icon variant="text" color="default" class="ms-n3"
                    size="small" @click="toggleVerticalOverlayNavActive(true)">
                    <VIcon icon="tabler-menu-2" size="24" />
                </VBtn>

                <div v-if="isLessThanOverlayNavBreakpoint(windowWidth) === false">
                    <template v-if="isFixplus()">
                        <span class="text-primary font-weight-bold">{{ user_info.user_name }}</span>
                        <span v-if="getUserLevel() === 10" class="text-primary font-weight-bold">({{ user_info.mcht_name }})</span>
                        <span v-else-if="getUserLevel() < 35" class="text-primary font-weight-bold">({{ user_info.sales_name }})</span>님 안녕하세요!
                    </template>
                    <template v-else>
                        <span class="text-primary font-weight-bold">{{ user_info.user_name }}</span>님 안녕하세요!
                    </template>
                </div>
                <VSpacer />
                <NavTokenableExpireTime />
                <NavbarZoomSwitcher />
                <NavbarThemeSwitcher />
                <NavbarNotifications v-if="user_info.level >= 35" />
                <UserProfile />
            </div>
        </template>

        <!-- 👉 Pages -->
        <RouterView v-slot="{ Component }">
            <Transition :name="appRouteTransition" mode="out-in">
                <Component :is="Component" />
            </Transition>
            <Snackbar ref="snackbar" />
            <PWASnackbar ref="pwaSnackbar"/>
            <AlertDialog ref="alert" />
            <LoadingDialog ref="loading" />
            <PayWindowCreateDialog ref="payLink"/>
            <PayWindowShowDialog ref="payShow"/>
            <PopupDialog ref="popup"/>
            <PasswordChangeNoticeDialog ref="passwordChangeNoticeDialog"/>
        </RouterView>

        <!-- 👉 Footer -->
        <template #footer>
            <Footer/>
        </template>

        <!-- 👉 Customizer -->
        <TheCustomizer />
    </VerticalNavLayout>

    <div v-else class="d-flex justify-center align-center" style="height: 100%;flex-direction: column;">
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
