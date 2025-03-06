<script lang="ts" setup>
import { pinInputEvent } from '@/@core/utils/pinInputEvent';
import { axios } from '@/plugins/axios';
import corp from '@/plugins/corp';

const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))


const visible = ref(false)

const user_name = ref('')
const user_pw = ref('')

const { digits, ref_opt_comp, handleKeyDown, defaultStyle} = pinInputEvent(6)

let resolveCallback: (token: string) => void;
const show = async (_user_name: string, _user_pw: string) => {
    user_name.value = _user_name
    user_pw.value   = _user_pw
    visible.value = true
    return new Promise<string>((resolve) => {
        resolveCallback = resolve;
    });
}

const onAgree = async (fin_number: string) => {
    const params = {
        brand_id: corp.id,
        verify_code : fin_number,
        user_name: user_name.value,
        user_pw: user_pw.value,
    }
    axios.post(`/api/v1/auth/2fa-qrcode/vertify`, params)
        .then(r => {
            visible.value = false
            resolveCallback(r.data.token)
        })
        .catch(async e => {
            visible.value = false
            resolveCallback('')
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
