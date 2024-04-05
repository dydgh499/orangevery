<script setup lang="ts">
import { useMchtBlacklistStore } from '@/views/services/mcht-blacklists/useStore'
import { installments } from '@/views/merchandises/pay-modules/useStore'
import { requiredValidatorV2 } from '@validators'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { reactive, watchEffect } from 'vue';
import { VForm } from 'vuetify/components'
import type { Options, AuthPay, PayModule, Merchandise } from '@/views/types'
import corp from '@corp'

interface Props {
    return_url: string,
    pay_url: string,
    pay_module: PayModule, 
    merchandise: Merchandise,
}
const props = defineProps<Props>()

const { customValidFormRequest } =  useMchtBlacklistStore()
const is_show_pay_button = ref(corp.pv_options.paid.use_pay_verification_mobile ? false : true)
const auth_pay_info = reactive(<AuthPay>({}))
const vForm = ref<VForm>()

const urlParams = new URLSearchParams(window.location.search)
auth_pay_info.item_name = urlParams.get('item_name') || ''
auth_pay_info.buyer_name = urlParams.get('buyer_name') || ''
auth_pay_info.buyer_phone = urlParams.get('phone_num') || ''
auth_pay_info.amount = Number(urlParams.get('amount') || '')
auth_pay_info.installment = 0

const filterInstallment = computed(() => {
    return installments.filter((obj: Options) => { return obj.id <= (props.pay_module.installment || 0) })
})

const isMobile = () => {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
}

watchEffect(() => {
    console.log(is_show_pay_button.value)
    auth_pay_info.user_agent = isMobile() ? "WM" : "WP"
    auth_pay_info.pmod_id = props.pay_module.id
    auth_pay_info.return_url = props.return_url
    auth_pay_info.ord_num = props.pay_module.id + "A" + Date.now().toString().substr(0, 10)
    if(props.merchandise.use_pay_verification_mobile == 0)
        is_show_pay_button.value = true
})

watchEffect(async() => {
    await customValidFormRequest(props.merchandise)
})
</script>
<template>
    <VCard flat rounded>
        <VCardText>
            <slot name="explain">

            </slot>
            <VDivider />
            <VForm ref="vForm" :action="pay_url" method="post">
                <VTextField v-model="auth_pay_info.pmod_id" type="visible" name="pmod_id" style="display: none;" />
                <VTextField v-model="auth_pay_info.return_url" type="visible" name="return_url" style="display: none;" />
                <VTextField v-model="auth_pay_info.ord_num" type="visible" name="ord_num" style="display: none;" />
                <VTextField v-model="auth_pay_info.user_agent" type="visible" name="user_agent" style="display: none;" />
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0; margin-top: 24px;">
                    <template #name>상품명</template>
                    <template #input>
                        <VTextField v-model="auth_pay_info.item_name" type="text" name="item_name"
                            prepend-inner-icon="streamline:shopping-bag-hand-bag-2-shopping-bag-purse-goods-item-products"
                            maxlength="100" :rules="[requiredValidatorV2(auth_pay_info.item_name, '상품명')]" placeholder="상품명을 입력해주세요" counter 
                            :readonly="urlParams.get('item_name') ? true : false"/>
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                    <template #name>상품금액</template>
                    <template #input>
                        <VTextField v-model="auth_pay_info.amount" type="number" suffix="₩" name="amount"
                            placeholder="상품금액을 입력해주세요" prepend-inner-icon="ic:outline-price-change"
                            :rules="[requiredValidatorV2(auth_pay_info.amount, '상품금액')]" 
                            :readonly="urlParams.get('amount') ? true : false"/>
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 24px 0;">
                    <template #name>구매자명</template>
                    <template #input>
                        <VTextField v-model="auth_pay_info.buyer_name" type="text" name="buyer_name"
                            placeholder="구매자명을 입력해주세요" :rules="[requiredValidatorV2(auth_pay_info.buyer_name, '구매자명')]" prepend-inner-icon="tabler-user" 
                            :readonly="urlParams.get('buyer_name') ? true : false"/>
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0; padding-bottom: 24px;">
                    <template #name>구매자 연락처</template>
                    <template #input>
                        <VTextField v-model="auth_pay_info.buyer_phone" type="number" name="buyer_phone"
                            prepend-inner-icon="tabler-device-mobile" placeholder="구매자 연락처를 입력해주세요"
                            :rules="[requiredValidatorV2(auth_pay_info.buyer_phone, '구매자 연락처')]" 
                            :readonly="urlParams.get('phone_num') ? true : false"/>
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0; padding-bottom: 24px;">
                    <template #name>할부기간</template>
                    <template #input>
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="auth_pay_info.installment" name="installment"
                            :items="filterInstallment" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                            label="할부기간 선택" item-title="title" item-value="id" single-line :rules="[requiredValidatorV2(auth_pay_info.installment, '할부기간')]" />
                    </template>
                </CreateHalfVCol>

                <MobileVerification v-if="corp.pv_options.paid.use_pay_verification_mobile && props.merchandise.use_pay_verification_mobile"
                    @update:pay_button="is_show_pay_button = $event" :phone_num="auth_pay_info.buyer_phone" />
                <VCol cols="12" style="padding: 0;" v-if="is_show_pay_button">
                    <VBtn block type="submit">
                        결제하기
                    </VBtn>
                </VCol>
            </VForm>
        </VCardText>
    </VCard>
</template>
