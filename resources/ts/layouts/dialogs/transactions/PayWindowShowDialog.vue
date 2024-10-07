<script setup lang="ts">
import { payWindowStore } from '@/views/quick-view/payWindowStore';
import { PayModule } from '@/views/types';
import { user_info } from '@axios';
import { timerV2 } from '@core/utils/timer';
import corp from '@corp';

const snackbar = <any>(inject('snackbar'))

const { move, copy, extend, getPayWindowUrl, renewPayWindow, multiplePayMove, isVisiableRemainTime } = payWindowStore()
const { remaining_time, expire_time, getRemainTimeColor, clearTimer} = timerV2("00:00:00")

const visible = ref(false)
const url = ref()
const payment_module = ref()

const show = async (_payment_module: PayModule) => {
    payment_module.value = _payment_module
    const res = await renewPayWindow(payment_module.value)
    payment_module.value.pay_window = res.data
    expire_time.value = payment_module.value.pay_window.holding_able_at
    
    url.value = getPayWindowUrl(payment_module.value, '')
    visible.value = true
}

const extendPayWindow = async () => {
    const res = await extend(payment_module.value.pay_window.window_code)
    payment_module.value.pay_window.holding_able_at = res.data.holding_able_at
    expire_time.value = res.data.holding_able_at
    snackbar.value.show(`결제링크의 유효기간이 ${res.data.holding_able_at}까지 연장되었습니다.`, 'success')
}

const close = () => {
    clearTimer()
    expire_time.value = '00:00:00'
    visible.value = false 
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="600">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="close()" />
        <!-- Dialog Content -->
        <VCard>
            <VCol cols="12">
                <VRow no-gutters>
                    <VCol cols="12" :md="6">
                        <VCardTitle>결제창 정보({{ payment_module.note }})</VCardTitle>
                    </VCol>
                    <VCol cols="12" :md="6">
                        <div v-if="isVisiableRemainTime(payment_module)" 
                            style="display: inline-flex; align-items: center;margin-right: 1em; float: inline-end;">
                            <h5 style="margin-right: 0.5em;">결제창 유효시간</h5>
                            <b :class="getRemainTimeColor">{{ remaining_time }}</b>
                        </div>
                    </VCol>
                </VRow>
            </VCol>
            <VCardText style="padding-top: 0;">
                <VCol cols="12">
                    <VRow no-gutters style="padding-top: 12px;">
                        <VCol cols="5" :md="3">
                            <span>결제창 주소</span>
                        </VCol>
                        <VCol cols="7" :md="9">
                            <b>{{ url }}</b>
                        </VCol>
                    </VRow>
                    <VRow no-gutters style="padding-top: 12px;" v-if="payment_module.pay_window_secure_level === 2 || payment_module.pay_window_secure_level === 4">
                        <VCol cols="5" :md="3">
                            <span>PIN 번호</span>
                        </VCol>
                        <VCol cols="7" :md="9">
                            <b>{{ payment_module.pay_window.pin_code }}</b>
                        </VCol>
                    </VRow>
                </VCol>
            </VCardText>

            
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn @click="move(url)" size="small">이동</VBtn>
                <VBtn @click="copy(url)" size="small" color="warning">주소복사</VBtn>
                <VBtn @click="extendPayWindow()" size="small" color="error" 
                    v-if="isVisiableRemainTime(payment_module)"
                >유효기간 연장</VBtn>
                <VBtn @click="multiplePayMove(payment_module)" size="small" color="error"
                    v-if="corp.pv_options.paid.use_multiple_hand_pay && payment_module.module_type === 1 && user_info.use_multiple_hand_pay"
                >다중결제</VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
