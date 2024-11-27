<script lang="ts" setup>
import { inputFormater } from '@/@core/utils/formatters';
import { axios } from '@/plugins/axios';
import { BillKey } from '@/views/types';
import { requiredValidatorV2 } from '@validators';
import { VForm } from 'vuetify/components';

interface BillPayInfo {
    ord_num: string,
    buyer_name: string,
    buyer_phone: string,
    item_name: string,
    amount: number,
}

const {
    phone_num_format,
    amount_format,

    phone_num,
    amount,

    formatPhoneNum,
    formatAmount
} = inputFormater()

const vForm = ref<VForm>()
const visible = ref(false)

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const bill_key = ref(<BillKey>({}))
const bill_pay_info = ref(<BillPayInfo>{})

const show = (_bill_key: BillKey)  => {
    visible.value = true
    bill_key.value = _bill_key
    bill_pay_info.value = (<BillPayInfo>{
        ord_num: '',
        buyer_name: '',
        buyer_phone: '',
        item_name: '',
        amount: 0,
    })
}

const submit = async() => {
    const is_valid = await vForm.value.validate()
    if (is_valid.valid) {
        if(await alert.value.show('정말 결제하시겠습니까?')) {
            axios.post(`/api/v1/manager/merchandises/pay-modules/bill-keys/${bill_key.value.id}/hand`, bill_pay_info.value).then(r => {
                visible.value = false
                snackbar.value.show('성공하였습니다.', 'success')
            })
            .catch(async e => {
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            })
        }
    }
}

watchEffect(() => {
    bill_pay_info.value.ord_num = bill_key.value.id + "BP" + Date.now().toString().substr(0, 10)
})

watchEffect(() => {
    bill_pay_info.value.buyer_phone = phone_num.value
    bill_pay_info.value.amount = amount.value
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="800">
        <DialogCloseBtn @click="visible = false" />
        <VCard title="빌키 생성">
            <VCardText>
                <VForm ref="vForm">
                    <VCol style="padding: 0 12px;">
                        <VRow>
                            <VCol md="12" cols="12" style="padding: 0 12px;">
                                <VRow no-gutters style="min-height: 3.5em;">
                                    <VCol cols="4" :md="2">
                                        <label>상품명</label>
                                    </VCol>
                                    <VCol cols="8" :md="10">
                                        <VTextField
                                            v-model="bill_pay_info.item_name"
                                            prepend-icon="tabler:shopping-bag"
                                            maxlength="100" 
                                            counter
                                            variant="underlined"
                                            :rules="[requiredValidatorV2(bill_pay_info.item_name, '상품명')]" 
                                            placeholder="상품명을 입력해주세요"/>
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VRow>
                            <VCol md="6" cols="12" style="padding: 0 12px;">
                                <VRow no-gutters style="min-height: 3.5em;">
                                    <VCol cols="4" :md="4">
                                        <label>구매자명</label>
                                    </VCol>
                                    <VCol cols="8" :md="8">
                                        <VTextField 
                                            v-model="bill_pay_info.buyer_name"
                                            variant="underlined"
                                            placeholder="구매자명을 입력해주세요" :rules="[requiredValidatorV2(bill_pay_info.buyer_name, '구매자명')]" 
                                            prepend-icon="tabler-user" />
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="6" cols="12" style="padding: 0 12px;">
                                <VRow no-gutters style="min-height: 3.5em;">
                                    <VCol cols="4" :md="4">
                                        <label>연락처</label>
                                    </VCol>
                                    <VCol cols="8" :md="8">
                                        <VTextField 
                                            v-model="phone_num_format" 
                                            @input="formatPhoneNum"
                                            variant="underlined"
                                            prepend-icon="tabler-device-mobile" placeholder="구매자 연락처를 입력해주세요"
                                            :rules="[requiredValidatorV2(buyer_phone, '구매자 연락처')]"
                                            />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VRow cols="12">
                            <VCol md="6" cols="12" style="padding: 0 12px;">
                                <VRow no-gutters style="min-height: 3.5em;">
                                    <VCol cols="4" :md="4">
                                        <label>상품금액</label>
                                    </VCol>
                                    <VCol cols="8" :md="8">
                                        <VTextField 
                                            v-model="amount_format" suffix="₩" 
                                            @input="formatAmount"
                                            variant="underlined"
                                            placeholder="상품금액을 입력해주세요" prepend-icon="ic:outline-price-change"
                                            :rules="[requiredValidatorV2(amount, '상품금액')]" />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </VCol>
                </VForm>
            </VCardText>
            <VDivider />
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn  @click="submit()">
                    결제하기
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
.table-th,
.table-td {
  border-inline-end: thin solid rgba(var(--v-border-color), var(--v-border-opacity)) !important;
}

:deep(.v-row) {
  align-items: center;
}

.card-pay-th {
  padding: 0.5em;
}

.card-pay-td {
  padding: 0.5em;
}

:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
