<script setup lang="ts">
import { timerV1 } from '@/@core/utils/timer';
import { Merchandise } from '@/views/types';
import { axios, getUserLevel } from '@axios';
import { pinInputEvent } from '@core/utils/pin_input_event';
import corp from '@corp';

interface Props {
    totalInput?: number,
    default?: string,
    phone_num: string,
    merchandise: Merchandise,
}

const props = withDefaults(defineProps<Props>(), {
    totalInput: 6,
    default: '',
})

const emits = defineEmits(['update:token'])

const snackbar = <any>(inject('snackbar'))
const button_status = ref(0)

const { digits, ref_opt_comp, handleKeyDown, defaultStyle} = pinInputEvent(props.totalInput)

const { countdown_timer, countdownTimer, restartTimer } = timerV1(180, 1100)

digits.value = props.default.split('')

if (getUserLevel() >= 35 && props.merchandise.id !== -1) {
    button_status.value = 2
    emits('update:token', true)
}

const handleKeyDownEvent = (index: number) => {
    handleKeyDown(index)
    if (digits.value.join('').length === props.totalInput)
            verification()
}

const requestCodeIssuance = async () => {
    try {
        const r = await axios.post('/api/v1/bonaejas/mobile-code-issuance', { 
            phone_num: props.phone_num, 
            brand_id: corp.id, 
            mcht_id: props.merchandise.id
        })
        snackbar.value.show('휴대폰번호로 인증번호를 보냈습니다!<br>6자리 인증번호를 입력해주세요.', 'success')
        button_status.value = 1

        if (countdown_timer)
            clearInterval(countdown_timer)

        restartTimer()
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
    }
}

const verification = async () => {
    if (button_status.value === 0) {
        await requestCodeIssuance()
    }
    else if (button_status.value === 1) {
        try {
            const r = await axios.post('/api/v1/bonaejas/mobile-code-auth', { 
                phone_num: props.phone_num, 
                verification_number: digits.value.join(''),
                brand_id: corp.id, 
                mcht_id: props.merchandise.id
            })
            emits('update:token', r.data.token)
            button_status.value = 2
            snackbar.value.show('인증에 성공하였습니다.', 'success')
        }
        catch(e: any) {
            snackbar.value.show(e.response.data.message, 'error')
        }
    }
    else if (button_status.value === 2) {
        snackbar.value.show('이미 인증에 성공하였습니다.', 'success')
    }
}
</script>
<template>
    <div>
        <VCol cols="12" style="padding: 0;" v-if="button_status === 1">
            <div style="margin-top: 1.5em;">
                <VCol>
                    <h6 class="text-base font-weight-bold mb-3">
                        6자리 인증번호를 입력해주세요.
                    </h6>
                    <div ref="ref_opt_comp" class="d-flex align-center gap-4">
                        <VTextField v-for="i in props.totalInput" :key="i" :model-value="digits[i - 1]" type="number"
                            v-bind="defaultStyle" maxlength="1" @input="handleKeyDownEvent(i)" />
                    </div>
                </VCol>
                <VCol class="retry-container">
                    <span @click="requestCodeIssuance()" class="text-primary retry-text">인증번호 재발송</span>
                    <span>
                        <span>만료시간:</span>
                        <span id="countdown" class="text-primary">{{ countdownTimer }}</span>
                    </span>
                </VCol>
            </div>
        </VCol>

        <VCol cols="12" style="padding: 1em 0;" v-if="button_status !== 2">
            <VBtn block @click="verification()">
                휴대폰 인증하기
            </VBtn>
            <VDivider />
        </VCol>
    </div>
</template>

<style scoped>
.retry-text {
  cursor: pointer;
  font-size: 0.9em;
  text-decoration: underline;
}

.retry-container {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
}

:deep(.v-field__input) {
  padding: 0.1rem !important;
  font-size: 1.25rem !important;
  text-align: center !important;
}

#countdown {
  margin-inline-start: 0.5em;
}
</style>
