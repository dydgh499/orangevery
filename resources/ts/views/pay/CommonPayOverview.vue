<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters';
import { installments } from '@/views/merchandises/pay-modules/useStore';
import type { BasePay, Merchandise, Options, PayModule } from '@/views/types';
import { requiredValidatorV2 } from '@validators';
import { useDisplay } from 'vuetify';

interface Props {
    common_info: BasePay,
    pay_module: PayModule, 
    merchandise: Merchandise,
    pay_code: string
}

const props = defineProps<Props>()
const params_mode = <any>(inject('params_mode'))
const params = <any>(inject('params'))

const is_verify_sms = ref(false)

const {
    phone_num_format,
    amount_format,
    phone_num,
    amount,
    formatPhoneNum,
    formatAmount,
} = inputFormater()

props.common_info.installment = 0

const updateToken = (value : string) => {
    if(value.length > 10) {
        is_verify_sms.value = true
    }
}

const isShowMobileVerification = computed(() => {
    return props.pay_module.pay_window_secure_level >= 3 && is_verify_sms.value === false
})

const filterInstallment = computed(() => {
    return installments.filter((obj: Options) => { return obj.id <= (props.pay_module.installment || 0) })
})

watchEffect(() => {
    const { mobile } = useDisplay()
    props.common_info.user_agent = mobile.value ? "WM" : "WP"
    props.common_info.pmod_id = props.pay_module.id
    props.common_info.ord_num = props.pay_module.id + props.pay_code + Date.now().toString().substr(0, 10)
})

watchEffect(() => {
    if(params_mode.value) {
        phone_num_format.value = params.value.buyer_phone.toString()
        amount_format.value = params.value.amount.toString()
        props.common_info.item_name = params.value.item_name
        props.common_info.buyer_name = params.value.buyer_name
        props.common_info.buyer_phone = params.value.buyer_phone
        props.common_info.amount = params.value.amount
    }
    else {
        props.common_info.buyer_phone = phone_num.value
        props.common_info.amount = amount.value
    }
})
</script>
<template>
    <VCardTitle style="margin: 0.5em 0;"><b>상품정보</b></VCardTitle>
    <VTextField v-model="props.common_info.pmod_id" type="visible" name="pmod_id" style="display: none;" />
    <VTextField v-model="props.common_info.ord_num" type="visible" name="ord_num" style="display: none;" />
    <VTextField v-model="props.common_info.user_agent" type="visible" name="user_agent" style="display: none;" />
    <VTextField v-model="props.common_info.buyer_phone" type="visible" name="buyer_phone" style="display: none;" />
    <VTextField v-model="props.common_info.amount" type="visible" name="amount" style="display: none;" />
    <VTextField v-model="props.common_info.item_name" type="visible" name="item_name" style="display: none;" />
    <VTextField v-model="props.common_info.buyer_name" type="visible" name="buyer_name" style="display: none;" />
    <VCol style="padding: 0 12px;">
        <VRow>
            <VCol md="12" cols="12" style="padding: 0 12px;">
                <VRow no-gutters style="min-height: 3.5em;">
                    <VCol cols="4" :md="2">
                        <label>상품명</label>
                    </VCol>
                    <VCol cols="8" :md="10">
                        <div v-if="params_mode" style="display: inline-flex;" class="text-primary">
                            <VIcon size="24" icon="tabler:shopping-bag" style="margin-right: 16px;"/>
                            <h4>{{ props.common_info.item_name }}</h4>
                        </div>
                        <VTextField v-else
                            v-model="props.common_info.item_name"
                            prepend-icon="tabler:shopping-bag"
                            maxlength="100" 
                            counter
                            variant="underlined"
                            :rules="[requiredValidatorV2(props.common_info.item_name, '상품명')]" 
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
                        <div v-if="params_mode" style="display: inline-flex;" class="text-primary">
                            <VIcon size="24" icon="tabler-user" style="margin-right: 16px;"/>
                            <h4>{{ props.common_info.buyer_name }}</h4>
                        </div>
                        <VTextField 
                            v-else
                            v-model="props.common_info.buyer_name"
                            variant="underlined"
                            placeholder="구매자명을 입력해주세요" :rules="[requiredValidatorV2(props.common_info.buyer_name, '구매자명')]" 
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
                        <div v-if="params_mode" style="display: inline-flex;" class="text-primary">
                            <VIcon size="24" icon="tabler-device-mobile" style="margin-right: 16px;"/>
                            <h4>{{ phone_num_format }}</h4>
                        </div>
                        <VTextField 
                            v-else
                            v-model="phone_num_format" 
                            @input="formatPhoneNum"
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
                        <div v-if="params_mode" style="display: inline-flex;" class="text-primary">
                            <VIcon size="24" icon="ic:outline-price-change" style="margin-right: 16px;"/>
                            <h4>{{ amount_format }} 원</h4>
                        </div>
                        <VTextField 
                            v-else
                            v-model="amount_format" suffix="₩" 
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
        v-if="isShowMobileVerification"
        @update:token="updateToken($event)" 
        :phone_num="props.common_info.buyer_phone" 
        :merchandise="props.merchandise"
    />
    <VCol cols="12" style="padding: 0;" v-else>
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
