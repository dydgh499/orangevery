<script setup lang="ts">
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import { installments, simplePays } from '@/views/merchandises/pay-modules/useStore';
import type { Merchandise, Options, PayModule, SimplePay } from '@/views/types';
import corp from '@corp';
import { requiredValidatorV2 } from '@validators';
import { reactive, watchEffect } from 'vue';
import { VForm } from 'vuetify/components';

interface Props {
    return_url: string,
    pay_url: string,
    pay_module: PayModule, 
    merchandise: Merchandise,
}
const props = defineProps<Props>()

const is_show_pay_button = ref(corp.pv_options.paid.use_pay_verification_mobile ? false : true)
const simple_pay_info = reactive(<SimplePay>({}))
const vForm = ref<VForm>()

const urlParams = new URLSearchParams(window.location.search)
simple_pay_info.item_name = urlParams.get('item_name') || ''
simple_pay_info.buyer_name = urlParams.get('buyer_name') || ''
simple_pay_info.buyer_phone = urlParams.get('phone_num') || ''
simple_pay_info.amount = Number(urlParams.get('amount') || '')
simple_pay_info.installment = 0

const filterInstallment = computed(() => {
    return installments.filter((obj: Options) => { return obj.id <= (props.pay_module.installment || 0) })
})
const updateToken = (value : string) => {
    if(value.length > 10) {
        is_show_pay_button.value = true
    }
}

watchEffect(() => {
    simple_pay_info.pmod_id = props.pay_module.id
    simple_pay_info.return_url = props.return_url
    simple_pay_info.ord_num = props.pay_module.id + "S" + Date.now().toString().substr(0, 10)
    if(props.merchandise.use_pay_verification_mobile === 0)
        is_show_pay_button.value = true
})
</script>
<template>
    <VCard flat rounded>
        <VCardText>
            <slot name="explain">

            </slot>
            <VDivider />
            <VForm ref="vForm" :action="pay_url" method="post">
                <VTextField v-model="simple_pay_info.pmod_id" type="visible" name="pmod_id" style="display: none;" />
                <VTextField v-model="simple_pay_info.return_url" type="visible" name="return_url" style="display: none;" />
                <VTextField v-model="simple_pay_info.ord_num" type="visible" name="ord_num" style="display: none;" />
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0; margin-top: 24px;">
                    <template #name>상품명</template>
                    <template #input>
                        <VTextField v-model="simple_pay_info.item_name" type="text" name="item_name"
                            prepend-inner-icon="streamline:shopping-bag-hand-bag-2-shopping-bag-purse-goods-item-products"
                            maxlength="100" :rules="[requiredValidatorV2(simple_pay_info.item_name, '상품명')]" placeholder="상품명을 입력해주세요" counter />
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                    <template #name>상품금액</template>
                    <template #input>
                        <VTextField v-model="simple_pay_info.amount" type="number" suffix="₩" name="amount"
                            placeholder="상품금액을 입력해주세요" prepend-inner-icon="ic:outline-price-change"
                            :rules="[requiredValidatorV2(simple_pay_info.amount, '상품금액')]" />
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 24px 0;">
                    <template #name>구매자명</template>
                    <template #input>
                        <VTextField v-model="simple_pay_info.buyer_name" type="text" name="buyer_name"
                            placeholder="구매자명을 입력해주세요" :rules="[requiredValidatorV2(simple_pay_info.buyer_name, '구매자명')]" prepend-inner-icon="tabler-user" />
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                    <template #name>구매자 연락처</template>
                    <template #input>
                        <VTextField v-model="simple_pay_info.buyer_phone" type="number" name="buyer_phone"
                            prepend-inner-icon="tabler-device-mobile" placeholder="구매자 연락처를 입력해주세요"
                            :rules="[requiredValidatorV2(simple_pay_info.buyer_phone, '구매자 연락처')]" />
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 24px 0;">
                    <template #name>결제방식</template>
                    <template #input>
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="simple_pay_info.route" name="route"
                            :items="simplePays" prepend-inneer-icon="fluent-credit-card-clock-20-regular" label="결제방식 선택"
                            item-title="title" item-value="id" single-line />
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0; padding-bottom: 24px;">
                    <template #name>할부기간</template>
                    <template #input>
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="simple_pay_info.installment" name="installment"
                            :items="filterInstallment" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                            label="할부기간 선택" item-title="title" item-value="id" single-line />
                    </template>
                </CreateHalfVCol>

                <MobileVerification v-if="corp.pv_options.paid.use_pay_verification_mobile && props.merchandise.use_pay_verification_mobile"
                    @update:token="updateToken($event)" :phone_num="simple_pay_info.buyer_phone" 
                    :merchandise="props.merchandise"/>
                <VCol cols="12" style="padding: 0;">
                    <VBtn block type="submit">
                        결제하기
                    </VBtn>
                </VCol>
            </VForm>
        </VCardText>
    </VCard>
</template>
