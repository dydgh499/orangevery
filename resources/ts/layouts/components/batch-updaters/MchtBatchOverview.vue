

<script lang="ts" setup>
import FeeBookDialog from '@/layouts/dialogs/users/FeeBookDialog.vue'
import PasswordAuthDialog from '@/layouts/dialogs/users/PasswordAuthDialog.vue'
import CheckAgreeDialog from '@/layouts/dialogs/utils/CheckAgreeDialog.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { useRequestStore } from '@/views/request'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { Options } from '@/views/types'
import { banks, getOnlyNumber } from '@/views/users/useStore'
import { axios, getIndexByLevel, getLevelByIndex, getUserLevel, user_info } from '@axios'
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

const checkAgreeDialog = ref()
const passwordAuthDialog = ref()

const { cus_filters } = useStore()
const { request } = useRequestStore()
const { sales, initAllSales } = useSalesFilterStore()

const feeBookDialog = ref()

const levels = corp.pv_options.auth.levels
const show_fees = <Options[]>([{id:0, title:"숨김"}, {id:1, title:"노출"}])
const selected_all = ref(0)
const merchandise = reactive<any>({
    custom_id: null,
    trx_fee: 0,
    hold_fee: 0,
    sales5_fee: 0,
    sales4_fee: 0,
    sales3_fee: 0,
    sales2_fee: 0,
    sales1_fee: 0,
    sales0_fee: 0,

    acct_num: "",
    acct_name: "",
    bank: { code: null, title: '선택안함' },
    business_num: "",
    enabled: 1,
    is_show_fee: 0,
})
const noti = reactive<any>({
    noti_url: "",
    noti_note: "",
    noti_status: true,
})

const getCommonParams = async (params: any, method: string, type: string) => {
    if (props.selected_idxs.length || (props.selected_sales_id && props.selected_level) || selected_all.value) {
        if(selected_all.value) {
            const agree = await checkAgreeDialog.value.show(store.pagenation.total_count, method, '가맹점')
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

        if (await alert.value.show(`정말 ${type}${method}하시겠습니까?`)) {
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
        snackbar.value.show('가맹점을 1개이상 선택해주세요.', 'error')
        return [false, params]
    }
}

const batchRemove = async () => {
    const [result, params] = await getCommonParams({}, '삭제', '일괄')
    if(result) {
        const r = await request({ url: `/api/v1/manager/merchandises/batch-updaters/remove`, method: 'delete', data: params }, true)
        emits('update:select_idxs', [])
    }
}

const post = async (page: string, _params: any, type='일괄') => {
    try {
        const [result, params] = await getCommonParams(_params, '수정', type)
        if(result) {
            const r = await axios.post('/api/v1/manager/merchandises/batch-updaters/' + page, params)
            snackbar.value.show(r.data.message, 'success')
            emits('update:select_idxs', [])
        }
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
}

const getApplyDt = async (type: string) => {
    let apply_dt = ''
    if(type === 'direct-apply')
        apply_dt = formatDate(new Date)
    else 
        apply_dt = await feeBookDialog.value.show()
    return apply_dt
}

const setSalesFee = async (sales_idx: number, type: string) => {
    const fee_type = type === 'direct-apply' ? '일괄' : '예약'
    const apply_dt = await getApplyDt(type)

    if(apply_dt !== '') {
        post(`salesforces/${type}`, {
            'sales_fee': parseFloat(merchandise['sales' + sales_idx + "_fee"]),
            'sales_id': merchandise['sales' + sales_idx + "_id"],
            'level': getIndexByLevel(sales_idx),
            'apply_dt': apply_dt,
        }, fee_type)
    }
}

const setMchtFee = async (type: string) => {
    const fee_type = type === 'direct-apply' ? '일괄' : '예약'
    const apply_dt = await getApplyDt(type)

    if(apply_dt !== '') {
        post(`merchandises/${type}`, {
            'trx_fee': parseFloat(merchandise.trx_fee),
            'hold_fee': parseFloat(merchandise.hold_fee),
            'apply_dt': formatDate(new Date),
        }, fee_type)
    }
}

const setEnabled = () => {
    post('set-enabled', {
        'enabled': Number(merchandise.enabled),
    })
}

const setCustomFilter = () => {
    post('set-custom-filter', {
        'custom_id': merchandise.custom_id,
    })
}
const setBusinessNum = () => {
    post('set-business-num', {
        'business_num': merchandise.business_num,
    })
}

const setAccountInfo = () => {
    post('set-account-info', {
        'acct_num': merchandise.acct_num,
        'acct_name': merchandise.acct_name,
        'acct_bank_code': merchandise.bank.code,
        'acct_bank_name': merchandise.bank.title,
    })
}

const setIsShowFee = async () => {
    post('set-show-fee', {
        'is_show_fee': merchandise.is_show_fee,
    })
}

// -------------- noti ----------------
const setNotiUrl = () => {
    post('set-noti-url', {
        'noti_status': noti.noti_status,
        'send_url': noti.noti_url,
        'note': noti.noti_note,
    })
}

initAllSales()

watchEffect(() => {
    if(props.selected_sales_id && props.selected_level) {
        const idx = getLevelByIndex(props.selected_level)
        merchandise[`sales${idx}_id`] = props.selected_sales_id
    }
})

</script>
<template>
    <section>
        <VCard title="가맹점 일괄 작업" style="max-height: 55em !important;overflow-y: auto !important;">
            <VCardText>
                <template v-if="props.selected_sales_id === 0 && props.selected_level === 0">
                    <div style=" display: flex;align-items: center; justify-content: space-between;">
                        <VRadioGroup v-model="selected_all">
                            <VRadio :value="0">
                                <template #label>
                                    <b>선택된 가맹점 : {{ props.selected_idxs.length.toLocaleString() }}개</b>
                                </template>
                            </VRadio>
                            <VRadio :value="1" v-if="getUserLevel() === 40">
                                <template #label>
                                    <b>가맹점: {{ store.pagenation.total_count }}개</b>
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
                    <template v-for="i in 6" :key="i">
                        <VRow no-gutters style="align-items: center; margin-bottom: 0.5em;" v-if="levels['sales' + (6 - i) + '_use'] && getUserLevel() >= getIndexByLevel(6 - i)">
                            <VCol md="3" cols="12" style="padding: 0.25em;">
                                {{ levels['sales' + (6 - i) + '_name'] }}/수수료율
                            </VCol>
                            <VCol md="3" cols="6" style="padding: 0.25em;">
                                <VAutocomplete :menu-props="{ maxHeight: 400 }"
                                    v-model="merchandise['sales' + (6 - i) + '_id']" :items="sales[6 - i].value"
                                    :label="levels['sales' + (6 - i) + '_name'] + ' 선택'" item-title="sales_name"
                                    item-value="id" single-line />
                                <VTooltip activator="parent" location="top"
                                    v-if="merchandise['sales' + (6 - i) + '_id']">
                                    {{ sales[6 - i].value.find(obj => obj.id ===
                                        merchandise['sales' + (6 - i) + '_id'])?.sales_name }}
                                </VTooltip>
                            </VCol>
                            <VCol md="2" cols="6" style="padding: 0.25em;">
                                <VTextField v-model="merchandise['sales' + (6 - i) + '_fee']" type="number"
                                    suffix="%" />
                            </VCol>
                            <VCol md="4" cols="12" style="padding: 0.25em;">
                                <div style="float: inline-end;">
                                    <VBtn variant="tonal" size="small" @click="setSalesFee(6 - i, 'direct-apply')" style='margin-left: 0.5em;'>
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setSalesFee(6 - i, 'book-apply')"
                                        style='margin-left: 0.5em;'>
                                        예약적용
                                        <VIcon end size="18" icon="tabler-clock-up" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </template>
                    <VDivider style="margin: 1em 0;" />
                    <VRow no-gutters style="align-items: center;">
                        <VCol md="3" cols="12" style="padding: 0.25em;">
                            거래/유보금 수수료율
                        </VCol>
                        <VCol md="3" cols="6" style="padding: 0.25em;">
                            <VTextField v-model="merchandise.trx_fee" type="number" suffix="%"/>
                        </VCol>
                        <VCol md="2" cols="6" style="padding: 0.25em;">
                            <VTextField v-model="merchandise.hold_fee" type="number" suffix="%"/>
                        </VCol>
                        <VCol md="4" cols="12" style="padding: 0.25em;">
                            <div style="float: inline-end;">
                                <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setMchtFee('direct-apply')">
                                    즉시적용
                                    <VIcon end size="18" icon="tabler-direction-sign" />
                                </VBtn>
                                <VBtn variant="tonal" size="small" color="secondary" @click="setMchtFee('book-apply')"
                                    style='margin-left: 0.5em;'>
                                    예약적용
                                    <VIcon end size="18" icon="tabler-clock-up" />
                                </VBtn>
                            </div>
                        </VCol>
                    </VRow>
                    <template v-if="corp.pv_options.paid.use_noti">
                        <VDivider style="margin: 1em 0;" />
                        <VRow no-gutters style="align-items: center;">
                            <VCol md="3" cols="12" style="padding: 0.25em;">
                                <BaseQuestionTooltip :location="'top'" :text="'노티 URL 등록'"
                                    :content="'선택한 가맹점의 모든 노티 URL이 추가됩니다.<br>(같은 노티 URL의 중복등록은 불가능합니다.)'">
                                </BaseQuestionTooltip>
                            </VCol>
                            <VCol md="3" cols="6" style="padding: 0.25em;">
                                <VTextField v-model="noti.noti_url" type="text"
                                    placeholder="https://www.naver.com" />
                            </VCol>
                            <VCol md="2" cols="6" style="padding: 0.25em;">
                                <VTextField v-model="noti.noti_note" label="메모사항"
                                    prepend-inner-icon="twemoji-spiral-notepad" maxlength="300" />
                            </VCol>
                            <VCol md="4" cols="12" style="padding: 0.25em;">
                                <div style="float: inline-end;">
                                    <VBtn variant="tonal" size="small" @click="setNotiUrl()" style='margin-left: 0.5em;'>
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </template>
                    <VDivider style="margin: 1em 0;" />
                    <VRow no-gutters style="align-items: center;">
                        <VCol md="2" cols="12" style="padding: 0.25em;margin-bottom: auto !important;">계좌 정보</VCol>
                        <VCol md="3" cols="12" style="padding: 0.25em;margin-bottom: auto !important;">
                            <VTextField v-model="merchandise.acct_num" prepend-inner-icon="ri-bank-card-fill"
                                placeholder="계좌번호 입력" persistent-placeholder />
                        </VCol>
                        <VCol md="2" cols="6" style="padding: 0.25em;margin-bottom: auto !important;">
                            <VTextField v-model="merchandise.acct_name" prepend-inner-icon="tabler-user"
                                placeholder="예금주 입력" persistent-placeholder />
                        </VCol>
                        <VCol md="3" cols="6" style="padding: 0.25em;">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="merchandise.bank"
                                :items="[{ code: null, title: '선택안함' }].concat(banks)"
                                prepend-inner-icon="ph-buildings" label="은행 선택"
                                :hint="`${merchandise.bank.title}, 은행 코드: ${merchandise.bank.code ? merchandise.bank.code : '000'} `"
                                item-title="title" item-value="code" persistent-hint return-object single-line
                                />
                        </VCol>
                        <VCol md="2" cols="12" style="padding: 0.25em;margin-bottom: auto !important; margin-left: auto;">
                            <VBtn variant="tonal" size="small" @click="setAccountInfo()" style="float: inline-end;">
                                즉시적용
                                <VIcon end size="18" icon="tabler-direction-sign" />
                            </VBtn>
                        </VCol>
                    </VRow>
                    <VDivider style="margin: 1em 0;" />
                    <VRow>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="4" cols="12">커스텀 필터</VCol>
                                <VCol md="8">
                                    <div class="batch-container">
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="merchandise.custom_id"
                                            :items="[{ id: null, type: 1, name: '사용안함' }].concat(cus_filters)" label="커스텀 필터"
                                            item-title="name" item-value="id" single-line />
                                        <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setCustomFilter()">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="4" cols="12">사업자 번호</VCol>
                                <VCol md="8">
                                    <div class="batch-container">
                                        <VTextField v-model="merchandise.business_num" type="number" placeholder="사업자등록번호 입력"
                                            persistent-placeholder @update:model-value="merchandise.business_num = getOnlyNumber($event)"/>
                                        <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setBusinessNum()">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                    </VRow>
                    <VRow md="12" :cols=12>
                        <VCol :md="6" :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="4" cols="12">수수료율 노출</VCol>
                                <VCol md="8">
                                    <div class="batch-container">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="merchandise.is_show_fee" 
                                            :items="show_fees" item-title="title" item-value="id" single-line/>
                                        <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setIsShowFee()">
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                    </div>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol :md="6" v-if="corp.pv_options.paid.subsidiary_use_control">
                            <VRow no-gutters style="align-items: center;">
                                <VCol md="4" cols="12">전산 사용상태</VCol>
                                <VCol md="8">
                                    <div class="batch-container">
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="merchandise.enabled" 
                                            :items="[{id:0, title:'OFF'}, {id:1, title:'ON'}]" item-title="title" item-value="id" 
                                            single-line/>
                                        <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setEnabled()">
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
        <FeeBookDialog ref="feeBookDialog"/>
        <CheckAgreeDialog ref="checkAgreeDialog"/>
        <PasswordAuthDialog ref="passwordAuthDialog"/>
    </section>
</template>
