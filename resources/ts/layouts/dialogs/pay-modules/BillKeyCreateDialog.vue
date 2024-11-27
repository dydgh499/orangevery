<script lang="ts" setup>
import { inputFormater } from '@/@core/utils/formatters';
import CreditCard from '@/layouts/components/CreditCard.vue';
import { axios } from '@/plugins/axios';
import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore';
import { useSalesFilterStore } from '@/views/salesforces/useStore';
import { PayModule } from '@/views/types';
import { lengthValidatorV2, requiredValidatorV2 } from '@validators';
import { VForm } from 'vuetify/components';

interface BillKeyCreate {
    pmod_id: number | null,
    card_num: string,
    yymm: string,
    auth_num: string,
    card_pw: string,    
    ord_num: string,
    buyer_name: string,
    buyer_phone: string,
}

const {
    phone_num_format,
    card_num_format,
    yymm_format,

    phone_num,
    card_num,
    yymm,

    formatPhoneNum,
    formatCardNum,
    formatYYmm,
} = inputFormater()

const vForm = ref<VForm>()
const visible = ref(false)

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const mcht_id = ref(null)
const { mchts } = useSalesFilterStore()
const pay_modules = ref<PayModule[]>([])
const bill_key = ref(<BillKeyCreate>{})

const show = ()  => {
    visible.value = true
    bill_key.value = (<BillKeyCreate>{
        pmod_id: null,
        card_num: '',
        yymm: '',
        auth_num: '',
        card_pw: '',
        ord_num: '',
        buyer_name: '',
        buyer_phone: '',
    })
}

const submit = async() => {
    const is_valid = await vForm.value.validate()
    if (is_valid.valid) {
        if(await alert.value.show('정말 빌키를 생성하시겠습니까?')) {
            axios.post(`/api/v1/manager/merchandises/pay-modules/bill-keys`, bill_key.value).then(r => {
                visible.value = false
                snackbar.value.show('성공하였습니다.', 'success')
            })
            .catch(async e => {
                snackbar.value.show(e.response.data.message, 'error')
                const r = errorHandler(e)
            })
        }
    }
}

const mchtUpdate = async () => {
    if(mcht_id.value) {
        bill_key.value.pmod_id = null
        pay_modules.value = await getAllPayModules(mcht_id.value)
    }
}

watchEffect(() => {
    bill_key.value.ord_num = bill_key.value.pmod_id + "BC" + Date.now().toString().substr(0, 10)
})

watchEffect(() => {
    bill_key.value.buyer_phone = phone_num.value
    bill_key.value.card_num = card_num.value
    bill_key.value.yymm = yymm.value
})

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="800">
        <DialogCloseBtn @click="visible = false" />
        <VCard title="빌키 생성">
            <VCardText>
                <VForm ref="vForm">
                    <VCol style="padding: 0 12px;">
                        <VRow>
                            <VCol md="6" cols="12">
                                <VRow no-gutters>
                                    <VCol cols="4" :md="4">
                                        <label>가맹점</label>
                                    </VCol>
                                    <VCol cols="8" :md="8">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="mcht_id" :items="mchts"
                                            variant="underlined"
                                            prepend-icon="tabler-building-store" item-title="mcht_name" item-value="id" single-line
                                            placeholder="가맹점 선택" 
                                            @update:modelValue="mchtUpdate"/>
                                    </VCol>
                                </VRow>
                            </VCol>
                            <VCol md="6" cols="12">
                                <VRow no-gutters>
                                    <VCol cols="4" :md="4">
                                        <label>결제모듈</label>
                                    </VCol>
                                    <VCol cols="8" :md="8">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="bill_key.pmod_id" :items="pay_modules"
                                            variant="underlined"
                                            prepend-icon="ic-outline-send-to-mobile" item-title="note" item-value="id" single-line
                                            placeholder="결제모듈 선택" 
                                            />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VRow>
                            <VDivider />
                            <VCol md="6" cols="12" style="padding: 0 12px;">
                                <VRow no-gutters style="min-height: 3.5em;">
                                    <VCol cols="4" :md="4">
                                        <label>구매자명</label>
                                    </VCol>
                                    <VCol cols="8" :md="8">
                                        <VTextField 
                                            v-model="bill_key.buyer_name"
                                            variant="underlined"
                                            placeholder="구매자명을 입력해주세요" :rules="[requiredValidatorV2(bill_key.buyer_name, '구매자명')]" 
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
                                        <VTextField 
                                            v-model="phone_num_format" 
                                            @input="formatPhoneNum"
                                            variant="underlined"
                                            prepend-icon="tabler-device-mobile" placeholder="구매자 연락처를 입력해주세요"
                                            :rules="[requiredValidatorV2(buyer_phone, '구매자 연락처')]"
                                            />
                                    </VCol>
                                </VRow>
                            </VCol>
                        </VRow>
                        <VRow>
                            <VDivider />
                            <VCol cols="12" style="padding: 0 12px;">
                                <VRow :style="$vuetify.display.smAndDown ? 'display: flex;flex-direction: column-reverse;' : ''">
                                    <VCol cols="12" md="6" style="display: flex;flex-direction: column;justify-content: space-around;">
                                        <VRow no-gutters style="min-height: 3.5em;">
                                            <VCol md="4" cols="4">
                                                <label>카드번호</label>
                                            </VCol>
                                            <VCol md="8" cols="8">
                                                <VTextField  
                                                    variant="underlined"
                                                    v-model="card_num_format"
                                                    @input="formatCardNum"
                                                    prepend-icon="tabler:credit-card"
                                                    placeholder="카드번호를 입력해주세요" 
                                                    :rules="[requiredValidatorV2(card_num, '카드번호')]"
                                                    maxlength="22" autocomplete="cc-number" />
                                            </VCol>
                                        </VRow>
                                        <VRow no-gutters style="min-height: 3.5em;">
                                            <VCol md="4" cols="4">
                                                <label>유효기간</label>
                                            </VCol>
                                            <VCol md="8" cols="8">
                                                <VTextField 
                                                    v-model="yymm_format" 
                                                    @input="formatYYmm"
                                                    placeholder="MM/YY" 
                                                    variant="underlined"
                                                    prepend-icon="ri:pass-expired-line"
                                                    :rules="[requiredValidatorV2(yymm, '유효기간'), lengthValidatorV2(bill_key.yymm, 4)]"
                                                    maxlength="5" style="min-inline-size: 11em;">
                                                    <VTooltip activator="parent" location="top">
                                                        카드의 유효기간 4자리를 입력해주세요.<br>
                                                        (MM/YY:0324)
                                                    </VTooltip>
                                                </VTextField>
                                            </VCol>
                                        </VRow>
                                        <VRow no-gutters style="min-height: 3.5em;">
                                            <VCol md="4" cols="4">
                                                <label>본인확인</label>
                                            </VCol>
                                            <VCol md="8" cols="8">
                                                <VTextField v-model="bill_key.auth_num" type="number" maxlength="10" variant="underlined"
                                                    prepend-icon="teenyicons:id-outline"
                                                    placeholder="생년월일6자리(사업자번호)" persistent-placeholder counter>
                                                    <VTooltip activator="parent" location="top">
                                                        개인카드일 경우 카드소유주의 생년월일6자리 입력,<br>법인카드의 경우 사업자등록번호를 입력해주세요.
                                                    </VTooltip>
                                                </VTextField>
                                            </VCol>
                                        </VRow>
                                        <VRow no-gutters style="min-height: 3.5em;">
                                            <VCol md="4" cols="4">
                                                <label>비밀번호</label>
                                            </VCol>
                                            <VCol md="8" cols="8">
                                                <div style="display: inline-flex; align-items: center;">
                                                    <VTextField v-model="bill_key.card_pw" 
                                                        type="password" 
                                                        prepend-icon="tabler:paywall"
                                                        variant="underlined"
                                                        persistent-placeholder
                                                        maxlength="2"
                                                        style="max-width: 4em;">
                                                        <VTooltip activator="parent" location="top">
                                                            카드비밀번호 앞 4자리 중 2자리를 입력해주세요.
                                                        </VTooltip>
                                                    </VTextField>
                                                    <b style="margin-left: 0.5em;">**</b>
                                                </div>
                                            </VCol>
                                        </VRow>
                                    </VCol>
                                    <VCol cols="12" md="6">
                                        <CreditCard
                                            :card_num="bill_key.card_num"
                                            :auth_num="bill_key.auth_num"
                                            :yymm="bill_key.yymm"
                                            :is_old_auth="1"
                                        />
                                    </VCol>                                    
                                </VRow>
                            </VCol>
                        </VRow>
                    </VCol>
                </VForm>
            </VCardText>
            <VDivider />
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn  @click="submit()">
                    생성하기
                </VBtn>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
.table-th,
.table-td {
  border-inline-end: thin solid rgba(var(--v-border-color), var(--v-border-opacity)) !important;
}

:deep(.v-row) {
  align-items: center;
}

.card-pay-th {
  padding: 0.5em;
}

.card-pay-td {
  padding: 0.5em;
}

:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
