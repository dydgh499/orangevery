<script setup lang="ts">
import BillPayOverview from '@/layouts/components/pay-windows/BillPayOverview.vue';
import CardOverview from '@/layouts/components/pay-windows/CardOverview.vue';
import BillKeySelectDialog from '@/layouts/dialogs/pay-modules/BillKeySelectDialog.vue';
import corp from '@/plugins/corp';
import CommonPayOverview from '@/views/pay/CommonPayOverview.vue';
import type { BillKey, HandPay, Merchandise, PayModule, SalesSlip } from '@/views/types';
import { axios } from '@axios';
import { cloneDeep } from 'lodash';
import { reactive } from 'vue';
import { VForm } from 'vuetify/components';

interface Props {
    pay_module: PayModule,
    merchandise: Merchandise,
}
const route = useRoute()

const props = defineProps<Props>()
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const salesslip = <any>(inject('salesslip'))
const auth_token = <any>(inject('auth_token'))

const sale_slip = ref(<SalesSlip>({}))
const hand_pay_info = reactive(<HandPay>({
    yymm: '',
    card_num: '',
    buyer_name: '',
    installment: 0,
}))
const billKeySelectDialog = ref()
const bill_key = ref(<BillKey>({id: 0}))
const vForm = ref<VForm>()

provide('bill_key', bill_key)

const pay = async () => {
    if (hand_pay_info.pmod_id) {
        const is_valid = await vForm.value?.validate()
        if (is_valid?.valid && await alert.value.show(hand_pay_info.amount.toLocaleString() + '원을 결제하시겠습니까?')) {
            try {
                const url = bill_key.value.id ? `/api/v1/pay/${route.params.window}/bill-keys/${bill_key.value.id}/pay` : '/api/v1/transactions/hand-pay'
                const params = bill_key.value.id ? Object.assign(cloneDeep(hand_pay_info), {token: auth_token.value}) : cloneDeep(hand_pay_info)
                const r = await axios.post(url, params)
                sale_slip.value = {
                    ...r.data,
                    ...props.merchandise
                }
                sale_slip.value.module_type = 1 // 수기결제
                salesslip.value.show(sale_slip.value, true)
                snackbar.value.show('성공하였습니다.', 'success')
            }
            catch (e: any) {
                if(e.response.data.code === 1999)
                    billKeyBack()
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            }
        }
    }
    else
        snackbar.value.show('결제모듈을 선택해주세요.', 'error')
}

const billKeySelect = async () => {
    const _bill_key = await billKeySelectDialog.value.show(route.params.window, hand_pay_info, auth_token)
    if(_bill_key !== null) {
        bill_key.value = _bill_key
        hand_pay_info.buyer_name = bill_key.value.buyer_name
        hand_pay_info.buyer_phone = bill_key.value.buyer_phone
    }
}

const billKeyBack = () => {
    bill_key.value = {id: 0}
}

</script>
<template>
    <VCard flat rounded>
        <VCardText style="padding: 0;">
            <VDivider />
            <VForm ref="vForm" @submit.prevent="pay">
                <BillPayOverview v-if="bill_key.id" :common_info="hand_pay_info" :pay_module="props.pay_module" :key="bill_key.id" :bill_key="bill_key">
                    <template #pay_group>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <VBtn type="submit" style="width: 33%;">
                                결제하기
                            </VBtn>
                            <VBtn
                                @click="billKeySelect()" color="info"
                                style="width: 33%;">
                                빌키조회
                            </VBtn>
                            <VBtn
                                @click="billKeyBack()" color="error"
                                style="width: 33%;">
                                수기결제
                            </VBtn>
                        </div>
                    </template>
                </BillPayOverview>
                <CommonPayOverview v-else :common_info="hand_pay_info" :pay_module="props.pay_module"
                    :merchandise="props.merchandise" :key="props.pay_module.id" :pay_code="'H'">
                    <template #extra_info>
                        <CardOverview :hand_pay_info="hand_pay_info" :is_old_auth="props.pay_module.is_old_auth"/>
                    </template>
                    <template #bill_key>
                        <VBtn v-if="corp.pv_options.paid.use_bill_key && props.pay_module.is_able_bill_key"
                            @click="billKeySelect()" color="info" size="small"
                            style="margin-left: 1em;">
                            빌키조회
                        </VBtn>
                    </template>
                </CommonPayOverview>
            </VForm>
        </VCardText>
    </VCard>
    <BillKeySelectDialog ref="billKeySelectDialog" />
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
