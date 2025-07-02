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
import { VerticalNavLayout } from '@layouts'

import ActivityHistoryTargetDialog from '@/layouts/dialogs/histories/ActivityHistoryTargetDialog.vue'
import PasswordChangeNoticeDialog from '@/layouts/dialogs/users/PasswordChangeNoticeDialog.vue'
import PhoneNum2FAVertifyDialog from '@/layouts/dialogs/users/PhoneNum2FAVertifyDialog.vue'
import AlertDialog from '@/layouts/dialogs/utils/AlertDialog.vue'
import LoadingDialog from '@/layouts/dialogs/utils/LoadingDialog.vue'

import PWASnackbar from '@/layouts/snackbars/PWASnackbar.vue'
import Snackbar from '@/layouts/snackbars/Snackbar.vue'

import { axios, getUserLevel, getUserMutual, user_info } from '@axios'

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const loading = <any>(inject('loading'))
const errorHandler = <any>(inject('$errorHandler'))
const activityHistoryTargetDialog = ref()

const pwaSnackbar = ref()
const phoneNum2FAVertifyDialog = ref()
const passwordChangeNoticeDialog = ref()

provide('pwaSnackbar', pwaSnackbar)
provide('phoneNum2FAVertifyDialog', phoneNum2FAVertifyDialog)
provide('activityHistoryTargetDialog', activityHistoryTargetDialog)

const { appRouteTransition, isLessThanOverlayNavBreakpoint } = useThemeConfig()
const { width: windowWidth } = useWindowSize()

const passwordChangeWarningValidate = () => {
    const last_change_at = new Date(user_info.value.password_change_at ?? '2024-06-15 17:20:00')
    const now = new Date()
    const diff = now.getTime() - last_change_at.getTime()

    const diffInDays = diff / (1000 * 3600 * 24)
    if(diffInDays >= 90) {
        if(passwordChangeNoticeDialog.value)
            passwordChangeNoticeDialog.value.show()
        else
            console.error('passwordChangeNoticeDialog is not initialized');
    }
}

const fa2RequireNotification = () => {
    if(getUserLevel() >= 35 && getUserLevel() < 50) {
        if(user_info.value.is_2fa_use === false) {
            let message = '2FA 인증을 활성화하여 계정의 보안등급을 높일 수 있습니다.<br>안전한 운영을 위해 <b>우측 상단 프로필에서 2차인증</b>을 설정해주세요.'
            if(getUserLevel() >= 40)
                message += `<br><br><h4 class='text-error'>※ 본사등급의 경우 필수적으로 2FA 인증을 활성화 할 것을 권고합니다. ※</h4>`
            alert.value.show(message)
        }
    }
}

onMounted(async () => {
    await nextTick()
    passwordChangeWarningValidate()
    fa2RequireNotification()    
})

</script>

<template>
    <VerticalNavLayout 
        :nav-items="navItems">
        <!-- 👉 navbar -->
        <template #navbar="{ toggleVerticalOverlayNavActive }">
            <div class="d-flex h-100 align-center">
                <VBtn v-if="isLessThanOverlayNavBreakpoint(windowWidth)" icon variant="text" color="default" class="ms-n3"
                    size="small" @click="toggleVerticalOverlayNavActive(true)">
                    <VIcon icon="tabler-menu-2" size="24" />
                </VBtn>

                <div v-if="isLessThanOverlayNavBreakpoint(windowWidth) === false">
                        <span class="text-primary font-weight-bold">{{ getUserMutual() }}</span>님 안녕하세요 !
                </div>
                <VSpacer />
                <NavTokenableExpireTime />
                <NavbarZoomSwitcher />
                <NavbarThemeSwitcher />
                <NavbarNotifications v-if="getUserLevel() >= 35" />
                <UserProfile />
            </div>
        </template>
        <RouterView v-slot="{ Component, route }">
            <KeepAlive :include="['[id]', 'index', 'home', 'reply', 'create']" :exclude="['']" :max="20">
                <Component :is="Component" :key="route.fullPath" />
            </KeepAlive>
        </RouterView>

        <Snackbar ref="snackbar" />
        <PWASnackbar ref="pwaSnackbar"/>
        <AlertDialog ref="alert" />
        <LoadingDialog ref="loading" />
        
        <PhoneNum2FAVertifyDialog ref="phoneNum2FAVertifyDialog"/>
        <PasswordChangeNoticeDialog ref="passwordChangeNoticeDialog"/>
        <ActivityHistoryTargetDialog ref="activityHistoryTargetDialog"/>

        <template #footer>
            <Footer/>
        </template>
        <!--
            <TheCustomizer />
        -->
    </VerticalNavLayout>
</template>
