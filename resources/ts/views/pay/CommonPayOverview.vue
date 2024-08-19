<script setup lang="ts">
import { installments } from '@/views/merchandises/pay-modules/useStore';
import type { BasePay, Merchandise, Options, PayModule } from '@/views/types';
import corp from '@corp';
import { requiredValidatorV2 } from '@validators';
import { useDisplay } from 'vuetify';

interface Props {
    common_info: BasePay,
    pay_module: PayModule, 
    merchandise: Merchandise,
    pay_code: string
}

const props = defineProps<Props>()
const { mobile } = useDisplay()
const params = <any>(inject('params'))
const params_mode = <any>(inject('params_mode'))

const is_show_pay_button = ref(corp.pv_options.paid.use_pay_verification_mobile ? false : true)
const format_amount = ref('0')

props.common_info.installment = 0

const updateToken = (value : string) => {
    if(value.length > 10) {
        is_show_pay_button.value = true
    }
}

const ableMobileVerification = computed(() => {
    return corp.pv_options.paid.use_pay_verification_mobile && props.merchandise.use_pay_verification_mobile
})

const filterInstallment = computed(() => {
    return installments.filter((obj: Options) => { return obj.id <= (props.pay_module.installment || 0) })
})

const formatAmount = computed(() => {
    const parse_amount = parseFloat(format_amount.value.replace(/,/g, "")) || 0;
    props.common_info.amount = parse_amount
    format_amount.value = parse_amount.toLocaleString()
})

watchEffect(() => {
    props.common_info.user_agent = mobile ? "WM" : "WP"
    props.common_info.pmod_id = props.pay_module.id
    props.common_info.ord_num = props.pay_module.id + props.pay_code + Date.now().toString().substr(0, 10)
    if(props.merchandise.use_pay_verification_mobile == 0)
        is_show_pay_button.value = true
})

watchEffect(() => {
    if(params_mode) {
        props.common_info.item_name = params.value.item_name
        props.common_info.buyer_name = params.value.buyer_name
        props.common_info.buyer_phone = params.value.buyer_phone

        format_amount.value = params.value.amount.toString()
        props.common_info.amount = params.value.amount
    }
})
</script>
<template>
    <VCardTitle style="margin: 0.5em 0;"><b>상품정보</b></VCardTitle>
    <VTextField v-model="props.common_info.pmod_id" type="visible" name="pmod_id" style="display: none;" />
    <VTextField v-model="props.common_info.ord_num" type="visible" name="ord_num" style="display: none;" />
    <VTextField v-model="props.common_info.user_agent" type="visible" name="user_agent" style="display: none;" />
    <VCol style="padding: 0 12px;">
        <VRow>
            <VCol md="12" cols="12" style="padding: 0 12px;">
                <VRow no-gutters style="min-height: 3.5em;">
                    <VCol cols="4" :md="2">
                        <label>상품명</label>
                    </VCol>
                    <VCol cols="8" :md="10">
                        <VTextField v-model="props.common_info.item_name" name="item_name"
                            prepend-icon="tabler:shopping-bag"
                            maxlength="100" 
                            counter
                            variant="underlined"
                            :rules="[requiredValidatorV2(props.common_info.item_name, '상품명')]" 
                            placeholder="상품명을 입력해주세요" 
                            :disabled="params_mode ? true : false"/>
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
                        <VTextField v-model="props.common_info.buyer_name" name="buyer_name"
                            variant="underlined"
                            placeholder="구매자명을 입력해주세요" :rules="[requiredValidatorV2(props.common_info.buyer_name, '구매자명')]" 
                            prepend-icon="tabler-user" 
                            :disabled="params_mode ? true : false"/>
                    </VCol>
                </VRow>
            </VCol>
            <VCol md="6" cols="12" style="padding: 0 12px;">
                <VRow no-gutters style="min-height: 3.5em;">
                    <VCol cols="4" :md="4">
                        <label>연락처</label>
                    </VCol>
                    <VCol cols="8" :md="8">
                        <VTextField v-model="props.common_info.buyer_phone" type="number" name="buyer_phone"
                        variant="underlined"
                            prepend-icon="tabler-device-mobile" placeholder="구매자 연락처를 입력해주세요"
                            :rules="[requiredValidatorV2(props.common_info.buyer_phone, '구매자 연락처')]" 
                            :disabled="params_mode ? true : false"/>
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
                        <VTextField v-model="format_amount" suffix="₩" name="amount"
                            @input="formatAmount"
                            variant="underlined"
                            placeholder="상품금액을 입력해주세요" prepend-icon="ic:outline-price-change"
                            :rules="[requiredValidatorV2(props.common_info.amount, '상품금액')]" 
                            :disabled="params_mode ? true : false"/>
                    </VCol>
                </VRow>
            </VCol>
            <VCol md="6" cols="12" style="padding: 0 12px;">
                <VRow no-gutters style="min-height: 3.5em;">
                    <VCol cols="4" :md="4">
                        <label>할부기간</label>
                    </VCol>
                    <VCol cols="8" :md="8">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.common_info.installment" name="installment"
                            variant="underlined"
                            :items="filterInstallment" prepend-icon="fluent-credit-card-clock-20-regular"
                            label="할부기간 선택" item-title="title" item-value="id" single-line :rules="[requiredValidatorV2(props.common_info.installment, '할부기간')]" />
                    </VCol>
                </VRow>
            </VCol>
        </VRow>
        <slot name="extra_info"></slot> 
    </VCol>
    <br>
    <MobileVerification 
        v-if="ableMobileVerification"
        @update:token="updateToken($event)" 
        :phone_num="props.common_info.buyer_phone" 
        :merchandise="props.merchandise"
    />
    <VCol cols="12" style="padding: 0;" v-if="is_show_pay_button">
        <VBtn block type="submit">
            결제하기
        </VBtn>
    </VCol>

</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
