<script setup lang="ts">
import { requiredValidator } from '@validators'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import MultipleHandPayForm from '@/views/pay/multiple-hand-pay/MultipleHandPayForm.vue'
import { VForm } from 'vuetify/components'
import type { Merchandise, PayModule, SalesSlip, MultipleHandPay } from '@/views/types'
import { axios } from '@axios'
import corp from '@corp'

interface Props {
    pay_modules: PayModule[],
    merchandise: Merchandise,
}
const props = defineProps<Props>()
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const salesslip = <any>(inject('salesslip'))

const sale_slips = ref(<SalesSlip[]>([]))
const hand_pay_info = ref(<MultipleHandPay>({}))
const hand_pay_infos = ref(<MultipleHandPay[]>([]))
const vForm = ref<VForm>()

const full_processes = ref<any[]>([])

const urlParams = new URLSearchParams(window.location.search)
const is_show_pay_button = ref(corp.pv_options.paid.use_pay_verification_mobile ? false : true)

const init = () => {
    hand_pay_info.value = (<MultipleHandPay>({
        yymm: '',
        card_num: '',
        installment: 0,
        item_name: urlParams.get('item_name') || '',
        buyer_name: urlParams.get('buyer_name') || '',
        buyer_phone: urlParams.get('phone_num') || '',
    }))
    props.pay_modules.forEach(pay_module => {
        hand_pay_infos.value.push(<MultipleHandPay>({
            yymm: String(''),
            card_num: String(''),
            installment: Number(0),
            amount: Number(urlParams.get('amount') || ''),
            pmod_id: pay_module.id,
            is_old_auth: pay_module.is_old_auth,
            ord_num: pay_module.id + "H" + Date.now().toString().substr(0, 10),
        }))
    });
}

const pay = (index: number) => {
    return axios.post('/api/v1/transactions/hand-pay', {
        ...hand_pay_info,
        ...hand_pay_infos.value[index]
    })
}

const cancel = (index: number) => {
    return axios.post('/api/v1/transactions/pay-cancel', {
        pmod_id: hand_pay_infos.value[index].pmod_id,
        amount: hand_pay_infos.value[index].amount,
        trx_id: full_processes.value[index].trx_result.trx_id,
        only: false,
    })
}

// level 0 다중결제 파라미터 구성
const getProcessObject = () => {
    return {
        'trx_process': <Promise<any>>({}),
        'trx_result': <SalesSlip>({}),
        'cxl_process': null,
        'cxl_result': <SalesSlip>({}),
    }
}

// level 1 결제
const trxProcess = () => {
    full_processes.value = []
    for (let i = 0; i < hand_pay_infos.value.length; i++) {
        full_processes.value.push(getProcessObject())
        full_processes.value[i].trx_process = pay(i)
    }
}

// level 2 결제 결과 처리
const trxResult = async () => {
    const results = await Promise.all(full_processes.value.map(item => item.trx_process))
    for (let i = 0; i < results.length; i++) {
        full_processes.value[i].trx_result = {
            ...results[i].data,
            ...props.merchandise
        }
    }
}

// level 3 성공건 취소 처리 (실패 존재할 경우만)
const cxlProcess = async () => {
    let fail_find = false
    for (let i = 0; i < full_processes.value.length; i++) {
        if (full_processes.value[i].trx_result.result_cd != "0000") {
            fail_find = true
            break
        }
    }
    if (fail_find) {
        snackbar.value.show('결제실패건을 발견하였으므로 성공건들을 모두 취소합니다.', 'error')
        for (let i = 0; i < full_processes.value.length; i++) {
            if (full_processes.value[i].trx_result.result_cd == "0000")
                full_processes.value[i].cxl_process = cancel(i)
            else
                full_processes.value[i].cxl_process = null
        }
        await cxlResult()
    }
}

// level 4 성공건 취소 결과 처리
const cxlResult = async () => {
    const results = await Promise.all(full_processes.value.map(item => item.cxl_process))
    for (let i = 0; i < results.length; i++) {
        if (results[i] != null) {
            full_processes.value[i].cxl_result = {
                ...results[i].data,
                ...props.merchandise
            }
        }
    }
}

// 다중 결제 시작
const pays = async () => {
    const common_valid = await vForm.value?.validate()
    const total_amount = hand_pay_infos.value.reduce((sum, obj) => sum + Number(obj.amount), 0)

    for (let i = 0; i < hand_pay_infos.value.length; i++) {
        if (hand_pay_infos.value[i].status_color != 'success') {
            snackbar.value.show(props.pay_modules[i].note + ' 결제모듈을 확인해주세요.', 'error')
            return
        }
    }

    if (common_valid?.valid && await alert.value.show("총 " + total_amount.toLocaleString() + '원을 결제하시겠습니까?')) {
        snackbar.value.show('다중결제를 시작합니다...', 'primary')
        trxProcess()
        await trxResult()
        await cxlProcess()
    }
}

watchEffect(async () => {
    if (hand_pay_info.value.buyer_name && hand_pay_info.value.buyer_phone) {
        // watchEffect가 잡히지 않는 이유?
    }
    let is_valid = await vForm.value?.validate()
    hand_pay_info.value.status_icon = is_valid?.valid ? 'line-md:check-all' : 'line-md:emoji-frown-twotone'
    hand_pay_info.value.status_color = is_valid?.valid ? 'success' : 'error'
})
onMounted(() => {
    init()

})
</script>
<template>
    <VDivider />
    <CreateHalfVCol :mdl="6" :mdr="6">
        <template #name>
            <AppCardActions :actionCollapsed="true" id="common-field">
                <template #title>
                    <div>
                        <span>공통 결제정보</span>
                        <VIcon size="24" :icon=hand_pay_info.status_icon :color=hand_pay_info.status_color
                            style="float: inline-end;" />
                    </div>
                </template>
                <VDivider />
                <VForm ref="vForm">
                    <CreateHalfVCol :mdl="4" :mdr="8">
                        <template #name>상품명</template>
                        <template #input>
                            <VTextField v-model="hand_pay_info.item_name" type="text"
                                prepend-inner-icon="streamline:shopping-bag-hand-bag-2-shopping-bag-purse-goods-item-products"
                                maxlength="100" :rules="[requiredValidator]" placeholder="상품명을 입력해주세요" />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="4" :mdr="8">
                        <template #name>구매자명</template>
                        <template #input>
                            <VTextField v-model="hand_pay_info.buyer_name" type="text" placeholder="구매자명을 입력해주세요"
                                :rules="[requiredValidator]" prepend-inner-icon="tabler-user" />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="4" :mdr="8">
                        <template #name>휴대폰번호</template>
                        <template #input>
                            <VTextField v-model="hand_pay_info.buyer_phone" type="number"
                                prepend-inner-icon="tabler-device-mobile" placeholder="휴대폰번호를 입력해주세요"
                                :rules="[requiredValidator]" />
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="4" :mdr="8">
                        <template #name>총 결제금액</template>
                        <template #input>
                            <b>{{ hand_pay_infos.reduce((sum, obj) => sum + Number(obj.amount), 0).toLocaleString() }}</b>원
                        </template>
                    </CreateHalfVCol>
                </VForm>
            </AppCardActions>
        </template>
        <template #input>
            <MultipleHandPayForm v-for="(item, index) in hand_pay_infos" :key="index" :hand_pay_info="hand_pay_infos[index]"
                :pay_module="props.pay_modules[index]" style="margin-bottom: 1em;" />
        </template>
    </CreateHalfVCol>
    <VCard style="margin-bottom: 1em;" v-if="sale_slips.length > 0">
        <VCardText>
            <VRow no-gutters style="text-align: center;" v-for="(_item, _index) in  full_processes " :key="_index">
                <VCol md="6" v-if="_item.trx_process.state === 'pending'">
                    <VIcon size="24" icon="svg-spinners:bars-fade" />
                    <br>
                    <br>
                    <span>결제중 입니다...</span>
                </VCol>
                <VCol md="6" v-else>
                    <VIcon size="24"
                        :icon="_item.trx_result.result_cd == '0000' ? 'line-md:check-all' : 'line-md:emoji-frown-twotone'"
                        :color="_item.trx_result.result_cd == '0000' ? 'success' : 'error'" />
                    <br>
                    <br>
                    <span>{{ _item.trx_result.result_msg }}</span>
                    <br>
                    <br>
                    <template v-if="_item.trx_result.result_cd == '0000'">
                        <VBtn @click="salesslip.show(_item.trx_result)">승인 영수증 확인</VBtn>
                    </template>
                </VCol>
                <template v-if="_item.cxl_process != null">
                    <VCol md="6" v-if="_item.cxl_process.state === 'pending'">
                        <VIcon size="24" icon="svg-spinners:bars-fade" />
                        <br>
                        <br>
                        <span>취소중 입니다...</span>
                    </VCol>
                    <VCol md="6" v-else>
                        <VIcon size="24"
                            :icon="_item.cxl_result.result_cd == '0000' ? 'line-md:check-all' : 'line-md:emoji-frown-twotone'"
                            :color="_item.cxl_result.result_cd == '0000' ? 'success' : 'error'" />
                        <br>
                        <br>
                        <span>{{ _item.cxl_result.result_msg }}</span>
                        <br>
                        <br>
                        <template v-if="_item.cxl_result.result_cd == '0000'">
                            <VBtn @click="salesslip.show(_item.cxl_result)">취소 영수증 확인</VBtn>
                        </template>
                    </VCol>
                </template>
            </VRow>
        </VCardText>
    </VCard>
    <VCard>
        <VCardText>
            <MobileVerification
                v-if="corp.pv_options.paid.use_pay_verification_mobile && props.merchandise.use_pay_verification_mobile"
                @update:pay_button="is_show_pay_button = $event" :phone_num="hand_pay_info.buyer_phone" />
            <VCol cols="12" style="padding: 0;" v-if="is_show_pay_button">
                <VBtn block @click="pays()">
                    결제하기
                </VBtn>
            </VCol>
        </VCardText>
    </VCard>
</template>
<style>
@media screen and (min-width: 960px) {
  #common-field {
    margin-inline-end: 1em;
  }
}

:deep(.v-card-item) {
  padding: 18px !important;
}
</style>
