<script setup lang="ts">
import { UserAbility } from '@/plugins/casl/AppAbility';
import { useAppAbility } from '@/plugins/casl/useAppAbility';
import {axios, pay_token, user_info} from '@axios';
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant';
import corp from '@corp';
import { VNodeRenderer } from '@layouts/components/VNodeRenderer';
import { themeConfig } from '@themeConfig';
import { requiredValidator } from '@validators';
import { VForm } from 'vuetify/components';

import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png';
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png';
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png';
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png';
import authV2MaskDark from '@images/pages/misc-mask-dark.png';
import authV2MaskLight from '@images/pages/misc-mask-light.png';

const authThemeImg = useGenerateImageVariant(authV2LoginIllustrationLight, authV2LoginIllustrationDark, authV2LoginIllustrationBorderedLight, authV2LoginIllustrationBorderedDark, true)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)

const isPasswordVisible = ref(false)

const route = useRoute()
const router = useRouter()

const ability = useAppAbility()

const errors = ref<Record<string, string | undefined>>({
    code : undefined,
    message: undefined,
    data: undefined,
})

const refVForm = ref<VForm>()
const user_name = ref('master')
const user_pw = ref('1234')
/*
  Admin user_name: <strong>admin@demo.com</strong> / Pass: <strong>admin</strong>
  Client user_name: <strong>client@demo.com</strong> / Pass: <strong>client</strong>
*/
const getAbilities = (level: number) : UserAbility[] => {
    let auth :UserAbility[] = [];
    switch (level)  {
        case 0:     auth.push({action: 'manage', subject: 'Auth'}); break;
        case 10:    auth.push({action: 'manage', subject: 'Auth'}); break;
        case 15:    auth.push({action: 'manage', subject: 'all'}); break;
        case 20:    auth.push({action: 'manage', subject: 'all'}); break;
        case 30:    auth.push({action: 'manage', subject: 'all'}); break;
        case 35:    auth.push({action: 'read', subject: 'all'}); break;
        case 40:    auth.push({action: 'manage', subject: 'all'}); break;
        case 50:    auth.push({action: 'manage', subject: 'all'}); break;            
    }
    return auth;
}
const login = () => {
    axios.post('api/v1/auth/sign-in', {brand_id: corp.id, user_name: user_name.value, user_pw: user_pw.value })
    .then(r => {
        const {access_token, user} = r.data
        const abilities = getAbilities(user['level']);
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
        login()
    })
}
</script>

<template>
  <VRow
    no-gutters
    class="auth-wrapper"
  >
    <VCol
      lg="8"
      class="d-none d-lg-flex"
    >
      <div class="position-relative auth-bg rounded-lg w-100 ma-8 me-0">
        <div class="d-flex align-center justify-center w-100 h-100">
          <VImg
            max-width="505"
            :src="authThemeImg"
            class="auth-illustration mt-16 mb-2"
          />
        </div>

        <VImg
          :src="authThemeMask"
          class="auth-footer-mask"
        />
      </div>
    </VCol>

    <VCol
      cols="12"
      lg="4"
      class="d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-4"
      >
        <VCardText>
          <VNodeRenderer
            :nodes="themeConfig.app.logo"
            class="mb-6"
          />

          <h5 class="text-h5 font-weight-semibold mb-1">
            {{ themeConfig.app.title }}에 오신것을 환영합니다! 👋🏻
          </h5>
          <p class="mb-0">
            Please sign-in to your account and start the adventure
          </p>
        </VCardText>
        <VCardText>
          <VForm
            ref="refVForm"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- user_name -->
              <VCol cols="12">
                <VTextField
                  v-model="user_name"
                  label="아이디 입력"
                  type="user_name"
                  :rules="[requiredValidator]"
                  :error-messages="errors.message"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <VTextField
                  v-model="user_pw"
                  label="패스워드 입력"
                  :rules="[requiredValidator]"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <div class="d-flex align-center flex-wrap justify-space-between mt-2 mb-4">
                  <RouterLink
                    class="text-primary ms-2 mb-1"
                    :to="{ name: 'forgot-password' }"
                  >
                    패스워드를 잊으셨나요?
                  </RouterLink>
                </div>

                <VBtn
                  block
                  type="submit"
                >
                  Login
                </VBtn>
              </VCol>

              <!-- create account -->
              <VCol
                cols="12"
                class="text-center"
              >
                <span>새로운 고객이신가요?</span>
                <RouterLink
                  class="text-primary ms-2"
                  :to="{ name: '' }"
                >
                  회원가입
                </RouterLink>
              </VCol>
              <VCol
                cols="12"
                class="d-flex align-center"
              >
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
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
