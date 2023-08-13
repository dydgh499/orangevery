<script setup lang="ts">
import { UserAbility } from '@/plugins/casl/AppAbility'
import { useAppAbility } from '@/plugins/casl/useAppAbility'
import { axios, pay_token, user_info } from '@axios'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import corp from '@corp'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { requiredValidatorV2 } from '@validators'
import { VForm } from 'vuetify/components'
import Snackbar from '@/layouts/snackbars/Snackbar.vue'

import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'

import authV2LoginDefault1 from '@images/pages/auth-v2-login-default1.png'

const default_img = corp.login_img ? corp.login_img : authV2LoginDefault1
const authThemeImg = useGenerateImageVariant(default_img, default_img, default_img, default_img, true)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

const isPasswordVisible = ref(false)

const route = useRoute()
const router = useRouter()

const ability = useAppAbility()

const errors = ref<Record<string, string | undefined>>({
    code: undefined,
    message: undefined,
    data: undefined,
})

const snackbar = ref(null)
const refVForm = ref<VForm>()
const user_name = ref('')
const user_pw = ref('')

const getAbilities = (): UserAbility[] => {
    let auth: UserAbility[] = [];
    auth.push({ action: 'manage', subject: 'all' })
    return auth;
}
const login = () => {
    axios.post('/api/v1/auth/sign-in', { brand_id: corp.id, user_name: user_name.value, user_pw: user_pw.value })
        .then(r => {
            const { access_token, user } = r.data
            user['level'] = user['level'] == null ? 10 : user['level']
            const abilities = getAbilities()
            ability.update(abilities);
            pay_token.value = access_token
            user_info.value = user
            localStorage.setItem('abilities', JSON.stringify(abilities))
            // Redirect to `to` query if exist or redirect to index route
            router.replace(route.query.to ? String(route.query.to) : '/')
        })
        .catch(e => {
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
    <VRow no-gutters class="auth-wrapper">
        <VCol lg="8" class="d-none d-lg-flex">
            <div class="position-relative auth-bg rounded-lg w-100 ma-8 me-0">
                <div class="d-flex align-center justify-center w-100 h-100">
                    <VImg max-width="605" :src="authThemeImg" class="auth-illustration mt-16 mb-2" />
                </div>

                <VImg :src="authThemeMask" class="auth-footer-mask" />
            </div>
        </VCol>

        <VCol cols="12" lg="4" class="d-flex align-center justify-center">
            <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-4">
                <VCardText>
                    <VNodeRenderer :nodes="themeConfig.app.logo" class="mb-6" />
                    <h5 class="text-h5 font-weight-semibold mb-1">
                        {{ themeConfig.app.title }}에 오신것을 환영합니다! 👋🏻
                    </h5>
                </VCardText>
                <VCardText>
                    <VForm ref="refVForm" @submit.prevent="onSubmit">
                        <VRow no-gutters>
                            <!-- user_name -->
                            <VCol cols="12">
                                <VTextField v-model="user_name" label="아이디 입력" type="user_name" :rules="[requiredValidatorV2(user_name, '아이디')]"
                                    :error-messages="errors.message" />
                            </VCol>
                            <!-- password -->
                            <VCol cols="12" style="margin-top: 24px;">
                                <VTextField v-model="user_pw" label="패스워드 입력" :rules="[requiredValidatorV2(user_pw, '패스워드')]"
                                    :type="isPasswordVisible ? 'text' : 'password'"
                                    :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                                    @click:append-inner="isPasswordVisible = !isPasswordVisible"/>

                                <div class="d-flex align-center flex-wrap justify-space-between mt-2 mb-4">
                                    <div class="text-primary ms-2 mb-1" style="cursor: pointer;"
                                        @click="forgotPassword()">
                                        패스워드를 잊으셨나요?
                                    </div>
                                </div>

                                <VBtn block type="submit">
                                    Login
                                </VBtn>
                            </VCol>

                            <!-- create account -->
                            <VCol cols="12" class="text-center">
                            </VCol>
                            <VCol cols="12" class="d-flex align-center">
                            </VCol>
                        </VRow>
                    </VForm>
                </VCardText>
            </VCard>
        </VCol>
    </VRow>
    <Snackbar ref="snackbar" />
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";

@keyframes scale {
  0% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.1);
  }

  100% {
    transform: scale(1);
  }
}

.auth-illustration {
  animation: scale 14s infinite;
}
</style>

<route lang="yaml">
meta:
  layout: blank
  action: read
  subject: Auth
  redirectIfLoggedIn: true
</route>
