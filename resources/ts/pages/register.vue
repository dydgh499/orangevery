<script setup lang="ts">
import Snackbar from '@/layouts/snackbars/Snackbar.vue'
import { UserAbility } from '@/plugins/casl/AppAbility'
import { useAppAbility } from '@/plugins/casl/useAppAbility'
import router from '@/router'
import { axios, pay_token, user_info } from '@axios'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import corp from '@corp'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { requiredValidatorV2 } from '@validators'
import { VForm } from 'vuetify/components'

import authV2LoginDefault1 from '@images/pages/auth-v2-login-default1.png'

const default_img = corp.login_img ? corp.login_img : authV2LoginDefault1
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

const snackbar = ref(null)
const refVForm = ref<VForm>()

const ceo_name = ref('')
const phone_num = ref('')
const business_num = ref('')

const user_name = ref('')
const user_pw = ref('')
const user_pw_check = ref('')
/*
  Admin user_name: <strong>admin@demo.com</strong> / Pass: <strong>admin</strong>
  Client user_name: <strong>client@demo.com</strong> / Pass: <strong>client</strong>
*/
const getAbilities = (): UserAbility[] => {
    let auth: UserAbility[] = [];
    auth.push({ action: 'manage', subject: 'all' })
    return auth;
}
const signUp = () => {
    const params = {
        brand_id: corp.id,
        ceo_name: ceo_name.value,
        phone_num: phone_num.value,
        business_num: business_num.value,
        user_name: user_name.value,
        user_pw: user_pw.value,
    }
    axios.post('/api/v1/auth/sign-up', params)
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
const onSubmit = () => {
    refVForm.value?.validate()
        .then(({ valid: isValid }) => {
            if (isValid)
                signUp()
        })
}
const sameValidaor = () => {
    return user_pw.value === user_pw_check.value || '패스워드와 패스워드 확인이 같지 않습니다.'
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
            <VCard flat :max-width="500" class="mt-sm-0 pa-4">
                <VCardText>
                    <VNodeRenderer :nodes="themeConfig.app.logo" class="mb-6" />

                    <h5 class="text-h5 font-weight-semibold mb-1">
                        {{ themeConfig.app.title }}에 오신것을 환영합니다! 👋🏻
                    </h5>
                    <p class="mb-0">
                        서비스 운영에 사용할 본사 계정을 등록해주세요.
                    </p>
                </VCardText>
                <VCardText>
                    <VForm ref="refVForm" @submit.prevent="onSubmit">
                        <VRow>

                            <VCol cols="12">
                                <VTextField v-model="ceo_name" label="대표자명 입력" type="ceo_name"
                                    :rules="[requiredValidatorV2(ceo_name, '대표자명')]" />
                            </VCol>
                            <VCol cols="12">
                                <VTextField v-model="phone_num" label="전화번호 입력" type="phone_num"
                                    :rules="[requiredValidatorV2(phone_num, '전화번호')]" />
                            </VCol>
                            <VCol cols="12">
                                <VTextField v-model="business_num" label="사업자등록번호 입력" type="business_num"
                                    :rules="[requiredValidatorV2(business_num, '사업자등록번호')]" />
                            </VCol>
                            <!-- user_name -->
                            <VCol cols="12">
                                <VTextField v-model="user_name" label="아이디 입력" type="user_name" :rules="[requiredValidatorV2(user_name, '아이디')]"
                                    :error-messages="errors.message" />
                            </VCol>
                            <VCol cols="12">
                                <VTextField v-model="user_pw" label="패스워드 입력" :rules="[requiredValidatorV2(user_pw, '사업자등록번호')]"
                                    :type="isPasswordVisible ? 'text' : 'password'"
                                    :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                                    @click:append-inner="isPasswordVisible = !isPasswordVisible" />
                            </VCol>
                            <!-- password -->
                            <VCol cols="12">
                                <VTextField v-model="user_pw_check" label="패스워드 확인" :rules="[requiredValidatorV2(user_pw_check, '사업자등록번호'), sameValidaor]"
                                    :type="isPasswordVisible ? 'text' : 'password'"
                                    :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                                    @click:append-inner="isPasswordVisible = !isPasswordVisible" />

                                <div class="d-flex align-center flex-wrap justify-space-between mt-2 mb-4">
                                    <div class="text-primary ms-2 mb-1" style="cursor: pointer;">
                                    </div>
                                </div>
                                <VBtn block type="submit">
                                    Register
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
