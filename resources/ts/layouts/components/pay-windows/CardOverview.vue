<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters';
import CreditCard from '@/layouts/components/CreditCard.vue';
import { HandPay } from '@/views/types';
import { lengthValidatorV2, requiredValidatorV2 } from '@validators';
import { getAllPayModules } from '@/views/services/pay-modules/useStore'
import type { PayModule } from '@/views/types';

interface Props {
    hand_pay: HandPay,
    is_old_auth: number,
}
const props = defineProps<Props>()

const pay_modules = ref<PayModule[]>([])
const {
    card_num_format,
    yymm_format,
    card_num,
    yymm,
    formatCardNum,
    formatYYmm,
} = inputFormater()

card_num.value = props.hand_pay.card_num
yymm.value = props.hand_pay.yymm

const filterPayModule = computed(() => {
    return pay_modules.value.filter(item => {
        return item.module_type === 4
    })
})

watchEffect(() => {
    props.hand_pay.card_num = card_num.value
    props.hand_pay.yymm = yymm.value
})

onMounted(async () => {
    pay_modules.value = await getAllPayModules()
})
</script>
<template>
  <VRow>
    <VCol cols="12" md="6" order-md="1" order="2">
      <VCardTitle style="margin: 0.5em 0;"><b>결제정보</b></VCardTitle>

      <VRow no-gutters style="min-height: 3.5em;">
        <VCol md="4" cols="4">
          <label>결제모듈</label>
        </VCol>
        <VCol md="8" cols="8">
          <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.hand_pay.pmod_id"
            variant="underlined"
            :items="filterPayModule" prepend-inner-icon="ic-outline-send-to-mobile"
            label="결제모듈 별칭" item-title="note"
            :rules="[requiredValidatorV2(hand_pay.pmod_id, '결제모듈')]"
            item-value="id" />
        </VCol>
      </VRow>

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
            :rules="[requiredValidatorV2(hand_pay.card_num, '카드번호')]"
            maxlength="22"
            autocomplete="cc-number"
          />
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
            :rules="[requiredValidatorV2(hand_pay.yymm, '유효기간'), lengthValidatorV2(hand_pay.yymm, 4)]"
            maxlength="5"
            style="min-inline-size: 11em;"
          >
            <VTooltip activator="parent" location="top">
              카드의 유효기간 4자리를 입력해주세요.<br />(MM/YY:0324)
            </VTooltip>
          </VTextField>
        </VCol>
      </VRow>

      <VRow v-if="props.is_old_auth" no-gutters style="min-height: 3.5em;">
        <VCol md="4" cols="4">
          <label>본인확인</label>
        </VCol>
        <VCol md="8" cols="8">
          <VTextField
            v-model="hand_pay.auth_num"
            type="number"
            maxlength="10"
            variant="underlined"
            prepend-icon="teenyicons:id-outline"
            placeholder="생년월일6자리(사업자번호)"
            persistent-placeholder
            counter
          >
            <VTooltip activator="parent" location="top">
              개인카드일 경우 카드소유주의 생년월일6자리 입력,<br />법인카드의 경우 사업자등록번호를 입력해주세요.
            </VTooltip>
          </VTextField>
        </VCol>
      </VRow>

      <VRow v-if="props.is_old_auth" no-gutters style="min-height: 3.5em;">
        <VCol md="4" cols="4">
          <label>비밀번호</label>
        </VCol>
        <VCol md="8" cols="8">
          <div style="display: inline-flex; align-items: center;">
            <VTextField
              v-model="hand_pay.card_pw"
              type="password"
              prepend-icon="tabler:paywall"
              variant="underlined"
              persistent-placeholder
              maxlength="2"
              style="max-width: 4em;"
            >
              <VTooltip activator="parent" location="top">
                카드비밀번호 앞 4자리 중 2자리를 입력해주세요.
              </VTooltip>
            </VTextField>
            <b style="margin-left: 0.5em;">**</b>
          </div>
        </VCol>
      </VRow>
    </VCol>

    <VCol cols="12" md="6" order-md="2" order="1" class="d-flex align-center justify-center">
      <CreditCard
        :card_num="hand_pay.card_num"
        :auth_num="hand_pay.auth_num"
        :yymm="hand_pay.yymm"
        :is_old_auth="props.is_old_auth"
      />
    </VCol>
  </VRow>
</template>