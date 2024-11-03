<script setup lang="ts">
import Snackbar from '@/layouts/snackbars/Snackbar.vue'
import { UserAbility } from '@/plugins/casl/AppAbility'
import { useAppAbility } from '@/plugins/casl/useAppAbility'
import router from '@/router'
import { getUserPasswordValidate } from '@/views/users/useStore'
import { axios, pay_token, token_expire_time, user_info } from '@axios'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import corp from '@corp'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
import { VForm } from 'vuetify/components'

const ability = useAppAbility()

const route = useRoute()
const snackbar = ref(<any>(null))
const refVForm = ref<VForm>()

const user_pw = ref('')
const user_pw_check = ref('')
const isPasswordVisible = ref(false)
const isPasswordCheckVisible = ref(false)
const errors = ref<Record<string, string | undefined>>({
    code: undefined,
    message: undefined,
    data: undefined,
})


const default_img = corp.login_img ? corp.login_img : '/storage/images/defaults/logins/1.png'
const authThemeImg = useGenerateImageVariant(default_img, default_img, default_img, default_img, true)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

const getAbilities = (): UserAbility[] => {
    let auth: UserAbility[] = [];
    auth.push({ action: 'manage', subject: 'all' })
    return auth;
}

const resetPassword = () => {
    if (route.query.token === undefined)
        router.replace('/')
    if (user_pw.value != user_pw_check.value) {
        snackbar.value.show('패스워드 및 패스워드 확인이 다릅니다.', 'warning')
    }
    else if (route.query.token) {
        axios.post('/api/v1/auth/reset-password', {
            token: decodeURIComponent(route.query.token as string),
            level: route.query.level,
            user_pw: user_pw.value
        })
            .then(r => {
                const { access_token, user } = r.data
                user['level'] = user['level'] == null ? 10 : user['level']
                const abilities = getAbilities()
                ability.update(abilities);
                pay_token.value = access_token
                user_info.value = user
                token_expire_time.value = r.headers['token-expire-time']

                localStorage.setItem('token-expire-time', token_expire_time.value)
                localStorage.setItem('abilities', JSON.stringify(abilities))
                // Redirect to `to` query if exist or redirect to index route
                router.replace(route.query.to ? String(route.query.to) : '/')
            })
            .catch(e => {
                errors.value = e.response.data
            })
    }
    else
        snackbar.value.show('잘못된 접근입니다.', 'warning')
}

const onSubmit = () => {
    refVForm.value?.validate()
        .then(({ valid: isValid }) => {
            if (isValid)
                resetPassword()
        })
}
const passwordRules = computed(() => {
    return getUserPasswordValidate(Number(route.query.level) === 10 ? 0 : 1, user_pw.value)
})

const passwordCheckRules = computed(() => {
    return getUserPasswordValidate(Number(route.query.level) === 10 ? 0 : 1, user_pw_check.value)
})

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
                    <h4 class="text-h5 mb-1">
                        비밀번호 재설정 🔒
                    </h4>
                    <p class="mb-0 mt-5">
                        최초 접속으로 비밀번호를 재설정합니다.
                        <br>
                        새 비밀번호는 이전에 사용한 비밀번호와 달라야합니다.
                    </p>
                </VCardText>

                <VCardText>
                    <VForm ref="refVForm" @submit.prevent="onSubmit">
                        <VRow no-gutters>
                            <VCol cols="12" style="height: 4.5em;">
                                <VTextField v-model="user_pw" label="새 비밀번호"
                                    :type="isPasswordVisible ? 'text' : 'password'"
                                    :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                                    :rules="passwordRules" @click:append-inner="isPasswordVisible = !isPasswordVisible"
                                    :error-messages="errors.message" />
                            </VCol>
                            <VCol cols="12" style="height: 4.5em;">
                                <VTextField v-model="user_pw_check" label="새 비밀번호 확인" :rules="passwordCheckRules"
                                    :type="isPasswordCheckVisible ? 'text' : 'password'"
                                    :append-inner-icon="isPasswordCheckVisible ? 'tabler-eye-off' : 'tabler-eye'"
                                    @click:append-inner="isPasswordCheckVisible = !isPasswordCheckVisible" />
                            </VCol>
                            <VCol cols="12">
                                <VBtn block type="submit">
                                    새 비밀번호 설정
                                </VBtn>
                            </VCol>
                            <VCol cols="12" style="margin-top: 24px;">
                                <RouterLink class="d-flex align-center justify-center" :to="{ name: 'login' }">
                                    <VIcon icon="tabler-chevron-left" class="flip-in-rtl" />
                                    <span>Back to login</span>
                                </RouterLink>
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
