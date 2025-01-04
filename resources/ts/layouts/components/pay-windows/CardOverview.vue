<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters';
import CreditCard from '@/layouts/components/CreditCard.vue';
import { HandPayInfo } from '@/views/types';
import { lengthValidatorV2, requiredValidatorV2 } from '@validators';

interface Props {
    hand_pay_info: HandPayInfo,
    is_old_auth: number,
}
const props = defineProps<Props>()

const {
    card_num_format,
    yymm_format,
    card_num,
    yymm,
    formatCardNum,
    formatYYmm,
} = inputFormater()

card_num.value = props.hand_pay_info.card_num
yymm.value = props.hand_pay_info.yymm

watchEffect(() => {
    props.hand_pay_info.card_num = card_num.value
    props.hand_pay_info.yymm = yymm.value
})
</script>
<template>
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
                            <VTextField  
                                variant="underlined"
                                v-model="card_num_format"
                                @input="formatCardNum"
                                prepend-icon="tabler:credit-card"
                                placeholder="카드번호를 입력해주세요" 
                                :rules="[requiredValidatorV2(hand_pay_info.card_num, '카드번호')]"
                                maxlength="22" autocomplete="cc-number" />
                        </VCol>
                    </VRow>
                    <VRow no-gutters style="min-height: 3.5em;">
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
                                :rules="[requiredValidatorV2(hand_pay_info.yymm, '유효기간'), lengthValidatorV2(hand_pay_info.yymm, 4)]"
                                maxlength="5" style="min-inline-size: 11em;">
                                <VTooltip activator="parent" location="top">
                                    카드의 유효기간 4자리를 입력해주세요.<br>
                                    (MM/YY:0324)
                                </VTooltip>
                            </VTextField>
                        </VCol>
                    </VRow>
                    <VRow no-gutters style="min-height: 3.5em;" v-if="props.is_old_auth">
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
                    <VRow no-gutters style="min-height: 3.5em;" v-if="props.is_old_auth">
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
                        :is_old_auth="props.is_old_auth"
                    />
                </VCol>                                    
            </VRow>
        </VCol>
    </VRow>
</template>
