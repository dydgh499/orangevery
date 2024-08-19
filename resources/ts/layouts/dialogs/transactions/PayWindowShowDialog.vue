<script setup lang="ts">
import { payWindowStore } from '@/views/quick-view/payWindowStore';
import { PayModule } from '@/views/types';
import { user_info } from '@axios';
import { hourTimer } from '@core/utils/timer';
import corp from '@corp';

const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const { move, copy, extend, getPayWindowUrl, renewPayWindow, multiplePayMove } = payWindowStore()
const {remaining_time, expire_time, getRemainTimeColor, updateRemainingTime} = hourTimer()

const visible = ref(false)
const url = ref()
const payment_module = ref()
const intervalId = ref()

const show = async (_payment_module: PayModule) => {
    payment_module.value = _payment_module
    const res = await renewPayWindow(payment_module.value)
    payment_module.value.pay_window = res.data

    expire_time.value = payment_module.value.pay_window.holding_able_at
    intervalId.value = setInterval(updateRemainingTime, 1001);

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
    clearInterval(intervalId.value)
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
        <VCard title="결제창 정보">
            <VCardText>
                <VCol cols="12">
                    <template v-if="payment_module.module_type === 1">
                        <h4>수기결제창은 생성 후 1시간동안 유효합니다.</h4>
                    </template>
                    <VRow class="pt-5">
                        <VCol md="12" cols="12">
                            <VRow no-gutters>
                                <VCol cols="6" :md="3">
                                    <b>결제창 유효시간</b>
                                </VCol>
                                <VCol cols="6" :md="9">
                                    <b :class="getRemainTimeColor">{{ remaining_time }}</b>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol md="12" cols="12">
                            <VRow no-gutters>
                                <VCol cols="6" :md="3">
                                    <b>결제창 주소</b>
                                </VCol>
                                <VCol cols="6" :md="9">
                                    <label>{{ url }}</label>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCol>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn @click="move(url)" size="small">이동</VBtn>
                <VBtn @click="copy(url)" size="small" color="warning">복사</VBtn>
                <VBtn @click="extendPayWindow()" size="small" color="error">유효기간 연장</VBtn>
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
