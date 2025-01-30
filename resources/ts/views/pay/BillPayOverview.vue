<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters';
import DeliveryOverview from '@/layouts/components/pay-windows/DeliveryOverview.vue';
import ProductOverview from '@/layouts/components/pay-windows/ProductOverview.vue';
import { installments } from '@/views/merchandises/pay-modules/useStore';
import type { BillKey, BillPay, Merchandise, Options, PayModule, PayWindow, SalesSlip } from '@/views/types';
import { getOnlyNumber } from '@/views/users/useStore';
import { axios } from '@axios';
import { PayParamTypes } from '@core/enums';
import { requiredValidatorV2 } from '@validators';
import { cloneDeep } from 'lodash';
import { useDisplay } from 'vuetify';
import { VForm } from 'vuetify/components';

interface Props {
    pay_module: PayModule, 
    merchandise: Merchandise,
    pay_window: PayWindow,
}

const getDefaultFormat = () => {
    return {
        id: 0,
        buyer_name: '',
        buyer_phone: '',
        resident_num_front: '',
    }   
}

const props = defineProps<Props>()
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const salesslip = <any>(inject('salesslip'))
const auth_token = <any>(inject('auth_token'))
const billKeySelectDialog = ref()

const vForm = ref<VForm>()
const bill_key = ref(<BillKey>({id: 0}))
const bill_pay = ref(<BillPay>(getDefaultFormat()))
const sale_slip = ref(<SalesSlip>({}))

const params_mode = <any>(inject('params_mode'))
const params = <any>(inject('params'))

const {    
    phone_num_format,
    phone_num,
    formatPhoneNum,

    amount_format,
    amount,
    formatAmount,
} = inputFormater()
bill_pay.value.delivery_type = false

const filterInstallment = computed(() => {
    return installments.filter((obj: Options) => { return obj.id <= (props.pay_module.installment || 0) })
})

const billKeySelect = async () => {
    const _bill_key = await billKeySelectDialog.value.show(props.pay_window.window_code, bill_pay.value, auth_token)
    if(_bill_key !== null) {
        bill_key.value = _bill_key
        bill_pay.value.buyer_name = bill_key.value.buyer_name
        bill_pay.value.buyer_phone = bill_key.value.buyer_phone
    }
}

const billKeyBack = () => {
    bill_key.value = getDefaultFormat()
}

const pay = async () => {
    if (bill_pay.value.pmod_id) {
        if(bill_key.value.id) {
            const is_valid = await vForm.value?.validate()
            if (is_valid?.valid && await alert.value.show(bill_pay.value.amount.toLocaleString() + '원을 결제하시겠습니까?')) {
                try {
                    const r = await axios.post(
                        `/api/v1/pay/${props.pay_window.window_code}/bill-keys/${bill_key.value.id}/pay`, 
                        Object.assign(cloneDeep(bill_pay.value), {token: auth_token.value})
                    )
                    sale_slip.value = {
                        ...r.data,
                        ...props.merchandise
                    }
                    sale_slip.value.module_type = 1 // 수기결제
                    salesslip.value.show(sale_slip.value, true)
                    snackbar.value.show('성공하였습니다.', 'success')
                }
                catch (e: any) {
                    if(e.response.data.code === 1999)
                        billKeyBack()
                    snackbar.value.show(e.response.data.message, 'error')
                    const r = errorHandler(e)
                }
            }
        }
        else
            snackbar.value.show('빌키를 선택해주세요.', 'error')
    }
    else
        snackbar.value.show('결제모듈을 선택해주세요.', 'error')
}

watchEffect(() => {
    const { mobile } = useDisplay()
    bill_pay.value.user_agent = mobile.value ? "WM" : "WP"
    bill_pay.value.pmod_id = props.pay_module.id
    bill_pay.value.ord_num = props.pay_module.id + "BP" + Date.now().toString().substr(0, 10)
})

watchEffect(() => {
    if(params_mode.value) {
        amount_format.value = params.value.amount.toString()
        bill_pay.value.amount = params.value.amount
    }
    else {
        bill_pay.value.amount = amount.value
    }

    if(params_mode.value === PayParamTypes.SMS) {
        phone_num_format.value = params.value.buyer_phone.toString()
        bill_pay.value.buyer_name = params.value.buyer_name     
        bill_pay.value.buyer_phone = params.value.buyer_phone
    }
    else
        bill_pay.value.buyer_phone = phone_num.value
})
</script>
<template>
    <VCard flat rounded>
        <VCardText style="padding: 0;">
            <VDivider />
            <VForm ref="vForm" @submit.prevent="pay">
                <VCardTitle style="margin: 0.5em 0;"><b>상품정보</b></VCardTitle>
                <VCol style="padding: 0 12px;">
                    <template v-if="params_mode === PayParamTypes.SHOP">
                        <ProductOverview :common_info="bill_pay" :pay_module="props.pay_module"/>
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
                                            <h4>{{ bill_pay.item_name }}</h4>
                                        </div>
                                        <VTextField v-else
                                            v-model="bill_pay.item_name"
                                            prepend-icon="tabler:shopping-bag"
                                            maxlength="100" 
                                            counter
                                            variant="underlined"
                                            :rules="[requiredValidatorV2(bill_pay.item_name, '상품명')]" 
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
                                            :rules="[requiredValidatorV2(bill_pay.amount, '상품금액')]" 
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
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="bill_pay.installment" name="installment"
                                            variant="underlined"
                                            :items="filterInstallment" prepend-icon="fluent-credit-card-clock-20-regular"
                                            label="할부기간 선택" item-title="title" item-value="id" single-line :rules="[requiredValidatorV2(bill_pay.installment, '할부기간')]" />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </template>
                    <VRow>
                        <br>
                        <VDivider />
                        <VCardTitle><b>구매자정보</b></VCardTitle>
                    </VRow>
                    <VRow>
                        <VCol md="6" cols="12" style="padding: 0 12px;">
                            <VRow no-gutters style="min-height: 3.5em;">
                                <VCol cols="4" :md="4">
                                    <label>구매자명</label>
                                </VCol>
                                <VCol cols="8" :md="8">
                                    <div v-if="params_mode === PayParamTypes.SMS" style="display: inline-flex;" class="text-primary">
                                        <VIcon size="24" icon="tabler-user" style="margin-right: 16px;"/>
                                        <h4>{{ bill_pay.buyer_name }}</h4>
                                    </div>
                                    <VTextField 
                                        v-else
                                        v-model="bill_pay.buyer_name"
                                        variant="underlined"
                                        placeholder="구매자명을 입력해주세요" :rules="[requiredValidatorV2(bill_pay.buyer_name, '구매자명')]" 
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
                                    <div v-if="params_mode === PayParamTypes.SMS" style="display: inline-flex;" class="text-primary">
                                        <VIcon size="24" icon="tabler-device-mobile" style="margin-right: 16px;"/>
                                        <h4>{{ phone_num_format }}</h4>
                                    </div>
                                    <VTextField 
                                        v-else
                                        v-model="phone_num_format" 
                                        @input="formatPhoneNum"
                                        variant="underlined"
                                        prepend-icon="tabler-device-mobile" placeholder="구매자 연락처를 입력해주세요"
                                        :rules="[requiredValidatorV2(bill_pay.buyer_phone, '구매자 연락처')]" 
                                        />
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <template v-if="bill_key.id">
                        <VRow cols="12">
                            <VCol md="6" cols="12" style="padding: 0 12px;">
                                <VRow no-gutters style="min-height: 3.5em;">
                                    <VCol cols="4" :md="4">
                                        <label>발급사</label>
                                    </VCol>
                                    <VCol cols="8" :md="8">
                                        <div style="display: inline-flex;" class="text-primary">
                                            <VIcon size="24" icon="tabler:credit-card" style="margin-right: 16px;"/>
                                            <h4>{{ bill_key.issuer }}</h4>
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
                                            <h4>{{ bill_key.card_num }}</h4>
                                        </div> 
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </template>
                    <template v-else>
                        <VRow>
                            <VCol md="6" cols="12" style="padding: 0 12px;">
                                <VRow no-gutters style="min-height: 3.5em;">
                                    <VCol cols="4" :md="4">
                                        <label>생년월일</label>
                                    </VCol>
                                    <VCol cols="8" :md="8">
                                        <VTextField 
                                            v-model="bill_pay.resident_num_front" 
                                            maxlength="6"
                                            variant="underlined"
                                            prepend-icon="carbon-identification"
                                            @update:model-value="bill_pay.resident_num_front = getOnlyNumber($event)"
                                            style="width: 13em;"
                                            placeholder="890101"
                                        />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                    </template>
                    <DeliveryOverview :user_pay_info="bill_pay" v-if="params_mode === PayParamTypes.SHOP && bill_pay.delivery_type === true"/>
                </VCol>
                <br>
                <div v-if="auth_token" style="display: flex; align-items: center; justify-content: space-between;">
                    <VBtn
                        @click="billKeySelect()" color="warning" style="width: 49%;">
                        빌키조회
                    </VBtn>
                    <VBtn type="submit" style="width: 49%;">
                        결제하기
                    </VBtn>
                </div>
                <div v-else>
                    <VBtn
                        @click="billKeySelect()" color="warning" block>
                        구매자 인증
                    </VBtn>
                </div>
            </VForm>
        </VCardText>
        <BillKeySelectDialog ref="billKeySelectDialog" />
    </VCard>
</template>
<style scoped>
.v-row {
  align-items: center;
}
</style>
