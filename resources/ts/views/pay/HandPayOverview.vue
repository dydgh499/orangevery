<script setup lang="ts">
import CardOverview from '@/layouts/components/pay-windows/CardOverview.vue';
import CommonPayOverview from '@/views/pay/CommonPayOverview.vue';
import type { HandPay, Merchandise, PayModule, SalesSlip } from '@/views/types';
import { axios } from '@axios';
import { cloneDeep } from 'lodash';
import { reactive } from 'vue';
import { VForm } from 'vuetify/components';

interface Props {
    pay_module: PayModule,
    merchandise: Merchandise,
}

const props = defineProps<Props>()
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const salesslip = <any>(inject('salesslip'))

const sale_slip = ref(<SalesSlip>({}))
const hand_pay = reactive(<HandPay>({
    yymm: '',
    card_num: '',
    buyer_name: '',
    installment: 0,
}))
const vForm = ref<VForm>()

const pay = async () => {
    if (hand_pay.pmod_id) {
        const is_valid = await vForm.value?.validate()
        if (is_valid?.valid && await alert.value.show(hand_pay.amount.toLocaleString() + '원을 결제하시겠습니까?')) {
            try {
                const r = await axios.post('/api/v1/transactions/hand-pay', cloneDeep(hand_pay))
                sale_slip.value = {
                    ...r.data,
                    ...props.merchandise
                }
                sale_slip.value.module_type = 1 // 수기결제
                salesslip.value.show(sale_slip.value, true)
                snackbar.value.show('성공하였습니다.', 'success')
            }
            catch (e: any) {
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            }
        }
    }
    else
        snackbar.value.show('결제모듈을 선택해주세요.', 'error')
}


</script>
<template>
    <VCard flat rounded>
        <VCardText style="padding: 0;">
            <VDivider />
            <VForm ref="vForm" @submit.prevent="pay">
                <CommonPayOverview :common_info="hand_pay" :pay_module="props.pay_module"
                    :merchandise="props.merchandise" :key="props.pay_module.id" :pay_code="'H'">
                    <template #extra_info>
                        <CardOverview :hand_pay="hand_pay" :is_old_auth="props.pay_module.is_old_auth"/>
                    </template>
                </CommonPayOverview>
            </VForm>
        </VCardText>
    </VCard>
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
