<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { abnormal_trans_limits } from '@/views/merchandises/pay-modules/useStore'
import { banks } from '@/views/users/useStore'
import type { Salesforce } from '@/views/types'
import { axios, getLevelByIndex } from '@axios'
import corp from '@corp'

interface Props {
    item: Salesforce,
}
const props = defineProps<Props>()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const { pgs, cus_filters } = useStore()

const merchandise = reactive<any>({
    custom_filter_id: null,
    custom_id: null,
    sales_fee: 0,
    mcht_fee: 0,
    hold_fee: 0,
    acct_num: "",
    acct_name: "",
    bank: { code: null, title: '선택안함' },
    enabled: true,
})
const pay_module = reactive<any>({
    pg_id: null,
    abnormal_trans_limit: 0,
    pay_dupe_limit: 0,
    pay_dupe_least: 0,
    pay_disable_s_tm: 0,
    pay_disable_e_tm: 0,

    pay_mid: 0,
    pay_tid: 0,
    api_key: 0,
    sub_key: 0,

    pay_single_limit: 0,
    pay_day_limit: 0,
    pay_month_limit: 0,
    pay_year_limit: 0,

    show_pay_view: 0,
})

const noti = reactive<any>({
    noti_url: "",
    noti_note: "",
    noti_status: true,
})

const post = async (page: string, params: any) => {
    try {
        if (await alert.value.show('정말 일괄적용하시겠습니까?')) {
            const r = await axios.post('/api/v1/manager/salesforces/batch/' + page, params)
            snackbar.value.show('성공하였습니다.', 'success')
        }
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
}
const common = computed(() => {
    return {
        'id': props.item.id,
        'level': props.item.level,
        'pg_id': pay_module.pg_id,
        'custom_filter_id': merchandise.custom_filter_id,
    }
})
const setSalesFee = () => {
    post('sales-fee-direct-apply', {
        ...common.value,
        'sales_fee': parseFloat(merchandise.sales_fee),
    })
}
const setSalesFeeBooking = async () => {
    if (await alert.value.show('정말 예약적용하시겠습니까? <b>명일 00시</b>에 반영됩니다.<br><br><h5>실수로 적용된 예약적용 수수료는 "수수료율 변경이력" 탭에서 삭제시 반영되지 않습니다.</h5>')) {
        const r = await axios.post('/api/v1/manager/salesforces/batch/sales-fee-book-apply', {
            ...common.value,
            'sales_fee': parseFloat(merchandise.sales_fee),
        })
        snackbar.value.show('성공하였습니다.', 'success')
    }
}

const setMchtFee = () => {
    post('mcht-fee-direct-apply', {
        ...common.value,
        'mcht_fee': parseFloat(merchandise.mcht_fee),
        'hold_fee': parseFloat(merchandise.hold_fee),
    })
}

const setMchtFeeBooking = async () => {
    if (await alert.value.show('정말 예약적용하시겠습니까? <b>명일 00시</b>에 반영됩니다.<br><br><h5>실수로 적용된 예약적용 수수료는 "수수료율 변경이력" 탭에서 삭제시 반영되지 않습니다.</h5>')) {
        const r = await axios.post('/api/v1/manager/salesforces/batch/mcht-fee-book-apply', {
            ...common.value,
            'mcht_fee': parseFloat(merchandise.mcht_fee),
            'hold_fee': parseFloat(merchandise.hold_fee),
        })
        snackbar.value.show('성공하였습니다.', 'success')
    }
}

const setEnabled = () => {
    post('set-enabled', {
        ...common.value,
        'enabled': Number(merchandise.enabled),
    })
}

const setCustomFilter = () => {
    post('set-custom-filter', {
        ...common.value,
        'custom_id': merchandise.custom_id,
    })
}

const setAccountInfo = () => {
    post('set-account-info', {
        ...common.value,
        'acct_num': merchandise.acct_num,
        'acct_name': merchandise.acct_name,
        'acct_bank_code': merchandise.bank.code,
        'acct_bank_name': merchandise.bank.title,
    })
}
const setAbnormalTransLimit = () => {
    post('set-abnormal-trans-limit', {
        ...common.value,
        'abnormal_trans_limit': pay_module.abnormal_trans_limit,
    })
}
const setDupPayCountValidation = () => {
    post('set-dupe-pay-count-validation', {
        ...common.value,
        'pay_dupe_limit': pay_module.pay_dupe_limit,
    })
}
const setDupPayLeastValidation = () => {
    post('set-dupe-pay-least-validation', {
        ...common.value,
        'pay_dupe_least': pay_module.pay_dupe_least,
    })

}
const setPayLimit = (type: string) => {
    post('set-pay-limit', {
        ...common.value,
        'pay_single_limit': pay_module.pay_single_limit,
        'pay_day_limit': pay_module.pay_day_limit,
        'pay_month_limit': pay_module.pay_month_limit,
        'pay_year_limit': pay_module.pay_year_limit,
        'type': type,
    })
}
const setForbiddenPayTime = () => {
    post('set-pay-disable-time', {
        ...common.value,
        'pay_disable_s_tm': pay_module.pay_disable_s_tm,
        'pay_disable_e_tm': pay_module.pay_disable_e_tm,
    })
}
const setShowPayView = () => {
    post('set-show-pay-view', {
        ...common.value,
        'show_pay_view': pay_module.show_pay_view,
    })
}
//
const setMid = () => {
    post('set-mid', {
        ...common.value,
        'mid': pay_module.pay_mid,
    })
}
const setTid = () => {
    post('set-tid', {
        ...common.value,
        'tid': pay_module.pay_tid,
    })
}
const setApiKey = () => {
    post('set-api-key', {
        ...common.value,
        'api_key': pay_module.api_key,
    })
}
const setSubKey = () => {
    post('set-sub-key', {
        ...common.value,
        'sub_key': pay_module.sub_key,
    })
}
const setNotiUrl = () => {
    post('set-noti-url', {
        ...common.value,
        'noti_status': noti.noti_status,
        'send_url': noti.noti_url,
        'note': noti.noti_note,
    })
}
</script>
<template>
    <VCardTitle style="margin: 1em 0;">
        <BaseQuestionTooltip :location="'top'" :text="'하위 가맹점 일괄적용'" :content="'해당 영업점이 포함되어있는 가맹점에 모두 적용됩니다.'">
        </BaseQuestionTooltip>
    </VCardTitle>
    <div v-if="props.item.id != 0" style="width: 100%;">
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>
                <BaseQuestionTooltip :location="'top'" :text="'커스텀 필터 적용'"
                    :content="'해당 값을 선택한후 즉시적용을 클릭하면<br>해당 값과 가맹점의 커스텀 필터가 똑같은 가맹점만 일괄적용됩니다.'">
                </BaseQuestionTooltip>
            </template>
            <template #input>
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="merchandise.custom_filter_id"
                    :items="[{ id: null, type: 1, name: '사용안함' }].concat(cus_filters)"
                    prepend-inner-icon="tabler:folder-question" label="커스텀 필터" item-title="name" item-value="id"
                    single-line />
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>
                {{ corp.pv_options.auth.levels['sales' + getLevelByIndex(props.item.level) + '_name'] }} 수수료율</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="merchandise.sales_fee" type="number" suffix="%" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setSalesFee()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                    <VBtn variant="tonal" color="secondary" @click="setSalesFeeBooking()" style='margin-left: 0.5em;'>
                        예약적용
                        <VIcon end icon="tabler-clock-up" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>거래/유보금 수수료율</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="merchandise.mcht_fee" type="number" suffix="%" />
                    <VTextField v-model="merchandise.hold_fee" type="number" suffix="%" style='margin-left: 0.5em;' />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setMchtFee()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                    <VBtn variant="tonal" color="secondary" @click="setMchtFeeBooking()" style='margin-left: 0.5em;'>
                        예약적용
                        <VIcon end icon="tabler-clock-up" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>커스텀 필터</template>
            <template #input>
                <div class="batch-container">
                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="merchandise.custom_id"
                        :items="[{ id: null, type: 1, name: '사용안함' }].concat(cus_filters)"
                        prepend-inner-icon="tabler:folder-question" label="커스텀 필터" item-title="name" item-value="id"
                        single-line />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setCustomFilter()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.subsidiary_use_control">
            <template #name>전산 사용상태</template>
            <template #input>
                <div class="batch-container">
                    <BooleanRadio :radio="merchandise.enabled" @update:radio="merchandise.enabled = $event">
                        <template #true>ON</template>
                        <template #false>OFF</template>
                    </BooleanRadio>
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setEnabled()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>계좌 정보</template>
            <template #input>
                <div class="batch-container">
                    <VCol style="padding: 0;">
                        <VTextField v-model="merchandise.acct_num" prepend-inner-icon="ri-bank-card-fill"
                            placeholder="계좌번호 입력" persistent-placeholder style="margin-bottom: 0.5em;" />
                        <VTextField v-model="merchandise.acct_name" prepend-inner-icon="tabler-user" placeholder="예금주 입력"
                            persistent-placeholder style="margin-bottom: 0.5em;" />
                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="merchandise.bank"
                            :items="[{ code: null, title: '선택안함' }].concat(banks)" prepend-inner-icon="ph-buildings"
                            label="은행 선택"
                            :hint="`${merchandise.bank.title}, 은행 코드: ${merchandise.bank.code ? merchandise.bank.code : '000'} `"
                            item-title="title" item-value="code" persistent-hint return-object single-line create
                            style="margin-bottom: 0.5em;" />
                        <div style="text-align: end;">
                            <VBtn variant="tonal" @click="setAccountInfo()">
                                계좌 정보 즉시적용
                                <VIcon end icon="tabler-direction-sign" />
                            </VBtn>
                        </div>
                    </VCol>
                </div>
            </template>
        </CreateHalfVCol>
    </div>
    <div v-else style="width: 100%; text-align: center;">
        <CreateHalfVCol :mdl="0" :mdr="12">
            <template #name></template>
            <template #input>
                영업점을 추가하신 후 사용 가능합니다.
            </template>
        </CreateHalfVCol>
    </div>
    <VCardTitle style="margin: 1em 0;">
        <BaseQuestionTooltip :location="'top'" :text="'하위 가맹점 - 결제모듈 일괄적용'"
            :content="'해당 영업점이 포함되어있는 가맹점의 모든 결제모듈에 모두 적용됩니다.'">
        </BaseQuestionTooltip>
    </VCardTitle>
    <div v-if="props.item.id != 0" style="width: 100%;">
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>
                <BaseQuestionTooltip :location="'top'" :text="'PG사 필터 적용'"
                    :content="'해당 값을 선택한후 즉시적용을 클릭하면<br>해당 값과 결제모듈의 PG사가 똑같은 결제모듈만 일괄적용됩니다.'">
                </BaseQuestionTooltip>
            </template>
            <template #input>
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="pay_module.pg_id"
                    :items="[{ id: null, pg_name: '전체' }].concat(pgs)" prepend-inner-icon="ph-buildings" label="PG사 선택"
                    item-title="pg_name" item-value="id" single-line />
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>이상거래 한도</template>
            <template #input>
                <div class="batch-container">
                    <VSelect v-model="pay_module.abnormal_trans_limit" :items="abnormal_trans_limits"
                        prepend-inner-icon="jam-triangle-danger" label="이상거래 한도설정" item-title="title" item-value="id" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setAbnormalTransLimit()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>중복거래 하한금</template>
            <template #input>
                <div class="batch-container">
                    <VTextField type="number" v-model="pay_module.pay_dupe_least" prepend-inner-icon="tabler-currency-won"
                        suffix="만원" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setDupPayLeastValidation()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_dup_pay_validation">
            <template #name>중복결제 허용회수</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="pay_module.pay_dupe_limit" label="중복결제 허용회수" type="number" suffix="회 허용" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setDupPayCountValidation()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_pay_limit">
            <template #name>단건 결제 한도</template>
            <template #input>
                <div class="batch-container">
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="pay_module.pay_single_limit" type="number"
                        suffix="만원" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setPayLimit('single')">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_pay_limit">
            <template #name>일 결제 한도</template>
            <template #input>
                <div class="batch-container">
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="pay_module.pay_day_limit" type="number"
                        suffix="만원" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setPayLimit('day')">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>

            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_pay_limit">
            <template #name>월 결제 한도</template>
            <template #input>
                <div class="batch-container">
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="pay_module.pay_month_limit" type="number"
                        suffix="만원" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setPayLimit('month')">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>

            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_pay_limit">
            <template #name>연 결제 한도</template>
            <template #input>
                <div class="batch-container">
                    <VTextField prepend-inner-icon="tabler-currency-won" v-model="pay_module.pay_year_limit" type="number"
                        suffix="만원" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setPayLimit('year')">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_forb_pay_time">
            <template #name>결제금지 시간</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="pay_module.pay_disable_s_tm" type="time" />
                    <span class="text-center mx-auto">~</span>
                    <VTextField v-model="pay_module.pay_disable_e_tm" type="time" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setForbiddenPayTime()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9">
            <template #name>결제창 노출여부</template>
            <template #input>
                <div class="batch-container">
                    <BooleanRadio :radio="pay_module.show_pay_view" @update:radio="pay_module.show_pay_view = $event">
                        <template #true>노출</template>
                        <template #false>숨김</template>
                    </BooleanRadio>
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setShowPayView()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_mid_batch">
            <template #name>MID</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="pay_module.pay_mid" label="MID" type="text" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setMid()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_tid_batch">
            <template #name>TID</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="pay_module.pay_tid" label="TID" type="text" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setTid()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_api_key_batch">
            <template #name>API KEY(license)</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="pay_module.api_key" label="API KEY" type="text" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setApiKey()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
        <CreateHalfVCol :mdl="3" :mdr="9" v-if="corp.pv_options.paid.use_sub_key_batch">
            <template #name>SUB KEY(iv)</template>
            <template #input>
                <div class="batch-container">
                    <VTextField v-model="pay_module.sub_key" label="SUB KEY" type="text" />
                    <VBtn style='margin-left: 0.5em;' variant="tonal" @click="setSubKey()">
                        즉시적용
                        <VIcon end icon="tabler-direction-sign" />
                    </VBtn>
                </div>
            </template>
        </CreateHalfVCol>
    </div>
    <div v-else style="width: 100%; text-align: center;">
        <CreateHalfVCol :mdl="0" :mdr="12">
            <template #name></template>
            <template #input>
                영업점을 추가하신 후 사용 가능합니다.
            </template>
        </CreateHalfVCol>
    </div>
    <template v-if="corp.pv_options.paid.use_noti">
        <VCardTitle style="margin: 1em 0;">
            <BaseQuestionTooltip :location="'top'" :text="'하위 가맹점 - 노티 URL 일괄적용'"
                :content="'해당 영업점이 포함되어있는 가맹점의 모든 노티 URL이 추가됩니다.<br>(같은 노티 URL의 중복등록은 불가능합니다.)'">
            </BaseQuestionTooltip>
        </VCardTitle>
        <div v-if="props.item.id != 0" style="width: 100%;">
            <CreateHalfVCol :mdl="3" :mdr="9">
                <template #name>노티 URL</template>
                <template #input>
                    <div class="batch-container">
                        <VTextField v-model="noti.noti_url" type="text" placeholder="https://www.naver.com" />
                    </div>
                </template>
            </CreateHalfVCol>
            <CreateHalfVCol :mdl="3" :mdr="9">
                <template #name>노티 사용 유무</template>
                <template #input>
                    <VSwitch hide-details v-model="noti.noti_status" color="primary" />
                </template>
            </CreateHalfVCol>
            <VRow>
                <VCol>
                    <VTextarea v-model="noti.noti_note" counter label="메모사항" prepend-inner-icon="twemoji-spiral-notepad"
                        maxlength="95" />
                </VCol>
            </VRow>
            <div style="text-align: end;">
                <VBtn variant="tonal" @click="setNotiUrl()">
                    노티 정보 즉시적용
                    <VIcon end icon="tabler-direction-sign" />
                </VBtn>
            </div>
        </div>
        <div v-else style="width: 100%; text-align: center;">
            <CreateHalfVCol :mdl="0" :mdr="12">
                <template #name></template>
                <template #input>
                    영업점을 추가하신 후 사용 가능합니다.
                </template>
            </CreateHalfVCol>
        </div>
    </template>
</template>
<style>
.batch-container {
  display: flex;
  align-items: center;
}
</style>
