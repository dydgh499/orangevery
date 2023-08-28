<script setup lang="ts">
import { installments } from '@/views/merchandises/pay-modules/useStore'
import { requiredValidator } from '@validators'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { reactive, watchEffect } from 'vue';
import { VForm } from 'vuetify/components'
import type { Options, AuthPay } from '@/views/types'

interface Props {
    pmod_id: number,
    installment: number,
    return_url: string,
    pay_url: string,
    pg_type: string,
}
const props = defineProps<Props>()

const auth_pay_info = reactive(<AuthPay>({}))
const vForm = ref<VForm>()

const urlParams = new URLSearchParams(window.location.search)
auth_pay_info.item_name = urlParams.get('item_name') || ''
auth_pay_info.buyer_name = urlParams.get('buyer_name') || ''
auth_pay_info.buyer_phone = urlParams.get('phone_num') || ''
auth_pay_info.amount = Number(urlParams.get('amount') || '')

const filterInstallment = computed(() => {
    return installments.filter((obj: Options) => { return obj.id <= (props.installment || 0) })
})

watchEffect(() => {
    auth_pay_info.pmod_id = props.pmod_id
    auth_pay_info.return_url = props.return_url
})
</script>
<template>
    <VCard flat rounded>
        <VCardText>
            <slot name="explain">

            </slot>
            <VDivider />
            <VForm ref="vForm" :action="pay_url" method="post">
                <div>
                    <VTextField type="visible" name="only" value="0" style="display: none;" />
                    <VTextField v-model="auth_pay_info.pmod_id" type="visible" name="pmod_id" style="display: none;" />
                    <VTextField v-model="auth_pay_info.return_url" type="visible" name="return_url" style="display: none;" />
                    <VCol cols="12">
                        <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                            <template #name>상품명</template>
                            <template #input>
                                <VTextField v-model="auth_pay_info.item_name" type="text" name="item_name"
                                    prepend-inner-icon="streamline:shopping-bag-hand-bag-2-shopping-bag-purse-goods-item-products"
                                    maxlength="100" :rules="[requiredValidator]" placeholder="상품명을 입력해주세요" counter />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                            <template #name>상품금액</template>
                            <template #input>
                                <VTextField v-model="auth_pay_info.amount" type="number" suffix="₩" name="amount"
                                    placeholder="거래금액을 입력해주세요" prepend-inner-icon="ic:outline-price-change"
                                    :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 24px 0;">
                            <template #name>구매자명</template>
                            <template #input>
                                <VTextField v-model="auth_pay_info.buyer_name" type="text" name="buyer_name"
                                    placeholder="구매자명을 입력해주세요" :rules="[requiredValidator]"
                                    prepend-inner-icon="tabler-user" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0; padding-bottom: 24px;">
                            <template #name>휴대폰번호</template>
                            <template #input>
                                <VTextField v-model="auth_pay_info.buyer_phone" type="number" name="phone"
                                    prepend-inner-icon="tabler-device-mobile" placeholder="구매자 연락처를 입력해주세요"
                                    :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0; padding-bottom: 24px;">
                            <template #name>할부기간</template>
                            <template #input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="auth_pay_info.installment"
                                    name="installment" :items="filterInstallment"
                                    prepend-inneer-icon="fluent-credit-card-clock-20-regular" label="할부기간 선택"
                                    item-title="title" item-value="id" single-line :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                        <VCol cols="12" style="padding: 0;">
                            <VBtn block type="submit">
                                결제하기
                            </VBtn>
                        </VCol>
                    </VCol>
                </div>
            </VForm>
        </VCardText>
    </VCard>
</template>
