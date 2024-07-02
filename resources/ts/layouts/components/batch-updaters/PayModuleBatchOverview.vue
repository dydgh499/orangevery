

<script lang="ts" setup>
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import { abnormal_trans_limits, installments } from '@/views/merchandises/pay-modules/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { axios } from '@axios'
import corp from '@corp'

interface Props {
    selected_idxs: number[],
    selected_sales_id: number,
    selected_level: number,
}
const props = defineProps<Props>() 

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const { pgs, pss, settle_types, terminals, finance_vans, psFilter, setFee } = useStore()
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

    use_realtime_deposit: 0,
    show_pay_view: 0,
    note: '',

    pg_id: null,
    ps_id: null,
})

const post = async (page: string, params: any) => {
    try {
        if (props.selected_idxs.length || (props.selected_sales_id && props.selected_level)) {
            if (await alert.value.show('정말 일괄적용하시겠습니까?')) {
                Object.assign(params, { 
                    selected_idxs: props.selected_idxs,
                    selected_sales_id: props.selected_sales_id,
                    selected_level: props.selected_level, 
                })
                const r = await axios.post('/api/v1/manager/merchandises/pay-modules/batch-updaters/' + page, params)
                snackbar.value.show('성공하였습니다.', 'success')
            }
        }
        else
            snackbar.value.show('결제모듈은 1개이상 선택해주세요.', 'error')
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
        'show_pay_view': pay_module.show_pay_view,
    })
}
const setUseRealtimeDeposit = () => {
    post('set-use-realtime-deposit', {
        'use_realtime_deposit': pay_module.use_realtime_deposit,
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

const batchRemove = () => {
    
}

const filterPgs = computed(() => {
    const filter = pss.filter(item => { return item.pg_id == pay_module.pg_id })
    pay_module.ps_id = psFilter(filter, pay_module.ps_id)
    return filter
})

</script>
<template>
    <VCard title="결제모듈 일괄 작업">
        <VCardText>
            <template v-if="props.selected_sales_id === 0 && props.selected_level === 0">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <b>선택된 결제모듈 : {{ props.selected_idxs.length.toLocaleString() }}개</b>
                    <VBtn type="button" color="error" @click="batchRemove()" style="float: inline-end;" size="small">
                        일괄삭제
                        <VIcon size="18" icon="tabler-trash" />
                    </VBtn>
                </div>
                <VDivider style="margin: 0.5em 0;" />
            </template>
            <div style="width: 100%;">
                <VRow class="pt-3">
                    <VCol :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol>구간</VCol>
                            <VCol md="8">
                                <div class="batch-container" style="justify-content: end !important;">
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.pg_id" :items="pgs"
                                        prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_name" item-value="id"
                                        single-line style="max-width: 200px; margin-right: 0.5em;" />
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.ps_id" :items="filterPgs"
                                        prepend-inner-icon="mdi-vector-intersection" label="구간 선택" item-title="name"
                                        item-value="id" :hint="`${setFee(pss, pay_module.ps_id)}`" persistent-hint
                                        single-line style="max-width: 200px;" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setPaymentGateway()">
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
                            <VCol>이상거래 한도</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VSelect v-model="pay_module.abnormal_trans_limit" :items="abnormal_trans_limits"
                                        prepend-inner-icon="jam-triangle-danger" label="이상거래 한도설정" item-title="title"
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
                            <VCol>중복거래 하한금</VCol>
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
                            <VCol>중복결제 허용회수</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField v-model="pay_module.pay_dupe_limit" label="중복결제 허용회수" type="number"
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
                            <VCol>결제창 노출여부</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <BooleanRadio :radio="pay_module.show_pay_view"
                                        @update:radio="pay_module.show_pay_view = $event">
                                        <template #true>노출</template>
                                        <template #false>숨김</template>
                                    </BooleanRadio>
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setShowPayView()">
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
                            <VCol>단건 결제 한도</VCol>
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
                            <VCol>일 결제 한도</VCol>
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
                            <VCol>월 결제 한도</VCol>
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
                            <VCol>연 결제 한도</VCol>
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
                            <VCol>MID</VCol>
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
                            <VCol>TID</VCol>
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
                            <VCol>API KEY(license)</VCol>
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
                            <VCol>SUB KEY(iv)</VCol>
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
                            <VCol>할부개월</VCol>
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
                            <VCol>결제모듈 별칭</VCol>
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
                <VRow>
                    <VCol :md="6" :cols="12" v-if="corp.pv_options.paid.use_realtime_deposit">
                        <VRow no-gutters style="align-items: center;">
                            <VCol>실시간 사용여부</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <BooleanRadio :radio="pay_module.use_realtime_deposit"
                                        @update:radio="pay_module.use_realtime_deposit = $event">
                                        <template #true>사용</template>
                                        <template #false>미사용</template>
                                    </BooleanRadio>
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setUseRealtimeDeposit()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol :md="12" :cols="12" v-if="corp.pv_options.paid.use_forb_pay_time">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="2">결제금지 시간</VCol>
                            <VCol md="6">
                                <div class="batch-container">
                                    <VTextField v-model="pay_module.pay_disable_s_tm" type="time" style="margin-right: 0.1em;"/>
                                    <span class="text-center mx-auto">~</span>
                                    <VTextField v-model="pay_module.pay_disable_e_tm" type="time" style="margin-left: 0.1em;"/>
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setForbiddenPayTime()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
            </div>
        </VCardText>
    </VCard>
</template>
