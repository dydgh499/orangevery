

<script lang="ts" setup>
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
    pay_window_secure_level: 0,
    note: '',
    pg_id: null,
    ps_id: null,
    pay_window_secure_level: 1,
    pay_window_extend_hour: 1,
})


const getCommonParams = async (params: any, method: string) => {
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

        if (await alert.value.show(`정말 일괄${method}하시겠습니까?`)) {
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
    const [result, params] = await getCommonParams({}, '삭제')
    if(result) {
        const r = await request({ url: `/api/v1/manager/merchandises/pay-modules/batch-updaters/remove`, method: 'delete', data: params }, true)
        emits('update:select_idxs', [])
    }
}

const post = async (page: string, _params: any) => {
    try {
        const [result, params] = await getCommonParams(_params, '수정')
        if(result) {
            const r = await axios.post('/api/v1/manager/merchandises/pay-modules/batch-updaters/' + page, params)
            snackbar.value.show(r.data.message, 'success')
            emits('update:select_idxs', [])
        }
    }
    catch (e: any) {
        console.log(e)
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
}

const setPaymentGateway = () => {
    if(pay_module.pg_id && pay_module.ps_id) {
        post('set-payment-gateway', {
            'pg_id': pay_module.pg_id,
            'ps_id': pay_module.ps_id,
        })
    }
    else
        snackbar.value.show('PG사 또는 구간을 선택해주세요.', 'warning')
}

const setAbnormalTransLimit = () => {
    post('set-abnormal-trans-limit', {
        'abnormal_trans_limit': pay_module.abnormal_trans_limit,
    })
}
const setDupPayCountValidation = () => {
    post('set-dupe-pay-count-validation', {
        'pay_dupe_limit': pay_module.pay_dupe_limit,
    })
}
const setDupPayLeastValidation = () => {
    post('set-dupe-pay-least-validation', {
        'pay_dupe_least': pay_module.pay_dupe_least,
    })

}
const setPayLimit = (type: string) => {
    post('set-pay-limit', {
        'pay_single_limit': pay_module.pay_single_limit,
        'pay_day_limit': pay_module.pay_day_limit,
        'pay_month_limit': pay_module.pay_month_limit,
        'pay_year_limit': pay_module.pay_year_limit,
        'type': type,
    })
}
const setForbiddenPayTime = () => {
    post('set-pay-disable-time', {
        'pay_disable_s_tm': pay_module.pay_disable_s_tm,
        'pay_disable_e_tm': pay_module.pay_disable_e_tm,
    })
}
const setShowPayView = () => {
    post('set-show-pay-view', {
        'pay_window_secure_level': pay_module.pay_window_secure_level,
    })
}
const setUseRealtimeDeposit = () => {
    post('set-use-realtime-deposit', {
        'use_realtime_deposit': pay_module.use_realtime_deposit,
    })
}

const setFinId = () => {
    post('set-fin-id', {
        'fin_id': pay_module.fin_id,
    })
}

const setMid = () => {
    post('set-mid', {
        'mid': pay_module.pay_mid,
    })
}
const setTid = () => {
    post('set-tid', {
        'tid': pay_module.pay_tid,
    })
}
const setApiKey = () => {
    post('set-api-key', {
        'api_key': pay_module.api_key,
    })
}
const setSubKey = () => {
    post('set-sub-key', {
        'sub_key': pay_module.sub_key,
    })
}
const setInstallment = () => {
    post('set-installment', {
        'installment': pay_module.installment,
    })
}

const setNote = () => {
    post('set-note', {
        'note': pay_module.note,
    })
}

const setPaymentTermMin = () => {
    post('set-payment-term-min', {
        'payment_term_min': pay_module.payment_term_min,
    })
}

const setPayWindowSecureLevel = () => {
    post('set-pay-window-secure-level', {
        'pay_window_secure_level': pay_module.pay_window_secure_level,
    })
}

const setPayWindowExtendHour = () => {
    post('set-pay-window-extend-hour', {
        'pay_window_extend_hour': pay_module.pay_window_extend_hour,
    })
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
                <VRow no-gutters style="align-items: center;" class="pt-3">
                    <VCol md="3" cols="12" style="padding: 0.25em;margin-bottom: auto !important;">PG사/구간</VCol>
                    <VCol md="3" cols="6" style="padding: 0.25em;margin-bottom: auto !important;">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.pg_id" :items="pgs"
                                prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_name" item-value="id"
                                single-line />
                    </VCol>
                    <VCol md="3" cols="6" style="padding: 0.25em;margin-bottom: auto !important;">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.ps_id" :items="filterPgs"
                                prepend-inner-icon="mdi-vector-intersection" label="구간 선택" item-title="name"
                                item-value="id" :hint="`${setFee(pss, pay_module.ps_id)}`" persistent-hint
                                single-line />
                    </VCol>
                    <VCol md="3" cols="12" style="padding: 0.25em;margin-bottom: auto !important;">
                        <div style="float: inline-end;">
                            <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setPaymentGateway()">
                                즉시적용
                                <VIcon end size="18" icon="tabler-direction-sign" />
                            </VBtn>
                        </div>
                    </VCol>
                </VRow>
                
                <template v-if="corp.pv_options.paid.use_forb_pay_time">
                    <VDivider style="margin: 1em 0;" />
                    <VRow>
                        <VCol :md="12" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="3" cols="12" style="padding: 0.25em;margin-bottom: auto !important;">결제금지 시간</VCol>
                                <VCol md="3" cols="6" style="padding: 0.25em;margin-bottom: auto !important;">
                                    <VTextField v-model="pay_module.pay_disable_s_tm" type="time" label="시작시간"/>
                                </VCol>
                                <VCol md="3" cols="6" style="padding: 0.25em;margin-bottom: auto !important;">
                                    <VTextField v-model="pay_module.pay_disable_e_tm" type="time" label="종료시간"/>
                                </VCol>
                                <VCol md="3" cols="12" style="padding: 0.25em;margin-bottom: auto !important;">
                                    <div style="float: inline-end;">
                                        <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setForbiddenPayTime()">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </template>

                <VDivider style="margin: 1em 0;" />
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">이상거래 한도</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VSelect v-model="pay_module.abnormal_trans_limit" :items="abnormal_trans_limits"
                                        prepend-inner-icon="jam-triangle-danger" item-title="title"
                                        item-value="id" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setAbnormalTransLimit()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md=6>
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">중복거래 하한금</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField type="number" v-model="pay_module.pay_dupe_least"
                                        prepend-inner-icon="tabler-currency-won" suffix="만원" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setDupPayLeastValidation()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">동일카드 결제허용 회수</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField v-model="pay_module.pay_dupe_limit" type="number"
                                        suffix="회 허용" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setDupPayCountValidation()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md=6>
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">결제모듈 별칭</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField v-model="pay_module.note" placeholder='결제모듈 명칭을 적어주세요.😀'
                                        prepend-inner-icon="twemoji-spiral-notepad" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setNote()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VDivider style="margin: 1em 0;" />
                <VRow>
                    <VCol :md="6" :cols="12" v-if="corp.pv_options.paid.use_pay_limit">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">단건 결제 한도</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField prepend-inner-icon="tabler-currency-won"
                                        v-model="pay_module.pay_single_limit" type="number" suffix="만원" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setPayLimit('single')">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md=6>
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">일 결제 한도</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="pay_module.pay_day_limit"
                                        type="number" suffix="만원" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setPayLimit('day')">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol :md="6" :cols="12" v-if="corp.pv_options.paid.use_pay_limit">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">월 결제 한도</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField prepend-inner-icon="tabler-currency-won"
                                        v-model="pay_module.pay_month_limit" type="number" suffix="만원" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setPayLimit('month')">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md=6>
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">연 결제 한도</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="pay_module.pay_year_limit"
                                        type="number" suffix="만원" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setPayLimit('year')">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VDivider style="margin: 1em 0;" />
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">MID</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField v-model="pay_module.pay_mid" label="MID" type="text" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setMid()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md=6>
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">TID</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField v-model="pay_module.pay_tid" label="TID" type="text" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setTid()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">API KEY</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField v-model="pay_module.api_key" label="API KEY" type="text" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setApiKey()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md=6>
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">SUB KEY</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField v-model="pay_module.sub_key" label="SUB KEY" type="text" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setSubKey()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">할부개월</VCol>
                            <VCol md="8">
                                <div class="batch-container">                                    
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.installment"
                                        :items="installments" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                        label="할부한도 선택" item-title="title" item-value="id" single-line />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setInstallment()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">
                                결제 허용 간격
                            </VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField prepend-inner-icon="material-symbols:shutter-speed-minus" v-model="pay_module.payment_term_min"
                                        type="number" suffix="분"/>
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setPaymentTermMin()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">
                                결제창 보안등급
                            </VCol>
                            <VCol md="8" cols="12">
                                <div class="batch-container">
                                    <VSelect v-model="pay_module.pay_window_secure_level" :items="pay_window_secure_levels" 
                                        item-title="title" item-value="id" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setPayWindowSecureLevel()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="4" cols="12">
                                결제창 연장시간
                            </VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VSelect v-model="pay_module.pay_window_extend_hour" :items="pay_window_extend_hours"
                                        item-title="title" item-value="id"/>
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setPayWindowExtendHour()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>

                <template v-if="corp.pv_options.paid.use_realtime_deposit">
                    <VDivider style="margin: 1em 0;" />
                    <VRow>
                        <VCol :md="6" :cols="12" >
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="4" cols="12">실시간 사용여부</VCol>
                                <VCol md="8">
                                    <div class="batch-container">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.use_realtime_deposit"
                                            :items="is_realtime_deposit_use" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                            item-title="title" item-value="id" single-line />
                                        <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setUseRealtimeDeposit()">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6" :cols="12" >
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="4" cols="12">이체 모듈 타입</VCol>
                                <VCol md="8">
                                    <div class="batch-container">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.fin_id" :items="finance_vans"
                                            prepend-inner-icon="streamline-emojis:ant" label="모듈 타입 선택" item-title="nick_name"
                                            item-value="id" single-line />
                                        <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setFinId()">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </template>
            </div>
        </VCardText>
        <CheckAgreeDialog ref="checkAgreeDialog"/>
        <PasswordAuthDialog ref="passwordAuthDialog"/>
    </VCard>
</template>
