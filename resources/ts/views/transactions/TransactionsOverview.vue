<script lang="ts" setup>
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useSalesFilterStore, feeApplyHistoires } from '@/views/salesforces/useStore'
import { getAllMerchandises } from '@/views/merchandises/useStore'
import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { requiredValidator, lengthValidatorV2 } from '@validators'
import type { Transaction, Merchandise, PayModule, PaySection, Options } from '@/views/types'
import { module_types, installments, payModFilter } from '@/views/merchandises/pay-modules/useStore'
import corp from '@corp'

interface Props {
    item: Transaction,
}

const props = defineProps<Props>()

const { pgs, pss, settle_types, terminals, cus_filters, psFilter } = useStore()
const { sales, classification } = useSalesFilterStore()

const levels = corp.pv_options.auth.levels
const sales5 = ref(<any>({ id: null, sales_name: '선택안함' }))
const sales4 = ref(<any>({ id: null, sales_name: '선택안함' }))
const sales3 = ref(<any>({ id: null, sales_name: '선택안함' }))
const sales2 = ref(<any>({ id: null, sales_name: '선택안함' }))
const sales1 = ref(<any>({ id: null, sales_name: '선택안함' }))
const sales0 = ref(<any>({ id: null, sales_name: '선택안함' }))
const mcht = ref(<any>({ id: null, mcht_name: '선택안함' }))
const custom = ref(<any>({ id: null, type: 1, name: '사용안함' }))
const pay_modules = ref<PayModule[]>([])
let merchandises = <Merchandise[]>([])
let fee_histories = <any[]>([])


const initTrxAt = (is_trx: boolean) => {
    if (is_trx) {
        props.item.trx_dt = null
        props.item.trx_tm = null
    }
    else {
        props.item.cxl_dt = null
        props.item.cxl_tm = null
    }
}
const changePaymodEvent = () => {
    if (props.item.pmod_id != null) {
        const pmod = pay_modules.value.find((obj: PayModule) => obj.id == props.item.pmod_id)
        if (pmod) {
            props.item.module_type = pmod.module_type
            props.item.terminal_id = pmod.terminal_id
            props.item.pg_id = pmod.pg_id
            props.item.ps_id = pmod.ps_id
            props.item.mcht_settle_type = pmod.settle_type
            props.item.mcht_settle_fee = pmod.settle_fee as number
            props.item.mid = pmod.mid
            props.item.tid = pmod.tid
        }
    }
}
const changeMchtEvent = () => {
    if (props.item.mcht_id != null) {
        const mcht = merchandises.find((obj: Merchandise) => obj.id == props.item.mcht_id)
        if (mcht) {
            props.item.sales5_fee = mcht.sales5_fee
            props.item.sales4_fee = mcht.sales4_fee
            props.item.sales3_fee = mcht.sales3_fee
            props.item.sales2_fee = mcht.sales2_fee
            props.item.sales1_fee = mcht.sales1_fee
            props.item.sales0_fee = mcht.sales0_fee
            props.item.hold_fee = mcht.hold_fee
            props.item.mcht_fee = mcht.trx_fee

            sales5.value = sales[5].value.find(obj => obj.id === mcht.sales5_id)
            sales4.value = sales[4].value.find(obj => obj.id === mcht.sales4_id)
            sales3.value = sales[3].value.find(obj => obj.id === mcht.sales3_id)
            sales2.value = sales[2].value.find(obj => obj.id === mcht.sales2_id)
            sales1.value = sales[1].value.find(obj => obj.id === mcht.sales1_id)
            sales0.value = sales[0].value.find(obj => obj.id === mcht.sales0_id)
            custom.value = cus_filters.find(obj => obj.id === props.item.custom_id)
        }
    }
}
const filterPgs = computed(() => {
    const filter = pss.filter(item => { return item.pg_id == props.item.pg_id })
    props.item.ps_id = psFilter(filter, props.item.ps_id as number)
    props.item.ps_fee = pss.find((obj: PaySection) => obj.id == props.item.ps_id)?.trx_fee
    return filter
})
const filterPayMod = computed(() => {
    const filter = pay_modules.value.filter((obj: PayModule) => { return obj.mcht_id == props.item.mcht_id })
    props.item.pmod_id = payModFilter(pay_modules.value, filter, props.item.pmod_id as number)
    return filter
})
const filterInsts = computed(() => {
    if (props.item.pmod_id != null) {
        const pmod = pay_modules.value.find((obj: PayModule) => obj.id == props.item.pmod_id)
        return installments.filter((obj: Options) => { return pmod && obj.id <= pmod.installment });
    }
    else
        return []
})
const hintSalesApplyFee = (sales: any): string => {
    if (sales && sales.id) {
        const history = fee_histories.find(obj => obj.sales_id === sales.id)
        return history ? '마지막 일괄적용: ' + (history.trx_fee * 100).toFixed(3) + '%' : '';
    }
    else
        return ''
}

onMounted(async () => {
    await Promise.all([
        classification(),
        feeApplyHistoires(),
        getAllPayModules(),
        getAllMerchandises()
    ]).then(([classificationResult, feeHistoriesResult, payModulesResult, merchandisesResult]) => {
        fee_histories = feeHistoriesResult
        pay_modules.value = payModulesResult
        merchandises = merchandisesResult

        sales5.value = sales[5].value.find(obj => obj.id === props.item.sales5_id)
        sales4.value = sales[4].value.find(obj => obj.id === props.item.sales4_id)
        sales3.value = sales[3].value.find(obj => obj.id === props.item.sales3_id)
        sales2.value = sales[2].value.find(obj => obj.id === props.item.sales2_id)
        sales1.value = sales[1].value.find(obj => obj.id === props.item.sales1_id)
        sales0.value = sales[0].value.find(obj => obj.id === props.item.sales0_id)
        mcht.value = merchandises.find(obj => obj.id === props.item.mcht_id)
        custom.value = cus_filters.find(obj => obj.id === props.item.custom_id)

        watchEffect(() => {
            console.log(1)
            props.item.sales5_id = sales5.value?.id || null
            props.item.sales4_id = sales4.value?.id || null
            props.item.sales3_id = sales3.value?.id || null
            props.item.sales2_id = sales2.value?.id || null
            props.item.sales1_id = sales1.value?.id || null
            props.item.sales0_id = sales0.value?.id || null
            props.item.custom_id = custom.value?.id || null
            props.item.mcht_id = mcht.value.id
        })
    })
})

</script>
<template>
    <VRow class="match-height">
        <!-- 👉 가맹점 정보 -->
        <VCol cols="12" md="4">
            <VCard>
                <VCardItem>
                    <VCardTitle>가맹점 정보</VCardTitle>
                    <VRow class="pt-5">
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales5_use">
                            <VRow>
                                <VCol cols="12" md="4">
                                    <label>{{ levels.sales5_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales5"
                                        :items="[{ id: null, sales_name: '선택안함' }].concat(sales[5].value)"
                                        prepend-inner-icon="ph:share-network" :label="levels.sales5_name + ' 선택'"
                                        item-title="sales_name" persistent-hint :hint="hintSalesApplyFee(sales5)"
                                        item-value="id" return-object />
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VTextField v-model="props.item.sales5_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales4_use">
                            <VRow>
                                <VCol cols="12" md="4">
                                    <label>{{ levels.sales4_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales4"
                                        :items="[{ id: null, sales_name: '선택안함' }].concat(sales[4].value)"
                                        prepend-inner-icon="ph:share-network" :label="levels.sales4_name + ' 선택'"
                                        item-title="sales_name" persistent-hint :hint="hintSalesApplyFee(sales4)"
                                        item-value="id" return-object />
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VTextField v-model="props.item.sales4_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales3_use">
                            <VRow>
                                <VCol cols="12" md="4">
                                    <label>{{ levels.sales3_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales3"
                                        :items="[{ id: null, sales_name: '선택안함' }].concat(sales[3].value)"
                                        prepend-inner-icon="ph:share-network" :label="levels.sales3_name + ' 선택'"
                                        item-title="sales_name" persistent-hint :hint="hintSalesApplyFee(sales3)"
                                        item-value="id" return-object />
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VTextField v-model="props.item.sales3_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales2_use">
                            <VRow>
                                <VCol cols="12" md="4">
                                    <label>{{ levels.sales2_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales2"
                                        :items="[{ id: null, sales_name: '선택안함' }].concat(sales[2].value)"
                                        prepend-inner-icon="ph:share-network" :label="levels.sales2_name + ' 선택'"
                                        item-title="sales_name" persistent-hint :hint="hintSalesApplyFee(sales2)"
                                        item-value="id" return-object />
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VTextField v-model="props.item.sales2_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales1_use">
                            <VRow>
                                <VCol cols="12" md="4">
                                    <label>{{ levels.sales1_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales1"
                                        :items="[{ id: null, sales_name: '선택안함' }].concat(sales[1].value)"
                                        prepend-inner-icon="ph:share-network" :label="levels.sales1_name + ' 선택'"
                                        item-title="sales_name" persistent-hint :hint="hintSalesApplyFee(sales1)"
                                        item-value="id" return-object />
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VTextField v-model="props.item.sales1_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 영업점 수수료율 -->
                        <VCol cols="12" v-if="levels.sales0_use">
                            <VRow>
                                <VCol cols="12" md="4">
                                    <label>{{ levels.sales0_name }}/수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales0"
                                        :items="[{ id: null, sales_name: '선택안함' }].concat(sales[0].value)"
                                        prepend-inner-icon="ph:share-network" :label="levels.sales0_name + ' 선택'"
                                        item-title="sales_name" persistent-hint :hint="hintSalesApplyFee(sales0)"
                                        item-value="id" return-object />
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VTextField v-model="props.item.sales0_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 가맹점 수수료율 -->
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="4">
                                    <BaseQuestionTooltip :location="'top'" :text="'가맹점/수수료율'"
                                        :content="'가맹점 선택시 가맹점 정보 및 결제모듈 선택란이 현재 설정값 기준으로 세팅됩니다.<br>수수료율을 주의해서 입력해주시길 부탁드립니다.'">
                                    </BaseQuestionTooltip>
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="mcht"
                                        :items="[{ id: null, mcht_name: '선택안함' }].concat(merchandises)"
                                        prepend-inner-icon="ph:share-network" label="가맹점 선택" item-title="mcht_name"
                                        item-value="id" @update:modelValue="changeMchtEvent()" return-object />
                                </VCol>
                                <VCol cols="12" :md="4">
                                    <VTextField v-model="props.item.mcht_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="4">
                                    <label>유보금 수수료율</label>
                                </VCol>
                                <VCol cols="12" :md="8">
                                    <VTextField v-model="props.item.hold_fee" type="number" suffix="%"
                                        :rules="[requiredValidator]" />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="4">
                                    <label>커스텀 필터</label>
                                </VCol>
                                <VCol cols="12" md="8">
                                    <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="custom"
                                        :items="[{ id: null, name: '선택안함' }].concat(cus_filters)"
                                        prepend-inner-icon="tabler:folder-question" label="커스텀 필터" item-title="name"
                                        item-value="id" persistent-hint create />
                                </VCol>
                            </VRow>
                        </VCol>
                        <VDivider />
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 결제모듈 정보 -->
        <VCol cols="12" md="4">
            <VCard>
                <VCardItem>
                    <VCardTitle>결제모듈 정보</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>
                                        <BaseQuestionTooltip :location="'top'" :text="'결제모듈 선택'"
                                            :content="'결제모듈 선택 시 결제모듈 정보란이 현시각 기준 결제모듈 설정값으로 자동 세팅됩니다.<br>선택란이 나오지 않는다면, 가맹점을 먼저 선택해주세요.'">
                                        </BaseQuestionTooltip>
                                    </template>
                                    <template #input>
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pmod_id"
                                            :items="filterPayMod" prepend-inner-icon="ic-outline-send-to-mobile"
                                            label="결제모듈 선택" item-title="note" item-value="id" single-line
                                            :rules=[requiredValidator] @update:modelValue="changePaymodEvent()" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>결제모듈 타입</template>
                                    <template #input>
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.module_type"
                                            :items="module_types" prepend-inner-icon="ic-outline-send-to-mobile"
                                            label="결제모듈 타입 선택" item-title="title" item-value="id" single-line
                                            :rules=[requiredValidator] />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12" v-show="props.item.module_type == 0">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>장비 타입</template>
                                    <template #input>
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.terminal_id"
                                            :items="terminals" prepend-inner-icon="ic-outline-send-to-mobile" label="장비 선택"
                                            item-title="name" item-value="id" single-line />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>PG사</template>
                                    <template #input>
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.pg_id" :items="pgs"
                                            prepend-inner-icon="ph-buildings" label="PG사 선택" item-title="pg_name"
                                            item-value="id" single-line :rules=[requiredValidator] />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 PG 구간 -->
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>구간</template>
                                    <template #input>
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.ps_id"
                                            :items="filterPgs" prepend-inner-icon="mdi-vector-intersection" label="구간 선택"
                                            item-title="name" item-value="id" single-line :rules=[requiredValidator] />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 PG 수수료 -->
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>구간 수수료</template>
                                    <template #input>
                                        <VTextField v-model="props.item.ps_fee" type="number" suffix="%"
                                            :rules="[requiredValidator]" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <!-- 👉 정산일 -->
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>정산일</template>
                                    <template #input>
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_settle_type"
                                            :items="settle_types" prepend-inner-icon="ic-outline-send-to-mobile"
                                            label="정산일 선택" item-title="name" item-value="id" :rules=[requiredValidator] />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>입금 수수료</template>
                                    <template #input>
                                        <VTextField v-model="props.item.mcht_settle_fee" type="number" suffix="￦"
                                            :rules="[requiredValidator]" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>MID</template>
                                    <template #input>
                                        <VTextField v-model="props.item.mid" type="text" :rules="[requiredValidator]"
                                            prepend-inner-icon="tabler-user" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>TID</template>
                                    <template #input>
                                        <VTextField v-model="props.item.tid" type="text" prepend-inner-icon="jam-key-f" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>

        </VCol>
        <!-- 👉 매출 정보 -->
        <VCol cols="12" md="4">
            <VCard>
                <VCardItem>
                    <VCardTitle>매출 정보</VCardTitle>
                    <VRow class="pt-5">
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="3">
                                    <label>거래시간</label>
                                </VCol>
                                <VCol cols="12" md="3">
                                    <VTextField v-model="props.item.trx_dt" type="date" :rules="[requiredValidator]" />
                                </VCol>
                                <VCol cols="12" md="3">
                                    <VTextField v-model="props.item.trx_tm" type="time" :rules="[requiredValidator]"
                                        step="1" />
                                </VCol>
                                <VCol cols="12" md="3" style="text-align: center;">
                                    <VBtn variant="tonal" @click="initTrxAt(true)">
                                        초기화
                                        <VIcon end
                                            icon="streamline:interface-time-rewind-back-return-clock-timer-countdown" />
                                    </VBtn>
                                </VCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <VCol cols="12" md="3">
                                    <label>취소시간</label>
                                </VCol>
                                <VCol cols="12" md="3">
                                    <VTextField v-model="props.item.cxl_dt" type="date" />
                                </VCol>
                                <VCol cols="12" md="3">
                                    <VTextField v-model="props.item.cxl_tm" type="time" step="1" />
                                </VCol>
                                <VCol cols="12" md="3" style="text-align: center;">
                                    <VBtn variant="tonal" @click="initTrxAt(false)">
                                        초기화
                                        <VIcon end
                                            icon="streamline:interface-time-rewind-back-return-clock-timer-countdown" />
                                    </VBtn>
                                </VCol>
                            </VRow>
                        </VCol>

                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>
                                        <BaseQuestionTooltip :location="'top'" :text="'할부'"
                                            :content="'결제모듈에서 선택한 할부설정기간 이내의 값만 할부를 선택할 수 있습니다.'">
                                        </BaseQuestionTooltip>
                                    </template>
                                    <template #input>
                                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.installment"
                                            :items="filterInsts" prepend-inneer-icon="fluent-credit-card-clock-20-regular"
                                            label="할부 선택" item-title="title" item-value="id" single-line />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>
                                        <BaseQuestionTooltip :location="'top'" :text="'거래금액'"
                                            :content="'취소금액 입력시 꼭 -(마이너스 기호)를 입력해주세요.'">
                                        </BaseQuestionTooltip>
                                    </template>
                                    <template #input>
                                        <VTextField v-model="props.item.amount" type="number" suffix="￦"
                                            placeholder="거래금액을 입력해주세요" prepend-inner-icon="ic:outline-price-change"
                                            :rules="[requiredValidator]" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>주문번호</template>
                                    <template #input>
                                        <VTextField v-model="props.item.ord_num" type="text" placeholder="주문번호를 입력해주세요"
                                            prepend-inner-icon="ic:outline-border-color" :rules="[requiredValidator]" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>거래번호</template>
                                    <template #input>
                                        <VTextField v-model="props.item.trx_id" type="text" placeholder="거래번호를 입력해주세요"
                                            prepend-inner-icon="icon-park-twotone:transaction-order"
                                            :rules="[requiredValidator]" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>원거래번호</template>
                                    <template #input>
                                        <VTextField v-model="props.item.ori_trx_id" type="text" placeholder="원거래번호를 입력해주세요"
                                            prepend-inner-icon="icon-park-outline:transaction-order" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>카드번호</template>
                                    <template #input>
                                        <VTextField v-model="props.item.card_num" type="text" placeholder="카드번호를 입력해주세요"
                                            persistent-placeholder counter prepend-inner-icon="emojione:credit-card"
                                            :rules="[requiredValidator]" maxlength="16" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>발급사</template>
                                    <template #input>
                                        <VTextField v-model="props.item.issuer" type="text" placeholder="발급사를 입력해주세요"
                                            prepend-inner-icon="ph-buildings" :rules="[requiredValidator]" maxlength="20" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>매입사</template>
                                    <template #input>
                                        <VTextField v-model="props.item.acquirer" type="text" placeholder="매입사를 입력해주세요"
                                            prepend-inner-icon="ph-buildings" :rules="[requiredValidator]" maxlength="20" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>승인번호</template>
                                    <template #input>
                                        <VTextField v-model="props.item.appr_num" type="text" placeholder="승인번호를 입력해주세요"
                                            prepend-inner-icon="icon-park-solid:transaction-order" persistent-placeholder
                                            counter :rules="[requiredValidator, lengthValidatorV2(props.item.appr_num, 8)]"
                                            maxlength="8" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>구매자명</template>
                                    <template #input>
                                        <VTextField v-model="props.item.buyer_name" type="text" placeholder="구매자명을 입력해주세요"
                                            prepend-inner-icon="tabler-user" maxlength="50" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>구매자 연락처</template>
                                    <template #input>
                                        <VTextField v-model="props.item.buyer_phone" type="text"
                                            placeholder="구매자 연락처를 입력해주세요" prepend-inner-icon="tabler-device-mobile"
                                            maxlength="20" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>상품명</template>
                                    <template #input>
                                        <VTextField v-model="props.item.item_name" type="text" placeholder="상품명을 입력해주세요"
                                            prepend-inner-icon="streamline:shopping-bag-hand-bag-2-shopping-bag-purse-goods-item-products"
                                            maxlength="100" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VDivider />
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
