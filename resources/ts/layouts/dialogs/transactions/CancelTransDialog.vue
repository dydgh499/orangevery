<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters';
import type { Transaction } from '@/views/types';
import { axios } from '@axios';
import corp from '@corp';
import { maxAmountValidator, requiredValidatorV2 } from '@validators';

const store = <any>(inject('store'))
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const {
    amount_format,
    amount,
    formatAmount,
} = inputFormater()
const visible = ref(false)
const trans = ref<Transaction>({
    id: 0,
    trx_id: '',
    trx_dttm: '',
    cxl_dt: '',
    cxl_tm: '',
    amount: 0,
})
const max_cancel_amount = ref(0)
const vForm = ref()

const show = (item: Transaction) => {
    trans.value = item
    trans.value.cxl_dt = item.trx_dt
    trans.value.cxl_tm = item.trx_tm
    max_cancel_amount.value = item.amount

    amount_format.value = trans.value.amount.toString()
    amount.value = trans.value.amount
    visible.value = true
}

const submit = async() => {
    const is_valid = await vForm.value.validate();
    if (is_valid.valid && await alert.value.show('정말 취소 매출을 생성하시겠습니까?')) {
        const params = {
            id: trans.value.id,
            va_id:  trans.value.va_id,
            trx_id: trans.value.trx_id,
            trx_at: trans.value.trx_dttm,
            cxl_dt: trans.value.cxl_dt,
            cxl_tm: trans.value.cxl_tm,
            amount: trans.value.amount
        }
        try {
            const r = await axios.post('/api/v1/manager/transactions/cancel', params)
            snackbar.value.show('성공하였습니다.', 'success')
            store.setTable()
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
        visible.value = false
    }
}

watchEffect(() => {
    trans.value.amount = amount.value
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="600">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="취소매출생성">
            <VCardText>
                <VForm ref="vForm">
                    <VCol cols="12">
                        <VRow>
                            <VCol cols="12" md="4">
                                <label>취소시간</label>
                            </VCol>
                            <VCol cols="12" md="4">
                                <VTextField
                                        variant="underlined" 
                                        v-model="trans.cxl_dt" 
                                        type="date" 
                                        :rules="[requiredValidatorV2(trans.cxl_dt, '취소일')]"
                                    />
                            </VCol>
                            <VCol cols="12" md="4">
                                <VTextField
                                    variant="underlined" 
                                    v-model="trans.cxl_tm" 
                                    type="time" 
                                    step="1" 
                                    :rules="[requiredValidatorV2(trans.cxl_dt, '취소시간')]" 
                                />
                            </VCol>
                        </VRow>
                        <VRow v-if="corp.pv_options.paid.use_part_cancel">
                            <VCol cols="12" md="4">
                                <label>취소금액</label>
                            </VCol>
                            <VCol cols="12" md="4">
                                <VTextField v-model="amount_format" suffix="원" @input="formatAmount"
                                    variant="underlined" placeholder="취소금액을 입력해주세요"
                                    :rules="[requiredValidatorV2(trans.amount, '취소금액'), maxAmountValidator(trans.amount, '취소금액', max_cancel_amount)]" 
                                />
                            </VCol>
                        </VRow>
                    </VCol>
                </VForm>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="visible = false">
                    취소
                </VBtn>
                <VBtn @click="submit()">
                    생성
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
