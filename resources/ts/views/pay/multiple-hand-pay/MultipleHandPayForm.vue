<script setup lang="ts">
import { installments } from '@/views/merchandises/pay-modules/useStore'
import { requiredValidator, lengthValidator, lengthValidatorV2 } from '@validators'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { VForm } from 'vuetify/components'
import type { PayModule, Options, MultipleHandPay } from '@/views/types'
import { computed } from 'vue'

interface Props {
    hand_pay_info: MultipleHandPay,
    pay_module: PayModule,
}
const props = defineProps<Props>()
const multiVForm = ref<VForm>()
const is_show = ref(false)

const filterInstallment = computed(() => {
    return installments.filter((obj: Options) => { return obj.id <= (props.pay_module.installment || 0) })
})

watchEffect(async () => {
    let valid = props.hand_pay_info.amount > 0 && 
    props.hand_pay_info.card_num.length > 14 &&
    props.hand_pay_info.yymm.length == 4

    if(props.hand_pay_info.is_old_auth) {
        valid = valid && props.hand_pay_info.card_pw?.length == 2
        valid = valid && props.hand_pay_info.auth_num?.length as number > 2
    }

    props.hand_pay_info.status_icon = valid ? 'line-md:check-all' : 'line-md:emoji-frown-twotone'
    props.hand_pay_info.status_color = valid ? 'success' : 'error'
})
</script>
<template>
    <AppCardActions actionCollapsed>
        <template #title>
            <div>
                <span>{{ props.pay_module.note }}</span>
                <VIcon size="24" :icon="props.hand_pay_info.status_icon"
                    :color="props.hand_pay_info.status_color" style="float: inline-end;"/>
            </div>
        </template>
        <VDivider />
        <VForm ref="multiVForm">
            <CreateHalfVCol :mdl="4" :mdr="8">
                <template #name>상품금액</template>
                <template #input>
                    <VTextField v-model="props.hand_pay_info.amount" suffix="₩" placeholder="거래금액을 입력해주세요"
                        prepend-inner-icon="ic:outline-price-change" :rules="[requiredValidator]" />
                </template>
            </CreateHalfVCol>
            <CreateHalfVCol :mdl="4" :mdr="8">
                <template #name>카드번호</template>
                <template #input>
                    <VTextField v-model="props.hand_pay_info.card_num" type="text" persistent-placeholder
                        prepend-inner-icon="emojione:credit-card" placeholder="카드번호를 입력해주세요"
                        :rules="[lengthValidator(props.hand_pay_info.card_num, 15)]" maxlength="18" autocomplete="cc-number" />
                </template>
            </CreateHalfVCol>
            <CreateHalfVCol :mdl="4" :mdr="8">
                <template #name>유효기간</template>
                <template #input>
                    <VTextField v-model="props.hand_pay_info.yymm" type="text" persistent-placeholder
                        prepend-inner-icon="ic-baseline-calendar-today" placeholder="(MM/YY:0324)"
                        :rules="[lengthValidatorV2(props.hand_pay_info.yymm, 4)]" maxlength="4" />
                </template>
            </CreateHalfVCol>
            <CreateHalfVCol :mdl="4" :mdr="8">
                <template #name>할부기간</template>
                <template #input>
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.hand_pay_info.installment"
                        :items="filterInstallment"
                        prepend-inneer-icon="fluent-credit-card-clock-20-regular" item-title="title" item-value="id"
                        single-line :rules="[requiredValidator]"/>
                </template>
            </CreateHalfVCol>
            <CreateHalfVCol :mdl="6" :mdr="6" style="padding: 6px 0;" v-if="props.hand_pay_info.is_old_auth">
                <template #name>생년월일(사업자등록번호)</template>
                <template #input>
                    <VTextField v-model="props.hand_pay_info.auth_num" type="number" maxlength="10"
                        prepend-inner-icon="carbon:two-factor-authentication" />
                </template>
            </CreateHalfVCol>
            <CreateHalfVCol :mdl="6" :mdr="6" style="padding: 6px 0;" v-if="props.hand_pay_info.is_old_auth">
                <template #name>카드비밀번호 앞 2자리</template>
                <template #input>
                    <VTextField v-model="props.hand_pay_info.card_pw" counter prepend-inner-icon="tabler-lock"
                        :append-inner-icon="is_show ? 'tabler-eye' : 'tabler-eye-off'"
                        :type="is_show ? 'number' : 'password'" persistent-placeholder
                        @click:append-inner="is_show = !is_show" autocomplete maxlength="2" />
                </template>
            </CreateHalfVCol>
        </VForm>
    </AppCardActions>
</template>
<style>
:deep(.v-card-item) {
  padding: 18px !important;
}
</style>
