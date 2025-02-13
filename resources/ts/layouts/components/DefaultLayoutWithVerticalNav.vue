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

import SalesRecommenderCodeEialog from '@/layouts/dialogs/salesforces/SalesRecommenderCodeEialog.vue'
import HolidayDlg from '@/layouts/dialogs/services/HolidayDlg.vue'
import PayWindowShowDialog from '@/layouts/dialogs/transactions/PayWindowShowDialog.vue'
import PasswordChangeNoticeDialog from '@/layouts/dialogs/users/PasswordChangeNoticeDialog.vue'
import PhoneNum2FAVertifyDialog from '@/layouts/dialogs/users/PhoneNum2FAVertifyDialog.vue'
import AlertDialog from '@/layouts/dialogs/utils/AlertDialog.vue'
import LoadingDialog from '@/layouts/dialogs/utils/LoadingDialog.vue'
import PopupDialog from '@/layouts/dialogs/utils/PopupDialog.vue'

import PWASnackbar from '@/layouts/snackbars/PWASnackbar.vue'
import Snackbar from '@/layouts/snackbars/Snackbar.vue'

import corp from '@/plugins/corp'
import { isFixplus } from '@/plugins/fixplus'
import { axios, getUserLevel, user_info } from '@axios'

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const loading = <any>(inject('loading'))
const errorHandler = <any>(inject('$errorHandler'))

const popup = ref()
const payShow = ref()
const pwaSnackbar = ref()
const holidayDlg = ref()
const phoneNum2FAVertifyDialog = ref()
const passwordChangeNoticeDialog = ref()
const salesRecommenderCodeEialog = ref()

provide('popup', popup)
provide('payShow', payShow)
provide('holidayDlg', holidayDlg)
provide('pwaSnackbar', pwaSnackbar)
provide('phoneNum2FAVertifyDialog', phoneNum2FAVertifyDialog)
provide('salesRecommenderCodeEialog', salesRecommenderCodeEialog)

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
            if(corp.pv_options.paid.use_head_office_withdraw)
                alert.value.show('휴대폰 인증대신 구글 OTP 인증으로 전환하세요.')
            else
            {
                let message = '2FA 인증을 활성화하여 계정의 보안등급을 높일 수 있습니다.<br>안전한 운영을 위해 <b>우측 상단 프로필에서 2차인증</b>을 설정해주세요.'
                if(getUserLevel() >= 40)
                    message += `<br><br><h4 class='text-error'>※ 본사등급의 경우 필수적으로 2FA 인증을 활성화 할 것을 권고합니다. ※</h4>`
                alert.value.show(message)
            }
        }
    }
}

onMounted(async () => {
    await nextTick()
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
        const r = errorHandler(e)
    })
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
        <SalesRecommenderCodeEialog ref="salesRecommenderCodeEialog"
            :key="user_info.id"/>
        <PWASnackbar ref="pwaSnackbar"/>
        <AlertDialog ref="alert" />
        <LoadingDialog ref="loading" />
        <HolidayDlg ref="holidayDlg"/>
        <PayWindowShowDialog ref="payShow"/>
        
        <PopupDialog ref="popup"/>
        <PhoneNum2FAVertifyDialog ref="phoneNum2FAVertifyDialog"/>
        <PasswordChangeNoticeDialog ref="passwordChangeNoticeDialog"/>

        <template #footer>
            <Footer/>
        </template>
        <!--
            <TheCustomizer />
        -->
    </VerticalNavLayout>
</template>
