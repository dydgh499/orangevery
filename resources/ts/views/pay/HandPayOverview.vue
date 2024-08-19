<script setup lang="ts">
import CreditCard from '@/layouts/components/CreditCard.vue';
import CommonPayOverview from '@/views/pay/CommonPayOverview.vue';
import type { HandPay, Merchandise, PayModule, SalesSlip } from '@/views/types';
import { axios } from '@axios';
import { lengthValidatorV2, requiredValidatorV2 } from '@validators';
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
const hand_pay_info = reactive(<HandPay>({
    yymm: '',
    card_num: '',
    buyer_name: '',
    installment: 0,
}))
const vForm = ref<VForm>()

const pay = async () => {
    if (hand_pay_info.pmod_id) {
        const is_valid = await vForm.value?.validate()
        if (is_valid?.valid && await alert.value.show(hand_pay_info.amount.toLocaleString() + '원을 결제하시겠습니까?')) {
            try {
                const params = cloneDeep(hand_pay_info)
                const r = await axios.post('/api/v1/transactions/hand-pay', params)
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
            <slot name="explain">
            </slot>
            <VDivider />
            <VForm ref="vForm" @submit.prevent="pay">
                <CommonPayOverview :common_info="hand_pay_info" :pay_module="props.pay_module"
                    :merchandise="props.merchandise" :key="props.pay_module.id" :pay_code="'H'">
                    <template #extra_info>
                        <VRow>
                            <VDivider />
                            <VCardTitle style="margin: 0.5em 0;"><b>결제정보</b></VCardTitle>
                            <VCol cols="12" style="padding: 0 12px;">
                                <VRow :style="$vuetify.display.smAndDown ? 'display: flex;flex-direction: column-reverse;' : ''">
                                    <VCol cols="12" md="6" style="display: flex;flex-direction: column;justify-content: space-around;">
                                        <VRow no-gutters style="min-height: 3.5em;">
                                            <VCol md="4" cols="4">
                                                <label>카드번호</label>
                                            </VCol>
                                            <VCol md="8" cols="8">
                                                <VTextField  variant="underlined"
                                                    v-model="hand_pay_info.card_num"
                                                    prepend-icon="tabler:credit-card"
                                                    placeholder="카드번호를 입력해주세요" 
                                                    :rules="[requiredValidatorV2(hand_pay_info.card_num, '카드번호')]"
                                                    maxlength="18" autocomplete="cc-number" />
                                            </VCol>
                                        </VRow>
                                        <VRow no-gutters style="min-height: 3.5em;">
                                            <VCol md="4" cols="4">
                                                <label>유효기간</label>
                                            </VCol>
                                            <VCol md="8" cols="8">
                                                <VTextField v-model="hand_pay_info.yymm" placeholder="MMYY" variant="underlined"
                                                    prepend-icon="ri:pass-expired-line"
                                                    :rules="[requiredValidatorV2(hand_pay_info.yymm, '유효기간'), lengthValidatorV2(hand_pay_info.yymm, 4)]"
                                                    maxlength="4" style="min-inline-size: 11em;">
                                                    <VTooltip activator="parent" location="top">
                                                        카드의 유효기간 4자리를 입력해주세요.<br>
                                                        (MM/YY:0324)
                                                    </VTooltip>
                                                </VTextField>
                                            </VCol>
                                        </VRow>
                                        <VRow no-gutters style="min-height: 3.5em;" v-if="props.pay_module.is_old_auth">
                                            <VCol md="4" cols="4">
                                                <label>본인확인</label>
                                            </VCol>
                                            <VCol md="8" cols="8">
                                                <VTextField v-model="hand_pay_info.auth_num" type="number" maxlength="10" variant="underlined"
                                                    prepend-icon="teenyicons:id-outline"
                                                    placeholder="생년월일6자리(사업자번호)" persistent-placeholder counter>
                                                    <VTooltip activator="parent" location="top">
                                                        개인카드일 경우 카드소유주의 생년월일6자리 입력,<br>법인카드의 경우 사업자등록번호를 입력해주세요.
                                                    </VTooltip>
                                                </VTextField>
                                            </VCol>
                                        </VRow>
                                        <VRow no-gutters style="min-height: 3.5em;" v-if="props.pay_module.is_old_auth">
                                            <VCol md="4" cols="4">
                                                <label>비밀번호</label>
                                            </VCol>
                                            <VCol md="8" cols="8">
                                                <div style="display: inline-flex; align-items: center;">
                                                    <VTextField v-model="hand_pay_info.card_pw" 
                                                        type="password" 
                                                        prepend-icon="tabler:paywall"
                                                        variant="underlined"
                                                        persistent-placeholder
                                                        maxlength="2"
                                                        style="max-width: 4em;">
                                                        <VTooltip activator="parent" location="top">
                                                            카드비밀번호 앞 4자리 중 2자리를 입력해주세요.
                                                        </VTooltip>
                                                    </VTextField>
                                                    <b style="margin-left: 0.5em;">**</b>
                                                </div>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <CreditCard
                                            :card_num="hand_pay_info.card_num"
                                            :auth_num="hand_pay_info.auth_num"
                                            :yymm="hand_pay_info.yymm"
                                            :is_old_auth="props.pay_module.is_old_auth"
                                        />
                                    </VCol>                                    
                                </VRow>
                            </VCol>
                        </VRow>
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
