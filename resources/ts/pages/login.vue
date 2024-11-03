<script setup lang="ts">
import Google2FAVertifyDialog from '@/layouts/dialogs/users/Google2FAVertifyDialog.vue'
import PasswordAuthDialog from '@/layouts/dialogs/users/PasswordAuthDialog.vue'
import Snackbar from '@/layouts/snackbars/Snackbar.vue'
import { UserAbility } from '@/plugins/casl/AppAbility'
import { useAppAbility } from '@/plugins/casl/useAppAbility'
import { isBrightFix } from '@/plugins/fixplus'
import router from '@/router'
import { axios, getUserLevel, pay_token, token_expire_time, user_info } from '@axios'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import corp from '@corp'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { requiredValidatorV2 } from '@validators'
import { VForm } from 'vuetify/components'


const default_img = corp.login_img ? corp.login_img : '/utils/logins/1.png'
const authThemeImg = useGenerateImageVariant(default_img, default_img, default_img, default_img, true)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

const isPasswordVisible = ref(false)

const route = useRoute()
const ability = useAppAbility()

const errors = ref<Record<string, string | undefined>>({
    code: undefined,
    message: undefined,
    data: undefined,
})

const snackbar = ref()
const passwordAuthDialog = ref()
const google2FAVertifyDialog = ref()
const refVForm = ref<VForm>()
const user_name = ref('')
const user_pw = ref('')
const token = ref('')

provide('snackbar', snackbar)

const getAbilities = (): UserAbility[] => {
    let auth: UserAbility[] = [];
    auth.push({ action: 'manage', subject: 'all' })
    return auth;
}

const login = () => {
    axios.post('/api/v1/auth/sign-in', { brand_id: corp.id, user_name: user_name.value, user_pw: user_pw.value, token: token.value })
        .then(r => {
            const { access_token, user } = r.data
            user['level'] = user['level'] == null ? 10 : user['level']
            const abilities = getAbilities()
            ability.update(abilities)
            pay_token.value = access_token
            user_info.value = user            
            token_expire_time.value = r.headers['token-expire-time']

            localStorage.setItem('token-expire-time', token_expire_time.value)
            localStorage.setItem('abilities', JSON.stringify(abilities))
            // Redirect to `to` query if exist or redirect to index route
            if(isBrightFix() && getUserLevel() > 10)
                router.replace('transactions/summary')
            else
                router.replace(route.query.to ? String(route.query.to) : '/')
        })
        .catch(async e => {
            if(e.response.data.code === 955) {
                router.replace('reset-password?token='+encodeURIComponent(e.response.data.data.token)+"&level="+encodeURIComponent(e.response.data.data.level))
            }
            else if(e.response.data.code === 956) {
                let phone_num = e.response.data.data.phone_num
                if(phone_num) {
                    phone_num = phone_num.replaceAll(' ', '').replaceAll('-', '')
                    token.value = await passwordAuthDialog.value.show(phone_num)
                    if(token.value !== '')
                        login()
                }
                else
                    snackbar.value.show('등록된 로그인정보가 없습니다.<br>관리자에게 문의해주세요.', 'warning')
            }
            else if(e.response.data.code === 957) {
                token.value = await google2FAVertifyDialog.value.show(user_name.value, user_pw.value)
                if(token.value !== '')
                    login()
            }
            else
                pay_token.value = ''
            console.log(e)
            errors.value = e.response.data
        })
}

const forgotPassword = () => {
    snackbar.value.show('각 영업점들에게 전화해 주세요.', 'warning')
}

const onSubmit = () => {
    refVForm.value?.validate()
        .then(({ valid: isValid }) => {
            if (isValid)
                login()
        })
}
</script>

<template>
    <VRow no-gutters class="auth-wrapper bg-surface">
        <VCol lg="8" class="d-none d-md-flex">
            <div class="position-relative bg-background rounded-lg w-100 ma-8 me-0">
                <div class="d-flex align-center justify-center w-100 h-100">
                    <VImg max-width="605" :src="authThemeImg" class="auth-illustration mt-16 mb-2" />
                </div>
                <VImg :src="authThemeMask" class="auth-footer-mask" />
            </div>
        </VCol>

        <VCol cols="12" lg="4" class="auth-card-v2 d-flex align-center justify-center">
            <VCard flat class="mt-sm-0 pa-4" :max-width="500">
                <VCardText>
                    <VNodeRenderer :nodes="themeConfig.app.logo" class="mb-6" />
                    <h4 class="text-h4 mb-1 font-weight-bold">
                        <span class="text-capitalize">{{ themeConfig.app.title }}</span>에 오신것을 환영합니다! 👋🏻
                    </h4>
                </VCardText>
                <VCardText>
                    <VForm ref="refVForm" @submit.prevent="onSubmit">
                        <VRow>
                            <VCol cols="12" style="height: 4.5em;">
                                <VTextField v-model="user_name" label="아이디 입력" type="user_name" :rules="[requiredValidatorV2(user_name, '아이디')]"
                                    :error-messages="errors.message" />
                            </VCol>
                            <VCol cols="12">
                                <div style="height: 4em;">
                                    <VTextField v-model="user_pw" label="패스워드 입력" :rules="[requiredValidatorV2(user_pw, '패스워드')]"
                                    :type="isPasswordVisible ? 'text' : 'password'"
                                    :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                                    @click:append-inner="isPasswordVisible = !isPasswordVisible"/>
                                </div>

                                <div class="d-flex align-center flex-wrap mb-4">
                                    <div class="text-primary" style="cursor: pointer;"
                                        @click="forgotPassword()">
                                        패스워드를 잊으셨나요?
                                    </div>
                                </div>

                                <VBtn block type="submit">
                                    LOGIN
                                </VBtn>
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
    <Snackbar ref="snackbar" />
    <PasswordAuthDialog ref="passwordAuthDialog"/>
    <Google2FAVertifyDialog ref="google2FAVertifyDialog"/>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";
</style>

<route lang="yaml">
meta:
  layout: blank
  action: read
  subject: Auth
  redirectIfLoggedIn: true
</route>
