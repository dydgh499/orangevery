

<script lang="ts" setup>
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import { abnormal_trans_limits } from '@/views/merchandises/pay-modules/useStore'
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

    pay_single_limit: 0,
    pay_day_limit: 0,
    pay_month_limit: 0,
    pay_year_limit: 0,

    use_realtime_deposit: 0,
    show_pay_view: 0,
    note: '',
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
const setNote = () => {
    post('set-note', {
        'note': pay_module.note,
    })
}
</script>
<template>
    <VCard title="결제모듈 일괄 작업">
        <VCardText>
            <template v-if="props.selected_sales_id === 0 && props.selected_level === 0">
                <b>선택된 결제모듈 : {{ props.selected_idxs.length.toLocaleString() }}개</b>
                <VDivider style="margin: 1em 0;" />
            </template>
            <div style="width: 100%;">
                <VRow class="pt-3">
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol>이상거래 한도</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VSelect v-model="pay_module.abnormal_trans_limit" :items="abnormal_trans_limits"
                                        prepend-inner-icon="jam-triangle-danger" label="이상거래 한도설정" item-title="title"
                                        item-value="id" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setAbnormalTransLimit()">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
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
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setDupPayLeastValidation()">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
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
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setDupPayCountValidation()">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
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
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setShowPayView()">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol :md="6" :cols="12" v-if="corp.pv_options.paid.use_pay_limit">
                        <VRow no-gutters style="align-items: center;">
                            <VCol>단건 결제 한도</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField prepend-inner-icon="tabler-currency-won"
                                        v-model="pay_module.pay_single_limit" type="number" suffix="만원" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setPayLimit('single')">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
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
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setPayLimit('day')">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
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
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setPayLimit('month')">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
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
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setPayLimit('year')">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol :md="6" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol>MID</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField v-model="pay_module.pay_mid" label="MID" type="text" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setMid()">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
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
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setTid()">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
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
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setApiKey()">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
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
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setSubKey()">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <VRow>
                    <VCol :md="12" :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="2">결제모듈 별칭</VCol>
                            <VCol md="10">
                                <div class="batch-container">
                                    <VTextField v-model="pay_module.note" placeholder='결제모듈 명칭을 적어주세요.😀'
                                        prepend-inner-icon="twemoji-spiral-notepad" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setNote()">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
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
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setUseRealtimeDeposit()">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
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
                                    <VTextField v-model="pay_module.pay_disable_s_tm" type="time" />
                                    <span class="text-center mx-auto">~</span>
                                    <VTextField v-model="pay_module.pay_disable_e_tm" type="time" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setForbiddenPayTime()">
                                        즉시적용
                                        <VIcon end icon="tabler-direction-sign" />
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
<style scoped>
.batch-container {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
