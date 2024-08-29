

<script lang="ts" setup>
import FeeBookDialog from '@/layouts/dialogs/users/FeeBookDialog.vue'
import PasswordAuthDialog from '@/layouts/dialogs/users/PasswordAuthDialog.vue'
import CheckAgreeDialog from '@/layouts/dialogs/utils/CheckAgreeDialog.vue'
import { abnormal_trans_limits, installments, pay_window_extend_hours, pay_window_secure_levels } from '@/views/merchandises/pay-modules/useStore'
import { useRequestStore } from '@/views/request'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { axios, getUserLevel, user_info } from '@axios'
import corp from '@corp'

interface Props {
    selected_idxs: number[],
    selected_sales_id: number,
    selected_level: number,
}
const props = defineProps<Props>() 
const emits = defineEmits(['update:select_idxs'])

const store = <any>(inject('store'))
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const formatDate = <any>(inject('$formatDate'))

const feeBookDialog = ref()
const checkAgreeDialog = ref()
const passwordAuthDialog = ref()

const is_realtime_deposit_use = [{id:0, title:'미사용'}, {id:1, title:'사용'}]

const { request } = useRequestStore()
const { pgs, pss, settle_types, terminals, finance_vans, psFilter, setFee } = useStore()

const selected_all = ref(0)
const pay_module = reactive<any>({
    abnormal_trans_limit: 0,
    pay_dupe_limit: 0,
    pay_dupe_least: 0,
    pay_disable_s_tm: 0,
    pay_disable_e_tm: 0,

    pay_mid: '',
    pay_tid: '',
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
    note: '',
    pg_id: null,
    ps_id: null,
    pay_window_secure_level: 1,
    pay_window_extend_hour: 1,
})


const getCommonParams = async (params: any, method: string, type: string) => {
    if (props.selected_idxs.length || (props.selected_sales_id && props.selected_level) || selected_all.value) {
        if(selected_all.value) {
            const agree = await checkAgreeDialog.value.show(store.pagenation.total_count, method, '결제모듈')
            if (agree === false)
                return [false, params]
            else {
                if(corp.pv_options.paid.use_head_office_withdraw) {
                    let phone_num = user_info.value.phone_num
                    if(phone_num) {
                        phone_num = phone_num.replaceAll(' ', '').replaceAll('-', '')
                        const token = await passwordAuthDialog.value.show(phone_num)
                        if(token !== '') {
                            
                        }
                        else
                            return [false, params]
                    }
                    else {
                        snackbar.value.show('로그인한 계정의 휴대폰번호를 업데이트한 후 다시 시도해주세요.', 'error')
                        return [false, params]
                    }
                }
            }
        }
        let message = `정말 ${type}${method}하시겠습니까?`;
        if(params['apply_type'] === 1)
            message += `<br><b>${params['apply_dt']}${params['apply_dt'].length > 10 ? '부터' : '일 자정에'}</b> 적용될 예정입니다.`

        if (await alert.value.show(message)) {
            Object.assign(params, { 
                selected_idxs: props.selected_idxs,
                selected_sales_id: props.selected_sales_id,
                selected_level: props.selected_level, 
                selected_all: selected_all.value,
            })
            if(selected_all.value) {
                Object.assign(params, {
                    filter: store.params
                })
                params.filter.search = (document.getElementById('search') as HTMLInputElement)?.value
                params.total_selected_count = store.pagenation.total_count
            }
            return [true, params]
        }
        return [false, params]
    }
    else {
        snackbar.value.show('결제모듈을 1개이상 선택해주세요.', 'error')
        return [false, params]
    }
}

const batchRemove = async () => {
    const [result, params] = await getCommonParams({}, '삭제', '일괄')
    if(result) {
        const r = await request({ url: `/api/v1/manager/merchandises/pay-modules/batch-updaters/remove`, method: 'delete', data: params }, true)
        emits('update:select_idxs', [])
    }
}

const post = async (page: string, _params: any, apply_type: number) => {
    try {
        const apply_dt = await getApplyDt(page, apply_type)
        if(apply_dt !== '') {
            _params['apply_dt'] = apply_dt
            _params['apply_type'] = apply_type
            const [result, params] = await getCommonParams(_params, '적용', apply_type ? '예약' : '일괄')
            if(result) {
                const r = await axios.post('/api/v1/manager/merchandises/pay-modules/batch-updaters/' + page, params)
                snackbar.value.show(r.data.message, 'success')
                emits('update:select_idxs', [])
            }
        }
    }
    catch (e: any) {
        console.log(e)
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
}

const getApplyDt = async (page: string, type: number) => {
    let apply_dt = ''
    if(type === 0)
        apply_dt = formatDate(new Date)
    else 
        apply_dt = await feeBookDialog.value.show(page.includes('set-fee') ? false : true)
    return apply_dt
}


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

const setMid = (apply_type: number) => {
    post('set-mid', {
        'mid': pay_module.pay_mid,
    }, apply_type)
}
const setTid = (apply_type: number) => {
    post('set-tid', {
        'tid': pay_module.pay_tid,
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
                <h4 class="pt-3">이상거래 설정정보 일괄변경</h4>
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
                <VRow v-if="corp.pv_options.paid.use_pay_limit">
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="6" cols="12">
                                <VTextField prepend-inner-icon="tabler-currency-won" v-model="pay_module.pay_day_limit"
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
                <VRow v-if="corp.pv_options.paid.use_pay_limit">
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
                                <VTextField v-model="pay_module.pay_mid" label="MID" />
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
                                <VTextField v-model="pay_module.pay_tid" label="TID"  />
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
