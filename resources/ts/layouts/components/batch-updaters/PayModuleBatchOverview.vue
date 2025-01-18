

<script lang="ts" setup>
import { batch } from '@/layouts/components/batch-updaters/batch'
import FeeBookDialog from '@/layouts/dialogs/users/FeeBookDialog.vue'
import PasswordAuthDialog from '@/layouts/dialogs/users/PasswordAuthDialog.vue'
import CheckAgreeDialog from '@/layouts/dialogs/utils/CheckAgreeDialog.vue'
import { abnormal_trans_limits, fin_trx_delays, installments, pay_window_extend_hours, pay_window_secure_levels } from '@/views/merchandises/pay-modules/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { getUserLevel } from '@axios'
import corp from '@corp'

interface Props {
    selected_idxs: number[],
    selected_sales_id: number,
    selected_level: number,
}
const props = defineProps<Props>() 
const emits = defineEmits(['update:select_idxs'])
const {
        selected_idxs,
        selected_sales_id,
        selected_level,
        selected_all,
        feeBookDialog,
        checkAgreeDialog,
        passwordAuthDialog,
        post,
        batchRemove
    } = batch(emits, '결제모듈', 'merchandises/pay-modules')

const store = <any>(inject('store'))
const snackbar = <any>(inject('snackbar'))

const is_realtime_deposit_use = [{id:0, title:'미사용'}, {id:1, title:'사용'}]

const { pgs, pss, settle_types, finance_vans, psFilter, setFee } = useStore()
const pay_module = reactive<any>({
    abnormal_trans_limit: 0,
    pay_dupe_limit: 0,
    pay_dupe_least: 0,
    pay_disable_s_tm: 0,
    pay_disable_e_tm: 0,

    settle_type: 0,
    settle_fee: 0,
    mid: '',
    tid: '',
    api_key: '',
    sub_key: '',
    installment: 0,
    pay_single_limit: 0,
    pay_day_limit: 0,
    pay_month_limit: 0,
    pay_year_limit: 0,
    payment_term_min: 1,
    fin_id: null,
    use_realtime_deposit: 0,
    fin_trx_delay: 0,
    note: '',
    pg_id: null,
    ps_id: null,
    pay_window_secure_level: 1,
    pay_window_extend_hour: 1,
})

const setPaymentGateway = (apply_type: number) => {
    if(pay_module.pg_id && pay_module.ps_id) {
        post('set-payment-gateway', {
            'pg_id': pay_module.pg_id,
            'ps_id': pay_module.ps_id,
        }, apply_type)
    }
    else
        snackbar.value.show('PG사 또는 구간을 선택해주세요.', 'warning')
}

const setAbnormalTransLimit = (apply_type: number) => {
    post('set-abnormal-trans-limit', {
        'abnormal_trans_limit': pay_module.abnormal_trans_limit,
    }, apply_type)
}
const setDupPayCountValidation = (apply_type: number) => {
    post('set-dupe-pay-count-validation', {
        'pay_dupe_limit': pay_module.pay_dupe_limit,
    }, apply_type)
}
const setDupPayLeastValidation = (apply_type: number) => {
    post('set-dupe-pay-least-validation', {
        'pay_dupe_least': pay_module.pay_dupe_least,
    }, apply_type)

}
const setPayLimit = (type: string, apply_type: number) => {
    post('set-pay-limit', {
        'pay_single_limit': pay_module.pay_single_limit,
        'pay_day_limit': pay_module.pay_day_limit,
        'pay_month_limit': pay_module.pay_month_limit,
        'pay_year_limit': pay_module.pay_year_limit,
        'type': type,
    }, apply_type)
}
const setForbiddenPayTime = (apply_type: number) => {
    post('set-pay-disable-time', {
        'pay_disable_s_tm': pay_module.pay_disable_s_tm,
        'pay_disable_e_tm': pay_module.pay_disable_e_tm,
    }, apply_type)
}

const setUseRealtimeDeposit = (apply_type: number) => {
    post('set-use-realtime-deposit', {
        'use_realtime_deposit': pay_module.use_realtime_deposit,
    }, apply_type)
}

const setFinId = (apply_type: number) => {
    post('set-fin-id', {
        'fin_id': pay_module.fin_id,
    }, apply_type)
}

const setFinTrxDelay = (apply_type: number) => {
    post('set-fin-trx-delay', {
        'fin_trx_delay': pay_module.fin_trx_delay,
    }, apply_type)
}

const setSettleType = (apply_type: number) => {
    post('set-settle-type', {
        'settle_type': pay_module.settle_type,
    }, apply_type)
}

const setSettleFee = (apply_type: number) => {
    post('set-settle-fee', {
        'settle_fee': pay_module.settle_fee,
    }, apply_type)
}

const setMid = (apply_type: number) => {
    post('set-mid', {
        'mid': pay_module.mid,
    }, apply_type)
}
const setTid = (apply_type: number) => {
    post('set-tid', {
        'tid': pay_module.tid,
    }, apply_type)
}

const setApiKey = (apply_type: number) => {
    post('set-api-key', {
        'api_key': pay_module.api_key,
    }, apply_type)
}
const setSubKey = (apply_type: number) => {
    post('set-sub-key', {
        'sub_key': pay_module.sub_key,
    }, apply_type)
}
const setInstallment = (apply_type: number) => {
    post('set-installment', {
        'installment': pay_module.installment,
    }, apply_type)
}

const setNote = (apply_type: number) => {
    post('set-note', {
        'note': pay_module.note,
    }, apply_type)
}

const setPaymentTermMin = (apply_type: number) => {
    post('set-payment-term-min', {
        'payment_term_min': pay_module.payment_term_min,
    }, apply_type)
}

const setPayWindowSecureLevel = (apply_type: number) => {
    post('set-pay-window-secure-level', {
        'pay_window_secure_level': pay_module.pay_window_secure_level,
    }, apply_type)
}

const setPayWindowExtendHour = (apply_type: number) => {
    post('set-pay-window-extend-hour', {
        'pay_window_extend_hour': pay_module.pay_window_extend_hour,
    }, apply_type)
}

const filterPgs = computed(() => {
    const filter = pss.filter(item => { return item.pg_id == pay_module.pg_id })
    pay_module.ps_id = psFilter(filter, pay_module.ps_id)
    return filter
})

watchEffect(() => {
    selected_idxs.value = props.selected_idxs
    selected_sales_id.value = props.selected_sales_id
    selected_level.value = props.selected_level
})

</script>
<template>
    <VCard title="결제모듈 일괄작업" style="max-height: 55em !important;overflow-y: auto !important;">
        <VCardText>
            <template v-if="props.selected_sales_id === 0 && props.selected_level === 0">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <VRadioGroup v-model="selected_all">
                        <VRadio :value="0">
                            <template #label>
                                <b>선택된 결제모듈 : {{ props.selected_idxs.length.toLocaleString() }}개</b>
                            </template>
                        </VRadio>
                        <VRadio :value="1" v-if="getUserLevel() === 40">
                            <template #label>
                                <b>전체모듈: {{ store.pagenation.total_count }}개</b>
                            </template>
                        </VRadio>
                    </VRadioGroup>
                    <VBtn type="button" color="error" @click="batchRemove()" style="float: inline-end;" size="small">
                        일괄삭제
                        <VIcon size="18" icon="tabler-trash" />
                    </VBtn>
                </div>
                <VDivider style="margin: 0.5em 0;" />
            </template>
            <div style="width: 100%;">             
                <h4 class="pt-3">PG사 정보 일괄변경</h4>
                <br>
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <div class="batch-container">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.pg_id" :items="pgs"
                                         label="PG사 선택" item-title="pg_name" item-value="id"
                                        style="max-width: 9em;margin-right: 0.25em;"/>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.ps_id" :items="filterPgs"
                                         label="구간 선택" item-title="name"
                                        item-value="id" :hint="`${setFee(pss, pay_module.ps_id)}`" persistent-hint
                                        style="max-width: 9em;margin-left: 0.25em;"/>
                                </div>
                            </VCol>
                            <VCol md="6" cols="12">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setPaymentGateway(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setPaymentGateway(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>

                <VDivider style="margin: 1em 0;" />
                <h4 class="pt-3">FDS 설정정보 일괄변경</h4>
                <br>
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VSelect v-model="pay_module.abnormal_trans_limit" :items="abnormal_trans_limits"
                                    prepend-inner-icon="jam-triangle-danger" item-title="title"
                                    item-value="id" label="이상거래 한도"/>
                            </VCol>
                            <VCol md="6">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setAbnormalTransLimit(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setAbnormalTransLimit(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>                 
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md=6>
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VTextField type="number" v-model="pay_module.pay_dupe_least"
                                    prepend-inner-icon="tabler-currency-won" suffix="만원" label="중복거래 하한금"/>
                            </VCol>
                            <VCol md="6">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setDupPayLeastValidation(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setDupPayLeastValidation(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>                 
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VTextField v-model="pay_module.pay_dupe_limit" type="number"
                                        suffix="회 허용" label="동일카드 결제허용 회수"/>
                            </VCol>
                            <VCol md="6">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setDupPayCountValidation(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setDupPayCountValidation(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>                 
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md=6>
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.installment"
                                        :items="installments" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                        label="할부한도 선택" item-title="title" item-value="id" />
                            </VCol>
                            <VCol md="6">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setInstallment(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setInstallment(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>
                                </div>
                            </VCol>                            
                        </VRow>
                    </VCol>
                </VRow>
                <VDivider style="margin: 1em 0;" />
                <h4 class="pt-3">제한정보 일괄변경</h4>
                <br>
                <template v-if="corp.pv_options.paid.use_pay_limit">
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="pay_module.pay_single_limit"
                                            type="number" suffix="만원" label="단건 결제 한도"/>
                                </VCol>
                                <VCol md="6">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setPayLimit('single', 0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setPayLimit('single', 1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>                 
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md=6>
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="pay_module.pay_day_limit"
                                            type="number" suffix="만원" label="일 결제 한도"/>
                                    </VCol>
                                <VCol md="6">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setPayLimit('day', 0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setPayLimit('day', 1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>                 
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VTextField prepend-inner-icon="tabler-currency-won"
                                            v-model="pay_module.pay_month_limit" type="number" suffix="만원" label="월 결제 한도"/>
                                </VCol>
                                <VCol md="6">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setPayLimit('month', 0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setPayLimit('month', 1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>                 
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md=6>
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="pay_module.pay_year_limit"
                                    type="number" suffix="만원" label="연 결제 한도"/>
                                    </VCol>
                                <VCol md="6">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setPayLimit('year', 0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setPayLimit('year', 1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>                 
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </template>
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VTextField prepend-inner-icon="material-symbols:shutter-speed-minus" v-model="pay_module.payment_term_min"
                                        type="number" suffix="분" label="결제 허용 간격"/>
                            </VCol>
                            <VCol md="6">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setPaymentTermMin(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setPaymentTermMin(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>                 
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md="6" :cols="12" v-if="corp.pv_options.paid.use_forb_pay_time">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <div class="batch-container">
                                    <VTextField type="time" 
                                        v-model="pay_module.pay_disable_s_tm" label="결제금지 시작시간"
                                        style="min-width: 8em;"
                                    />
                                    <span style="margin: 0 0.25em;">-</span>
                                    <VTextField type="time" 
                                        v-model="pay_module.pay_disable_e_tm" label="결제금지 종료시간"
                                        style="min-width: 8em;"
                                    />
                                </div>
                            </VCol>
                            <VCol md="6" cols="12">                                    
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setForbiddenPayTime(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setForbiddenPayTime(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>                 
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>


                <VDivider style="margin: 1em 0;" />
                <h4 class="pt-3">결제정보 일괄변경</h4>
                <br>
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.settle_type" 
                                :items="settle_types" label="정산일"
                                prepend-inner-icon="ic-outline-send-to-mobile" item-title="name" item-value="id"/>
                            </VCol>
                            <VCol md="6">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setSettleType(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setSettleType(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>                 
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md=6>
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VTextField v-model="pay_module.settle_fee" label="이체 수수료" type="number" suffix="₩" />
                            </VCol>
                            <VCol md="6">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setSettleFee(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setSettleFee(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>                 
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VTextField v-model="pay_module.mid" label="MID" />
                            </VCol>
                            <VCol md="6">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setMid(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setMid(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>                 
                                </div>

                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md=6>
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VTextField v-model="pay_module.tid" label="TID"  />
                            </VCol>
                            <VCol md="6">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setTid(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setTid(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>                 
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VTextField v-model="pay_module.api_key" label="API KEY" />
                            </VCol>
                            <VCol md="6">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setApiKey(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setApiKey(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>                 
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md=6>
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VTextField v-model="pay_module.sub_key" label="SUB KEY" type="text" />
                            </VCol>
                            <VCol md="6">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setSubKey(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setSubKey(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>                 
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VTextField v-model="pay_module.note" 
                                        prepend-inner-icon="twemoji-spiral-notepad" label="결제모듈 별칭"/>
                            </VCol>
                            <VCol md="6">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setNote(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setNote(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>                 
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VDivider style="margin: 1em 0;" />
                <h4 class="pt-3">결제창 정보 일괄변경</h4>
                <br>
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VSelect v-model="pay_module.pay_window_secure_level" :items="pay_window_secure_levels" 
                                        item-title="title" item-value="id" label="결제창 보안등급"/>
                            </VCol>
                            <VCol md="6" cols="12">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setPayWindowSecureLevel(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setPayWindowSecureLevel(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>                 
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VSelect v-model="pay_module.pay_window_extend_hour" :items="pay_window_extend_hours"
                                        item-title="title" item-value="id" label="결제창 연장시간"/>
                            </VCol>
                            <VCol md="6">
                                <div class="button-cantainer">
                                    <VBtn variant="tonal" size="small" @click="setPayWindowExtendHour(0)">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setPayWindowExtendHour(1)"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>                 
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>

                <template v-if="corp.pv_options.paid.use_realtime_deposit">
                    <VDivider style="margin: 1em 0;" />
                    <h4 class="pt-3">실시간 정보 일괄변경</h4>
                    <br>
                    <VRow>
                        <VCol :md="6" :cols="12" >
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.use_realtime_deposit"
                                            :items="is_realtime_deposit_use" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                            item-title="title" item-value="id" label="실시간 사용여부" />
                                </VCol>
                                <VCol md="6" col="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setUseRealtimeDeposit(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setUseRealtimeDeposit(1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6" :cols="12" >
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.fin_id" :items="finance_vans"
                                            prepend-inner-icon="streamline-emojis:ant" label="모듈 타입 선택" item-title="nick_name"
                                            item-value="id" />
                                </VCol>
                                <VCol md="6">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setFinId(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setFinId(1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol :md="6" :cols="12" >
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="6" cols="12">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.fin_trx_delay"
                                        prepend-inner-icon="streamline-emojis:bug" :items="fin_trx_delays" label="이체 딜레이 선택"
                                        item-title="title" item-value="id" single-line
                                    />
                                    <VTooltip activator="parent" location="top">
                                        모아서 출금을 사용하는 가맹점을 변경할경우 중복출금이 발생할 수 있습니다.<br>
                                        해당가맹점의 경우 거래중지 이후 변경해주세요.
                                    </VTooltip>
                                </VCol>
                                <VCol md="6" col="12">
                                    <div class="button-cantainer">
                                        <VBtn variant="tonal" size="small" @click="setFinTrxDelay(0)">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setFinTrxDelay(1)"
                                            style='margin-left: 0.5em;'>
                                            예약적용
                                            <VIcon end size="18" icon="tabler-clock-up" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </template>
            </div>
        </VCardText>
        <FeeBookDialog ref="feeBookDialog"/>
        <CheckAgreeDialog ref="checkAgreeDialog"/>
        <PasswordAuthDialog ref="passwordAuthDialog"/>
    </VCard>
</template>
<style scoped>
.button-cantainer {
  display: flex;
  padding: 0.25em;
  float: inline-end;
}

</style>
