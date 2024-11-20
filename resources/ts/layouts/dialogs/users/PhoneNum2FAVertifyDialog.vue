<script lang="ts" setup>
import { pinInputEvent } from '@/@core/utils/pinInputEvent';
import { axios, user_info } from '@axios';
import { timerV1 } from '@core/utils/timer';

const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const visible = ref(false)

const phone_num = ref('')

const { digits, ref_opt_comp, handleKeyDown, defaultStyle} = pinInputEvent(6)
const { countdownTimer, restartTimer } = timerV1(180)

let resolveCallback: (token: string) => void;

const show = async (_phone_num: string) => {
    phone_num.value = _phone_num
    visible.value = true
    digits.value.fill('')

    restartTimer()
    return new Promise<string>((resolve) => {
        resolveCallback = resolve;
    });
}

const requestCodeIssuance = async () => {
    try {
        const res = await axios.post('/api/v1/bonaejas/mobile-code-head-office-issuence', {
            user_name: user_info.value.user_name
        })
        snackbar.value.show(res.data.message, 'success')        
        restartTimer()
    }
    catch(e:any) {
        snackbar.value.show(e.response.data.message, 'error') 
    }
}

const onAgree = async (fin_number: string) => {
    const params = {
        phone_num: phone_num.value,
        verification_number : fin_number,
    }
    axios.post(`/api/v1/bonaejas/mobile-code-auth`, params)
        .then(r => {
            visible.value = false
            resolveCallback(r.data.token)
        })
        .catch(async e => {
            snackbar.value.show('핀번호가 정확하지 않습니다.', 'error') 
            const r = errorHandler(e)
        })
};

const onCancel = () => {
    visible.value = false
    resolveCallback(''); // 취소 버튼 누름
}

const handleKeyDownEvent = (index: number) => {
    handleKeyDown(index)
    if (digits.value.join('').length === 6)
        onAgree(digits.value.join(''))
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="450">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="onCancel()" />
        <!-- Dialog Content -->
        <VCard class="pa-5 pa-sm-8">
            <VCardTitle class="text-h6 font-weight-bold mb-2">2FA 인증</VCardTitle>
            <VCol cols="12" style="padding: 0;">
                <div>
                    <VCol>
                        <h6 class="text-base font-weight-bold mb-3">
                            6자리 인증번호를 입력해주세요.
                        </h6>
                        <div ref="ref_opt_comp" class="d-flex align-center gap-4">
                            <VTextField v-for="i in 6" :key="i" :model-value="digits[i - 1]" type="number"
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
        </VCard>
    </VDialog>
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
</style>
