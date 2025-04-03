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
import { lengthValidator, passwordValidatorV2, requiredValidatorV2 } from '@validators'
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

const snackbar = ref(null)
const refVForm = ref<VForm>()

const ceo_name = ref('')
const phone_num = ref('')
const business_num = ref('')

const user_name = ref('')
const user_pw = ref('')
const user_pw_check = ref('')

const getAbilities = (): UserAbility[] => {
    let auth: UserAbility[] = [];
    auth.push({ action: 'manage', subject: 'all' })
    return auth;
}
const signUp = () => {
    const params = {
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
                        <span class="text-capitalize">{{ corp.name }}</span>에 오신것을 환영합니다! 👋🏻
                    </h4>
                    <p class="mb-0">
                        서비스 운영에 사용할 본사계정을 등록해주세요.
                    </p>
                </VCardText>
                <VCardText>
                    <VForm ref="refVForm" @submit.prevent="onSubmit">
                        <VRow>
                            <VCol cols="12" style="height: 4.5em;">
                                <VTextField v-model="ceo_name" label="대표자명 입력" type="ceo_name"
                                    :rules="[requiredValidatorV2(ceo_name, '대표자명')]" />
                            </VCol>
                            <VCol cols="12" style="height: 4.5em;">
                                <VTextField v-model="phone_num" label="전화번호 입력" type="phone_num"
                                    :rules="[requiredValidatorV2(phone_num, '전화번호')]" />
                            </VCol>
                            <VCol cols="12" style="height: 4.5em;">
                                <VTextField v-model="business_num" label="사업자등록번호 입력" type="business_num"
                                    :rules="[requiredValidatorV2(business_num, '사업자등록번호')]" />
                            </VCol>
                            <VCol cols="12" style="height: 4.5em;">
                                <VTextField v-model="user_name" label="아이디 입력" type="user_name" :rules="[requiredValidatorV2(user_name, '아이디'), lengthValidator(user_name, 8)]"
                                    :error-messages="errors.message" />
                            </VCol>
                            <VCol cols="12" style="height: 4.5em;">
                                <VTextField v-model="user_pw" label="패스워드 입력" :rules="[requiredValidatorV2(user_pw, '패스워드'), passwordValidatorV2]"
                                    :type="isPasswordVisible ? 'text' : 'password'"
                                    :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                                    @click:append-inner="isPasswordVisible = !isPasswordVisible" />
                            </VCol>
                            <VCol cols="12">
                                <div style="height: 4em;">
                                    <VTextField v-model="user_pw_check" label="패스워드 확인" :rules="[requiredValidatorV2(user_pw_check, '패스워드확인'), passwordValidatorV2]"
                                    :type="isPasswordVisible ? 'text' : 'password'"
                                    :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                                    @click:append-inner="isPasswordVisible = !isPasswordVisible" />
                                </div>
                                <VBtn block type="submit">
                                    REGISTER
                                </VBtn>
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
</style>

<route lang="yaml">
meta:
  layout: blank
  action: read
  subject: Auth
  redirectIfLoggedIn: true
</route>
