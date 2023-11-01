<script setup lang="ts">
import { installments } from '@/views/merchandises/pay-modules/useStore'
import { requiredValidator, lengthValidatorV2 } from '@validators'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { reactive, watchEffect } from 'vue';
import { VForm } from 'vuetify/components'
import type { Merchandise, SalesSlip, Options, HandPay } from '@/views/types'
import { cloneDeep } from 'lodash'
import { axios } from '@axios'
import corp from '@corp'

interface Props {
    pmod_id: number,
    installment: number,
    is_old_auth: boolean,
    merchandise: Merchandise
}
const props = defineProps<Props>()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const salesslip = <any>(inject('salesslip'))

const sale_slip = ref(<SalesSlip>({}))
const hand_pay_info = reactive(<HandPay>({
    yymm: '',
    card_num: '',
    buyer_name: '',
}))
const is_show = ref(false)
const vForm = ref<VForm>()
const is_show_pay_button = ref(corp.pv_options.paid.use_pay_verification_mobile ? false : true)

const urlParams = new URLSearchParams(window.location.search)
hand_pay_info.item_name = urlParams.get('item_name') || ''
hand_pay_info.buyer_name = urlParams.get('buyer_name') || ''
hand_pay_info.buyer_phone = urlParams.get('phone_num') || ''
hand_pay_info.amount = Number(urlParams.get('amount') || '')

const pay = async () => {
    if (hand_pay_info.pmod_id) {
        const is_valid = await vForm.value?.validate()
        if (is_valid?.valid && await alert.value.show('정말 결제하시겠습니까?')) {
            //5389038218744126
            try {
                const params = cloneDeep(hand_pay_info)
                const r = await axios.post('/api/v1/transactions/hand-pay', params)
                sale_slip.value.acquirer = r.data.acquirer
                sale_slip.value.issuer = r.data.issuer
                sale_slip.value.amount = r.data.amount
                sale_slip.value.buyer_name = r.data.buyer_name
                sale_slip.value.card_num = r.data.card_num
                sale_slip.value.item_name = r.data.item_name
                sale_slip.value.appr_num = r.data.appr_num
                sale_slip.value.installment = r.data.installment
                sale_slip.value.trx_dttm = r.data.trx_dttm
                sale_slip.value.is_cancel = Boolean(r.data.is_cancel)

                sale_slip.value.addr = props.merchandise.addr
                sale_slip.value.business_num = props.merchandise.business_num
                sale_slip.value.resident_num = props.merchandise.resident_num
                sale_slip.value.mcht_name = props.merchandise.mcht_name
                sale_slip.value.nick_name = props.merchandise.nick_name
                sale_slip.value.is_show_fee = props.merchandise.is_show_fee
                sale_slip.value.use_saleslip_prov = props.merchandise.use_saleslip_prov
                sale_slip.value.use_saleslip_sell = props.merchandise.use_saleslip_sell

                salesslip.value.show(sale_slip.value)
                snackbar.value.show('성공하였습니다.', 'success')
            }
            catch (e: any) {
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            }

        }
    }
    else
        snackbar.value.show('결제모듈을 선택해주세요.', 'error')
}
const filterInstallment = computed(() => {
    return installments.filter((obj: Options) => { return obj.id <= (props.installment || 0) })
})
watchEffect(() => {
    console.log(is_show_pay_button.value)
    hand_pay_info.pmod_id = props.pmod_id
    hand_pay_info.is_old_auth = props.is_old_auth
    hand_pay_info.ord_num = props.pmod_id + "H" + Date.now().toString().substr(0, 10)
})
</script>
<template>
    <VCard flat rounded>
        <VCardText>
            <slot name="explain">
            </slot>
            <VDivider />
            <VForm ref="vForm" @submit.prevent="pay">
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0; margin: 12px 0;">
                    <template #name>상품명</template>
                    <template #input>
                        <VTextField v-model="hand_pay_info.item_name" type="text"
                            prepend-inner-icon="streamline:shopping-bag-hand-bag-2-shopping-bag-purse-goods-item-products"
                            maxlength="100" :rules="[requiredValidator]" placeholder="상품명을 입력해주세요" />
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                    <template #name>상품금액</template>
                    <template #input>
                        <VTextField v-model="hand_pay_info.amount" type="number" suffix="₩" placeholder="거래금액을 입력해주세요"
                            prepend-inner-icon="ic:outline-price-change" :rules="[requiredValidator]" />
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 12px 0;">
                    <template #name>구매자명</template>
                    <template #input>
                        <VTextField v-model="hand_pay_info.buyer_name" type="text" placeholder="구매자명을 입력해주세요"
                            :rules="[requiredValidator]" prepend-inner-icon="tabler-user" />
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style=" padding: 0;">
                    <template #name>휴대폰번호</template>
                    <template #input>
                        <VTextField v-model="hand_pay_info.buyer_phone" type="number"
                            prepend-inner-icon="tabler-device-mobile" placeholder="휴대폰번호를 입력해주세요"
                            :rules="[requiredValidator]" />
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0; padding-top: 12px;">
                    <template #name>카드번호</template>
                    <template #input>
                        <VTextField v-model="hand_pay_info.card_num" type="text" persistent-placeholder counter
                            prepend-inner-icon="emojione:credit-card" placeholder="카드번호를 입력해주세요"
                            :rules="[requiredValidator]" maxlength="18" autocomplete="cc-number" />
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 0;">
                    <template #name>유효기간</template>
                    <template #input>
                        <VTextField v-model="hand_pay_info.yymm" type="number" prepend-inner-icon="ic-baseline-calendar-today"
                            placeholder="(MM/YY:0324)"
                            :rules="[requiredValidator, lengthValidatorV2(hand_pay_info.yymm, 4)]" maxlength="4" />
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="4" :mdr="8" style="padding: 12px 0;">
                    <template #name>할부기간</template>
                    <template #input>
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="hand_pay_info.installment"
                            :items="filterInstallment" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                            item-title="title" item-value="id" single-line :rules="[requiredValidator]" />
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="6" :mdr="6" style="padding: 6px 0;" v-if="hand_pay_info.is_old_auth">
                    <template #name>생년월일(사업자등록번호)</template>
                    <template #input>
                        <VTextField v-model="hand_pay_info.auth_num" type="number" maxlength="10"
                            prepend-inner-icon="carbon:two-factor-authentication" />
                    </template>
                </CreateHalfVCol>
                <CreateHalfVCol :mdl="6" :mdr="6" style="padding: 6px 0;" v-if="hand_pay_info.is_old_auth">
                    <template #name>카드비밀번호 앞 2자리</template>
                    <template #input>
                        <VTextField v-model="hand_pay_info.card_pw" counter prepend-inner-icon="tabler-lock"
                            :append-inner-icon="is_show ? 'tabler-eye' : 'tabler-eye-off'"
                            :type="is_show ? 'number' : 'password'" persistent-placeholder
                            @click:append-inner="is_show = !is_show" autocomplete maxlength="2" />
                    </template>
                </CreateHalfVCol>

                <MobileVerification v-if="corp.pv_options.paid.use_pay_verification_mobile"
                    @update:pay_button="is_show_pay_button = $event" :phone_num="hand_pay_info.buyer_phone" />

                <VCol cols="12" style="padding: 0;" v-if="is_show_pay_button">
                    <VBtn block type="submit">
                        결제하기
                    </VBtn>
                </VCol>
            </VForm>
        </VCardText>
    </VCard>
</template>
