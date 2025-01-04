<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters';
import DeliveryOverview from '@/layouts/components/pay-windows/DeliveryOverview.vue';
import ProductOverview from '@/layouts/components/pay-windows/ProductOverview.vue';
import type { BasePayInfo, BillKey, PayModule } from '@/views/types';
import { PayParamTypes } from '@core/enums';
import { requiredValidatorV2 } from '@validators';
import { useDisplay } from 'vuetify';

interface Props {
    common_info: BasePayInfo,
    pay_module: PayModule, 
    bill_key: BillKey,
}

const props = defineProps<Props>()

const params_mode = <any>(inject('params_mode'))
const params = <any>(inject('params'))

const {
    amount_format,
    amount,
    formatAmount,
} = inputFormater()

watchEffect(() => {
    
    const { mobile } = useDisplay()
    props.common_info.user_agent = mobile.value ? "WM" : "WP"
    props.common_info.pmod_id = props.common_info.pmod_id
    props.common_info.ord_num = props.common_info.pmod_id + "BP" + Date.now().toString().substr(0, 10)
})

watchEffect(() => {
    if(params_mode.value) {
        amount_format.value = params.value.amount.toString()
        props.common_info.amount = params.value.amount
    }
    else {
        props.common_info.amount = amount.value
    }
})
</script>
<template>
        <VCardTitle style="margin: 0.5em 0;"><b>상품정보</b></VCardTitle>
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
                                v-model="amount_format" suffix="₩" 
                                @input="formatAmount"
                                variant="underlined"
                                placeholder="상품금액을 입력해주세요" prepend-icon="ic:outline-price-change"
                                :rules="[requiredValidatorV2(props.common_info.amount, '상품금액')]" 
                            />
                        </VCol>
                    </VRow>
                </VCol>
            </VRow>
        </template>
        <VRow>
            <VDivider />
            <VCardTitle style="margin-top: 0.5em;"><b>구매자정보</b></VCardTitle>
        </VRow>
        <VRow>
            <VCol md="6" cols="12" style="padding: 0 12px;">
                <VRow no-gutters style="min-height: 3.5em;">
                    <VCol cols="4" :md="4">
                        <label>구매자명</label>
                    </VCol>
                    <VCol cols="8" :md="8">
                        <div style="display: inline-flex;" class="text-primary">
                            <VIcon size="24" icon="tabler-user" style="margin-right: 16px;"/>
                            <h4>{{ props.common_info.buyer_name }}</h4>
                        </div>
                    </VCol>
                </VRow>
            </VCol>
            <VCol md="6" cols="12" style="padding: 0 12px;">
                <VRow no-gutters style="min-height: 3.5em;">
                    <VCol cols="4" :md="4">
                        <label>연락처</label>
                    </VCol>
                    <VCol cols="8" :md="8">
                        <div style="display: inline-flex;" class="text-primary">
                            <VIcon size="24" icon="tabler-device-mobile" style="margin-right: 16px;"/>
                            <h4>{{ props.common_info.buyer_phone }}</h4>
                        </div>
                    </VCol>
                </VRow>
            </VCol>
        </VRow>
        <VRow cols="12">
            <VCol md="6" cols="12" style="padding: 0 12px;">
                <VRow no-gutters style="min-height: 3.5em;">
                    <VCol cols="4" :md="4">
                        <label>발급사</label>
                    </VCol>
                    <VCol cols="8" :md="8">
                        <div style="display: inline-flex;" class="text-primary">
                            <VIcon size="24" icon="tabler:credit-card" style="margin-right: 16px;"/>
                            <h4>{{ props.bill_key.issuer }}</h4>
                        </div>
                    </VCol>
                </VRow>
            </VCol>
            <VCol md="6" cols="12" style="padding: 0 12px;">
                <VRow no-gutters style="min-height: 3.5em;">
                    <VCol cols="4" :md="4">
                        <label>카드번호</label>
                    </VCol>
                    <VCol cols="8" :md="8">
                        <div style="display: inline-flex;" class="text-primary">
                            <VIcon size="24" icon="tabler:credit-card-filled" style="margin-right: 16px;"/>
                            <h4>{{ props.bill_key.card_num }}</h4>
                        </div> 
                    </VCol>
                </VRow>
            </VCol>
        </VRow>
        <DeliveryOverview :user_pay_info="props.common_info" v-if="params_mode === PayParamTypes.SHOP"/>
    </VCol>
    <br>
    <slot name="pay_group"></slot> 
</template>
