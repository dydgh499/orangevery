

<script lang="ts" setup>
import FeeBookDialog from '@/layouts/dialogs/users/FeeBookDialog.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import BooleanRadio from '@/layouts/utils/BooleanRadio.vue'
import { useRequestStore } from '@/views/request'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { banks, getOnlyNumber } from '@/views/users/useStore'
import { axios, getIndexByLevel, getLevelByIndex, getUserLevel } from '@axios'
import corp from '@corp'

interface Props {
    selected_idxs: number[],
    selected_sales_id: number,
    selected_level: number,
}

const props = defineProps<Props>()
const emits = defineEmits(['update:select_idxs'])

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))
const formatDate = <any>(inject('$formatDate'))

const { cus_filters } = useStore()
const { request } = useRequestStore()
const { sales, initAllSales } = useSalesFilterStore()

const feeBookDialog = ref()

const levels = corp.pv_options.auth.levels

const merchandise = reactive<any>({
    custom_id: null,
    mcht_fee: 0,
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

const getCommonParams = () => {
    return {
        'selected_idxs': props.selected_idxs,
        'selected_sales_id': props.selected_sales_id,
        'selected_level': props.selected_level,
    }
}

const post = async (page: string, params: any) => {
    try {
        if (props.selected_idxs.length || (props.selected_sales_id && props.selected_level)) {
            if (await alert.value.show('정말 일괄적용하시겠습니까?')) {
                Object.assign(params, getCommonParams())
                const r = await axios.post('/api/v1/manager/merchandises/batch-updaters/' + page, params)
                snackbar.value.show('성공하였습니다.', 'success')
                emits('update:select_idxs', [])
            }
        }
        else
            snackbar.value.show('가맹점은 1개이상 선택해주세요.', 'error')
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
    }
}

const setSalesFee = (sales_idx: number) => {
    post('sales-fee-direct-apply', {
        'sales_fee': parseFloat(merchandise['sales' + sales_idx + "_fee"]),
        'sales_id': merchandise['sales' + sales_idx + "_id"],
        'level': getIndexByLevel(sales_idx),
        'apply_dt': formatDate(new Date),
    })
}

const setSalesFeeBooking = async (sales_idx: number) => {
    const apply_dt = await feeBookDialog.value.show()
    if(apply_dt !== '') {
        if (await alert.value.show('정말 예약적용하시겠습니까?')) {
            const params = {
                'sales_fee': parseFloat(merchandise['sales' + sales_idx + "_fee"]),
                'sales_id': merchandise['sales' + sales_idx + "_id"],
                'level': getIndexByLevel(sales_idx),
                'apply_dt': apply_dt,
            }
            const r = await axios.post('/api/v1/manager/merchandises/batch-updaters/sales-fee-book-apply', Object.assign(params, getCommonParams()))
            snackbar.value.show('성공하였습니다.', 'success')
            emits('update:select_idxs', [])
        }
    }
}

const setMchtFee = () => {
    post('mcht-fee-direct-apply', {
        'mcht_fee': parseFloat(merchandise.mcht_fee),
        'hold_fee': parseFloat(merchandise.hold_fee),
        'apply_dt': formatDate(new Date),
    })
}

const setMchtFeeBooking = async () => {
    const apply_dt = await feeBookDialog.value.show()
    if(apply_dt !== '') {
        if (await alert.value.show('정말 예약적용하시겠습니까?')) {
            const params = {
                'mcht_fee': parseFloat(merchandise.mcht_fee),
                'hold_fee': parseFloat(merchandise.hold_fee),
                'apply_dt': apply_dt,
            }
            const r = await axios.post('/api/v1/manager/merchandises/batch-updaters/mcht-fee-book-apply', Object.assign(params, getCommonParams()))
            snackbar.value.show('성공하였습니다.', 'success')
        }
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


const batchRemove = async () => {
    const count = props.selected_idxs.length
    if(count === 0)
        snackbar.value.show('가맹점을 1개이상 선택해주세요.', 'error')
    else {
        if (await alert.value.show('정말 ' + count + '개의 가맹점을 일괄삭제 하시겠습니까?<br><h5>결제모듈 등 하위 정보들도 같이 삭제됩니다.</h5>')) {
            const params = { selected_idxs: props.selected_idxs }
            const r = await request({ url: `/api/v1/manager/merchandises/batch-updaters/remove`, method: 'delete', data: params }, true)
            emits('update:select_idxs', [])
        }
    }
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
        <VCard title="가맹점 일괄 작업">
        <VCardText>
            <template v-if="props.selected_sales_id === 0 && props.selected_level === 0">
                <div style=" display: flex;align-items: center; justify-content: space-between;">
                    <b>선택된 가맹점 : {{ props.selected_idxs.length.toLocaleString() }}개</b>
                    <VBtn type="button" color="error" @click="batchRemove()" style="float: inline-end;" size="small">
                        일괄삭제
                        <VIcon size="18" icon="tabler-trash" />
                    </VBtn>
                </div>
                <VDivider style="margin: 0.5em 0;" />
            </template>
            <div style="width: 100%;">
                <template v-for="i in 6" :key="i">
                    <VRow>
                        <VCol :cols="12"
                            v-if="levels['sales' + (6 - i) + '_use'] && getUserLevel() >= getIndexByLevel(6 - i)">
                            <VRow no-gutters style="align-items: center;">
                                <VCol>{{ levels['sales' + (6 - i) + '_name'] }}/수수료율</VCol>
                                <VCol md="8">
                                    <div class="batch-container">
                                        <div>
                                            <VAutocomplete :menu-props="{ maxHeight: 400 }"
                                                v-model="merchandise['sales' + (6 - i) + '_id']" :items="sales[6 - i].value"
                                                :label="levels['sales' + (6 - i) + '_name'] + '선택'" item-title="sales_name"
                                                item-value="id" single-line style="width: 200px;" />
                                            <VTooltip activator="parent" location="top"
                                                v-if="merchandise['sales' + (6 - i) + '_id']">
                                                {{ sales[6 - i].value.find(obj => obj.id ===
                                                    merchandise['sales' + (6 - i) + '_id'])?.sales_name }}
                                            </VTooltip>
                                        </div>
                                        <VTextField v-model="merchandise['sales' + (6 - i) + '_fee']" type="number"
                                            suffix="%" style='margin-left: 0.5em;' />
                                        <VBtn variant="tonal" size="small" @click="setSalesFee(6 - i)" style='margin-left: 0.5em;'>
                                            즉시적용
                                            <VIcon end size="18" icon="tabler-direction-sign" />
                                        </VBtn>
                                        <VBtn variant="tonal" size="small" color="secondary" @click="setSalesFeeBooking(6 - i)"
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
                <VDivider style="margin: 1em 0;" />
                <VRow>
                    <VCol :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol>거래/유보금 수수료율</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <VTextField v-model="merchandise.mcht_fee" type="number" suffix="%" />
                                    <VTextField v-model="merchandise.hold_fee" type="number" suffix="%"
                                        style="width: 200px; margin-left: 0.5em;" />
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setMchtFee()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                    <VBtn variant="tonal" size="small" color="secondary" @click="setMchtFeeBooking()"
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
                <VRow>
                    <VCol :cols="12">
                        <VRow no-gutters style="align-items: center;">
                            <VCol>계좌 정보</VCol>
                            <VCol md="10">
                                <div class="batch-container" style="align-items: baseline;">
                                    <VTextField v-model="merchandise.acct_num" prepend-inner-icon="ri-bank-card-fill"
                                        placeholder="계좌번호 입력" persistent-placeholder 
                                        style="margin-right: 0.5em;"/>
                                    <VTextField v-model="merchandise.acct_name" prepend-inner-icon="tabler-user"
                                        placeholder="예금주 입력" persistent-placeholder 
                                        style="max-width: 11em; margin-right: 0.5em;"/>
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="merchandise.bank"
                                        :items="[{ code: null, title: '선택안함' }].concat(banks)"
                                        prepend-inner-icon="ph-buildings" label="은행 선택"
                                        :hint="`${merchandise.bank.title}, 은행 코드: ${merchandise.bank.code ? merchandise.bank.code : '000'} `"
                                        item-title="title" item-value="code" persistent-hint return-object single-line
                                        style="max-width: 11em;" />
                                    <VBtn variant="tonal" size="small" @click="setAccountInfo()"
                                        style="margin-bottom: auto; margin-left: 0.5em;">
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
                            <VCol>커스텀 필터</VCol>
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
                            <VCol>사업자 번호</VCol>
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
                            <VCol>수수료율 노출</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <BooleanRadio :radio="merchandise.is_show_fee"
                                        @update:radio="merchandise.is_show_fee = $event">
                                        <template #true>노출</template>
                                        <template #false>숨김</template>
                                    </BooleanRadio>
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
                            <VCol>전산 사용상태</VCol>
                            <VCol md="8">
                                <div class="batch-container">
                                    <BooleanRadio :radio="merchandise.enabled" @update:radio="merchandise.enabled = $event">
                                        <template #true>ON</template>
                                        <template #false>OFF</template>
                                    </BooleanRadio>
                                    <VBtn style='margin-left: 0.5em;' variant="tonal" size="small" @click="setEnabled()">
                                        즉시적용
                                        <VIcon end size="18" icon="tabler-direction-sign" />
                                    </VBtn>
                                </div>
                            </VCol>
                        </VRow>
                    </VCol>
                </VRow>
                <template v-if="corp.pv_options.paid.use_noti">
                    <VDivider style="margin: 1em 0;" />
                    <VRow>
                        <VCol :cols="12">
                            <VRow no-gutters style="align-items: center;">
                                <VCol>
                                    <BaseQuestionTooltip :location="'top'" :text="'노티 URL 등록'"
                                        :content="'선택한 가맹점의 모든 노티 URL이 추가됩니다.<br>(같은 노티 URL의 중복등록은 불가능합니다.)'">
                                    </BaseQuestionTooltip>
                                </VCol>
                                <VCol md="10">
                                    <div class="batch-container">
                                        <VTextField v-model="noti.noti_url" type="text"
                                            placeholder="https://www.naver.com" 
                                            style="margin-right: 0.5em;"/>
                                        <VTextField v-model="noti.noti_note" label="메모사항"
                                            prepend-inner-icon="twemoji-spiral-notepad" maxlength="300" 
                                            style="margin-right: 0.5em;"/>
                                        <VBtn variant="tonal" size="small" @click="setNotiUrl()"
                                            >
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
    </VCard>
    <FeeBookDialog ref="feeBookDialog"/>
    </section>
</template>
