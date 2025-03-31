<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters';
import CommonOverview from '@/layouts/components/pay-windows/CommonOverview.vue';
import DeliveryOverview from '@/layouts/components/pay-windows/DeliveryOverview.vue';
import ProductOverview from '@/layouts/components/pay-windows/ProductOverview.vue';
import { installments } from '@/views/merchandises/pay-modules/useStore';
import type { BasePayInfo, Merchandise, Options, PayModule } from '@/views/types';
import { PayParamTypes } from '@core/enums';
import { requiredValidatorV2 } from '@validators';
import { useDisplay } from 'vuetify';

interface Props {
    common_info: BasePayInfo,
    pay_module: PayModule, 
    merchandise: Merchandise,
    pay_code: string
}

const props = defineProps<Props>()

const params_mode = <any>(inject('params_mode'))
const params = <any>(inject('params'))
const auth_token = <any>(inject('auth_token'))
const is_verify_sms = ref(false)

const {
    amount_format,
    amount,
    formatAmount,
} = inputFormater()

props.common_info.installment = 0
props.common_info.delivery_type = false
const updateToken = (value : string) => {
    auth_token.value = value
    if(value.length > 10) {
        is_verify_sms.value = true
    }
}

const isShowMobileVerification = computed(() => {
    return props.pay_module.pay_window_secure_level >= 3 && is_verify_sms.value === false
})


const filterInstallment = computed(() => {
    if(props.common_info.amount >= 50000)
        return installments.filter((obj: Options) => { return obj.id <= (props.pay_module.installment || 0) })
    else {
        props.common_info.installment = 0
        return [{ id: 0, title: "일시불" }]        
    }
})

watchEffect(() => {
    const { mobile } = useDisplay()
    props.common_info.user_agent = mobile.value ? "WM" : "WP"
    props.common_info.pmod_id = props.pay_module.id
    props.common_info.ord_num = props.pay_module.id + props.pay_code + Date.now().toString().substr(0, 10)
})

watchEffect(() => {
    if(params_mode.value) {
        amount_format.value = params.value.amount.toString()
        props.common_info.item_name = params.value.item_name
        props.common_info.amount = params.value.amount
    }
    else {
        props.common_info.amount = amount.value
    }
})

</script>
<template>
    <VCardTitle style="margin-top: 0.5em;"><b>상품정보</b></VCardTitle>
    <VTextField v-model="props.common_info.pmod_id" type="visible" name="pmod_id" style="display: none;" />
    <VTextField v-model="props.common_info.ord_num" type="visible" name="ord_num" style="display: none;" />
    <VTextField v-model="props.common_info.user_agent" type="visible" name="user_agent" style="display: none;" />
    <VTextField v-model="props.common_info.buyer_phone" type="visible" name="buyer_phone" style="display: none;" />
    <VTextField v-model="props.common_info.amount" type="visible" name="amount" style="display: none;" />
    <VTextField v-model="props.common_info.item_name" type="visible" name="item_name" style="display: none;" />
    <VTextField v-model="props.common_info.buyer_name" type="visible" name="buyer_name" style="display: none;" />
    
    <VTextField v-model="props.common_info.option_groups" type="visible" name="option_groups" style="display: none;" />
    <VTextField v-model="props.common_info.addr" type="visible" name="addr" style="display: none;" />
    <VTextField v-model="props.common_info.detail_addr" type="visible" name="detail_addr" style="display: none;" />
    <VTextField v-model="props.common_info.note" type="visible" name="note" style="display: none;" />
    
    <VCol style="padding: 0 12px;">
        <template v-if="params_mode === PayParamTypes.SHOP">
            <ProductOverview :common_info="props.common_info" :pay_module="props.pay_module"/>
        </template>
        <template v-else>
            <VRow>
                <VCol md="12" cols="12" style="padding: 0 12px;">
                    <VRow no-gutters style="min-height: 3.5em;">
                        <VCol cols="4" :md="2">
                            <label>상품명</label>
                        </VCol>
                        <VCol cols="8" :md="10">
                            <div v-if="params_mode === PayParamTypes.SMS" style="display: inline-flex;" class="text-primary">
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
            <VRow cols="12">
                <VCol md="6" cols="12" style="padding: 0 12px;">
                    <VRow no-gutters style="min-height: 3.5em;">
                        <VCol cols="4" :md="4">
                            <label>상품금액</label>
                        </VCol>
                        <VCol cols="8" :md="8">
                            <div v-if="params_mode === PayParamTypes.SMS" style="display: inline-flex;" class="text-primary">
                                <VIcon size="24" icon="ic:outline-price-change" style="margin-right: 16px;"/>
                                <h4>{{ amount_format }} 원</h4>
                            </div>
                            <VTextField 
                                v-else
                                v-model="amount_format" suffix="원" 
                                @input="formatAmount"
                                variant="underlined"
                                placeholder="상품금액을 입력해주세요" prepend-icon="ic:outline-price-change"
                                :rules="[requiredValidatorV2(props.common_info.amount, '상품금액')]" 
                            />
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
                                variant="underlined" :disabled="props.common_info.amount < 50000"
                                :items="filterInstallment" prepend-icon="fluent-credit-card-clock-20-regular"
                                label="할부기간 선택" item-title="title" item-value="id" single-line :rules="[requiredValidatorV2(props.common_info.installment, '할부기간')]" />
                            <VTooltip activator="parent" location="top" v-if="props.common_info.amount < 50000">
                                결제금액이 50,000원 이상일 때 가능합니다.
                            </VTooltip>
                        </VCol>
                    </VRow>
                </VCol>
            </VRow>
        </template>
        <CommonOverview :user_pay_info="props.common_info" :params_mode="params_mode" :params="params"/>
        <slot name="extra_info"></slot>
        <DeliveryOverview :user_pay_info="props.common_info" v-if="params_mode === PayParamTypes.SHOP && props.common_info.delivery_type === true"/>
    </VCol>
    <br>
    <MobileVerification 
        v-if="isShowMobileVerification"
        @update:token="updateToken($event)" 
        :phone_num="props.common_info.buyer_phone" 
        :merchandise="props.merchandise"
    />
    <template v-else>
        <VRow v-if="params_mode === PayParamTypes.SHOP">
        <VDivider/>
        <VCol cols="12" :md="12" class="mb-3">
                <VCheckbox 
                    v-model="props.common_info.delivery_type" class="check-label" 
                    label="배송여부" style="align-items: end;" 
                    :false-value=false :true-value=true
                />
            </VCol>
        </VRow>
        <VCol cols="12" style="padding: 0;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <VBtn block type="submit">
                    결제하기
                </VBtn>
            </div>
        </VCol>
    </template>
</template>
<style scoped>
.product-wrapper {
  border: 1px solid rgb(var(--v-border-color), var(--v-border-opacity));
  border-radius: 0.5em;
  margin: 0.5em;
}

:deep(.v-row) {
  align-items: center;
}
</style>
