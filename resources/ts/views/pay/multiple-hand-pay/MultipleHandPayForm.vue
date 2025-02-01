<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters'
import { installments } from '@/views/merchandises/pay-modules/useStore'
import type { MultipleHandPay, Options, PayModule } from '@/views/types'
import { lengthValidatorV2, requiredValidatorV2 } from '@validators'
import { computed } from 'vue'
import { useDisplay } from 'vuetify'
import { VForm } from 'vuetify/components'

interface Props {
    hand_pay: MultipleHandPay,
    pay_module: PayModule,
    index: number,
}
const props = defineProps<Props>()
const multiVForm = ref<VForm>()

const {
    card_num_format,
    yymm_format,
    amount_format,
    card_num,
    yymm,
    amount,
    formatCardNum,
    formatYYmm,
    formatAmount,
} = inputFormater()

const filterInstallment = computed(() => {
    if(props.hand_pay.amount >= 50000)
        return installments.filter((obj: Options) => { return obj.id <= (props.pay_module.installment || 0) })
    else {
        props.hand_pay.installment = 0
        return [{ id: 0, title: "일시불" }]        
    }
})

watchEffect(async () => {
    let valid = props.hand_pay.amount > 0 && 
    props.hand_pay.card_num.length > 14 &&
    props.hand_pay.yymm.length == 4

    if(props.pay_module.is_old_auth) {
        valid = valid && props.hand_pay.card_pw?.length == 2
        valid = valid && props.hand_pay.auth_num?.length as number > 2
    }

    props.hand_pay.status_icon = valid ? 'line-md:check-all' : 'line-md:emoji-frown-twotone'
    props.hand_pay.status_color = valid ? 'success' : 'error'
})

watchEffect(() => {
    props.hand_pay.card_num = card_num.value
    props.hand_pay.yymm = yymm.value
    props.hand_pay.amount = amount.value
})


watchEffect(() => {
    const { mobile } = useDisplay()
    props.hand_pay.user_agent = mobile.value ? "WM" : "WP"
    props.hand_pay.pmod_id = props.pay_module.id
    props.hand_pay.ord_num = props.pay_module.id + "H" + Date.now().toString().substr(0, 10)
})

</script>
<template>
    <AppCardActions :actionCollapsed="true">
        <template #title>
            <div>
                <span>결제정보 {{ index+1 }}</span>
                <VIcon size="24" :icon="props.hand_pay.status_icon"
                    :color="props.hand_pay.status_color" style="float: inline-end;"/>
            </div>
        </template>
        <VDivider />
        <VForm ref="multiVForm">
            <VCol cols="12" style="padding-bottom: 0;">
                <VRow cols="12">
                    <VCol md="6" cols="12" style="padding: 0 12px;">
                        <VRow no-gutters style="min-height: 4em;">
                            <VCol cols="4" :md="4">
                                <label>상품금액</label>
                            </VCol>
                            <VCol cols="8" :md="8">
                                <VTextField 
                                    v-model="amount_format" suffix="₩" 
                                    @input="formatAmount"
                                    variant="underlined"
                                    placeholder="상품금액을 입력해주세요" prepend-icon="ic:outline-price-change"
                                    :rules="[requiredValidatorV2(props.hand_pay.amount, '상품금액')]" 
                                />
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol md="6" cols="12" style="padding: 0 12px;">
                        <VRow no-gutters style="min-height: 4em;">
                            <VCol cols="4" :md="4">
                                <label>할부기간</label>
                            </VCol>
                            <VCol cols="8" :md="8">
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.hand_pay.installment" name="installment"
                                    variant="underlined"
                                    :items="filterInstallment" prepend-icon="fluent-credit-card-clock-20-regular"
                                    label="할부기간 선택" item-title="title" item-value="id" single-line :rules="[requiredValidatorV2(props.hand_pay.installment, '할부기간')]" />
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow no-gutters style="min-height: 4em;">
                    <VCol md="4" cols="4">
                        <label>카드번호</label>
                    </VCol>
                    <VCol md="8" cols="8">
                        <VTextField  
                            variant="underlined"
                            v-model="card_num_format"
                            @input="formatCardNum"
                            prepend-icon="tabler:credit-card"
                            placeholder="카드번호를 입력해주세요" 
                            :rules="[requiredValidatorV2(props.hand_pay.card_num, '카드번호')]"
                            maxlength="22" autocomplete="cc-number" />
                    </VCol>
                </VRow>
                <VRow no-gutters style="min-height: 4em;">
                    <VCol md="4" cols="4">
                        <label>유효기간</label>
                    </VCol>
                    <VCol md="8" cols="8">
                        <VTextField 
                            v-model="yymm_format" 
                            @input="formatYYmm"
                            placeholder="MM/YY" 
                            variant="underlined"
                            prepend-icon="ri:pass-expired-line"
                            :rules="[requiredValidatorV2(props.hand_pay.yymm, '유효기간'), lengthValidatorV2(hand_pay.yymm, 4)]"
                            maxlength="5" style="min-inline-size: 11em;">
                            <VTooltip activator="parent" location="top">
                                카드의 유효기간 4자리를 입력해주세요.<br>
                                (MM/YY:0324)
                            </VTooltip>
                        </VTextField>
                    </VCol>
                </VRow>
                
                <VRow no-gutters style="min-height: 4em;" v-if="props.pay_module.is_old_auth">
                    <VCol md="4" cols="4">
                        <label>본인확인</label>
                    </VCol>
                    <VCol md="8" cols="8">
                        <VTextField v-model="props.hand_pay.auth_num" type="number" maxlength="10" variant="underlined"
                            prepend-icon="teenyicons:id-outline"
                            placeholder="생년월일6자리(사업자번호)" persistent-placeholder counter>
                            <VTooltip activator="parent" location="top">
                                개인카드일 경우 카드소유주의 생년월일6자리 입력,<br>법인카드의 경우 사업자등록번호를 입력해주세요.
                            </VTooltip>
                        </VTextField>
                    </VCol>
                </VRow>
                <VRow no-gutters style="min-height: 4em;" v-if="props.pay_module.is_old_auth">
                    <VCol md="4" cols="4">
                        <label>비밀번호</label>
                    </VCol>
                    <VCol md="8" cols="8">
                        <div style="display: inline-flex; align-items: center;">
                            <VTextField v-model="props.hand_pay.card_pw" 
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
        </VForm>
    </AppCardActions>
</template>
<style scoped>
:deep(.v-card-item) {
  padding: 18px !important;
}

:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
