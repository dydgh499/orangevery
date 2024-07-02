<script lang="ts" setup>
import { pinInputEvent } from '@/@core/utils/pin_input_event';
import { axios, getUserType, user_info } from '@/plugins/axios';
import { timer } from '@core/utils/timer';

const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const errors = ref<Record<string, string | undefined>>({
    code: undefined,
    message: undefined,
    data: undefined,
})

const visible = ref(false)
const qrcode_url = ref('')
const { digits, ref_opt_comp, handleKeyDown, defaultStyle} = pinInputEvent(6)
const { countdown_time, countdownTimer, startTimer } = timer(300)

const user_pw = ref('')
const user_type = getUserType().id === 2 ? 'services/operators' : 'salesforces'

const current_step = ref(0)
const steps = [
  {
    title: '인증 사전 준비',
    subtitle: '2FA APP 설치하기',
  },
  {
    title: 'QRCode 스캔',
    subtitle: '인증수단 등록',
  },
  {
    title: '인증수단 검증',
    subtitle: '등록완료',
  },
]

const show = async () => {
    current_step.value = 0
    visible.value = true
}

const onAgree = async () => {
    const params = {
        verify_code : digits.value.join(''),
        user_pw: user_pw.value,
    }
    axios.post(`/api/v1/manager/${user_type}/${user_info.value.id}/2fa-qrcode/create-vertify`, params)
        .then(r => {
            snackbar.value.show('2FA 설정에 성공하였습니다.<br>차후 로그인부터 2FA인증이 활성화 됩니다.', 'success')
            user_info.value.is_2fa_use = true
            visible.value = false
        })
        .catch(async e => {
            errors.value = e.response.data
            if(e.response.data.code === 952) {
                snackbar.value.show('핀번호 또는 패스워드가 정확하지 않습니다.', 'error') 
            }
            const r = errorHandler(e)
        })
};

const onCancel = () => {
    visible.value = false

}

const stepUp = async () => {
    current_step.value++
    if(current_step.value === 1 && qrcode_url.value === '') {
        const res = await axios.post(`/api/v1/manager/${user_type}/${user_info.value.id}/2fa-qrcode`)
        qrcode_url.value = res.data.qrcode_url
        startTimer()
    }
}

watchEffect(() => {
    if(countdown_time.value <= 0)
        qrcode_url.value = ''
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="800">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="onCancel()" />
        <!-- Dialog Content -->
        <VCard class="pa-5 pa-sm-8">
            <VCardTitle class="text-h6 font-weight-bold mb-2">2FA 설정하기</VCardTitle>
            <VCardItem class="text-start">
                <VRow>
                    <VCol cols="12" md="4">
                        <AppStepper v-model:current-step="current_step" :isActiveStepValid="true"
                        direction="vertical" :items="steps"/>
                    </VCol>
                    <VCol cols="12" md="8">
                            <VForm>
                                <VWindow v-model="current_step" class="disable-tab-transition">
                                    <VWindowItem>
                                            <span class="text-base">
                                                인증이 진행될 Authenticator 어플리케이션을 먼저 휴대폰에 설치해주세요.
                                            </span>
                                            <h5>(이미 설치되어 있는 경우 스킵하세요.)</h5>
                                            <br>
                                            <span class="text-base">
                                                설치가 완료되었다면 
                                                <VBtn type="button" aria-readonly="" size="small">
                                                    다음
                                                    <VIcon end icon="tabler-arrow-right" />
                                                </VBtn>
                                                을 클릭합니다.
                                            </span>
                                    </VWindowItem>
                                    <VWindowItem>
                                            <div>
                                                <span class="text-base">
                                                    Authenticator 어플리케이션에서 아래 QRCode를 스캔하여 인증수단을 등록해주세요.
                                                </span>
                                                <h5>QRCode는 5분간 유효합니다. (5분 이내에 인증이 완료되지 않을 시 핀번호는 만료됩니다.)</h5>
                                            </div>
                                            <div style="margin-right: auto;margin-left: auto; text-align: center;">
                                                <div v-html="qrcode_url"></div>
                                                <div>
                                                    <span>만료시간:</span>
                                                    <span id="countdown" class="text-primary">{{ countdownTimer }}</span>
                                                </div>
                                            </div>
                                            <br>
                                            <span class="text-base">
                                                어플리케이션에 6자리 랜덤 핀번호가 추가된 것을 확인하였으면
                                                <VBtn type="button" aria-readonly="" size="small">
                                                    다음
                                                    <VIcon end icon="tabler-arrow-right" />
                                                </VBtn>
                                                을 클릭합니다.
                                            </span>
                                    </VWindowItem>
                                    <VWindowItem>
                                            <div style="margin-bottom: 2em;">
                                                <span class="text-base">
                                                Authenticator 에서 확인되는 6자리 핀번호를 입력해주세요.
                                                </span>
                                                <br>
                                                <div ref="ref_opt_comp" class="d-flex align-center gap-4">
                                                    <VTextField v-for="i in 6" :key="i" :model-value="digits[i - 1]" type="number"
                                                        v-bind="defaultStyle" maxlength="1" @input="handleKeyDown(i)" />
                                                </div>
                                            </div>
                                            <div>
                                                <span class="text-base">
                                                로그인에 사용했던 패스워드를 입력해주세요.
                                                </span>
                                                <br>
                                                <VTextField v-model="user_pw" type="password"
                                                    prepend-inner-icon="tabler-lock" placeholder="패스워드 입력"
                                                    persistent-placeholder 
                                                    :error-messages="errors.message"/>
                                            </div>
                                            <br>
                                            <span class="text-base">
                                                핀번호 및 패스워드를 입력한 후
                                                <VBtn type="button" aria-readonly="" size="small" color="success">
                                                    완료
                                                    <VIcon end icon="tabler-check" />
                                                </VBtn>
                                                를 클릭합니다.
                                            </span>
                                            <h5>(추후 2차 인증을 재설정할 시 본사등급의 휴대폰 인증후 재발급 가능합니다.)</h5>
                                    </VWindowItem>
                                </VWindow>
                            </VForm>
                            <div class="d-flex flex-wrap gap-4 justify-sm-space-between justify-center mt-8">
                                <VBtn
                                    color="secondary"
                                    variant="tonal"
                                    :disabled="current_step === 0"
                                    @click="current_step--"
                                >
                                    <VIcon
                                    icon="tabler-arrow-left"
                                    start
                                    class="flip-in-rtl"
                                    />
                                    이전
                                </VBtn>
                                <VBtn
                                    v-if="steps.length - 1 === current_step"
                                    color="success"
                                    append-icon="tabler-check"
                                    @click="onAgree"
                                >
                                    완료
                                </VBtn>
                                <VBtn
                                    v-else
                                    @click="stepUp()"
                                >
                                    다음
                                    <VIcon
                                    icon="tabler-arrow-right"
                                    end
                                    class="flip-in-rtl"
                                    />
                                </VBtn>
                            </div>
                    </VCol>
                </VRow>
            </VCardItem>
        </VCard>
    </VDialog>
</template> 
