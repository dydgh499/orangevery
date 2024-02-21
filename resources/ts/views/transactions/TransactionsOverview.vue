<script lang="ts" setup>
import { useThemeConfig } from '@core/composable/useThemeConfig'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { requiredValidator, lengthValidatorV2 } from '@validators'
import type { Transaction, Merchandise, PayModule, PaySection, Options } from '@/views/types'
import { module_types, installments, payModFilter } from '@/views/merchandises/pay-modules/useStore'
import { getUserLevel, getIndexByLevel } from '@axios'
import { dev_settle_types } from '@/views/services/brands/useStore'
import { ko } from 'date-fns/locale';
import corp from '@corp'

interface Props {
    item: Transaction,
}

const formatDate = <any>(inject('$formatDate'))
const formatTime = <any>(inject('$formatTime'))

const props = defineProps<Props>()
const { pgs, pss, settle_types, terminals, cus_filters, psFilter, finance_vans } = useStore()
const { sales, mchts, initAllSales, hintSalesApplyFee } = useSalesFilterStore()
const { theme } = useThemeConfig()

const levels = corp.pv_options.auth.levels
const pay_modules = ref<PayModule[]>([])
const trx_dttm = ref(<string>(''))
const cxl_dttm = ref(<string>(''))

const getDttmFormat = (date: Date) => {
    return formatDate(date) + " " + formatTime(date)
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

            if (pmod.use_realtime_deposit) {
                const idx = finance_vans.find(obj => obj.id === pmod.fin_id)?.dev_fee
                props.item.dev_realtime_fee = idx ? (finance_vans[idx].dev_fee * 100).toFixed(3) : 0
            }
            else
                props.item.dev_realtime_fee = 0
        }
    }
}
const changeMchtEvent = () => {
    if (props.item.mcht_id != null) {
        const mcht = mchts.find((obj: Merchandise) => obj.id == props.item.mcht_id)
        if (mcht) {
            props.item.sales5_fee = mcht.sales5_fee
            props.item.sales4_fee = mcht.sales4_fee
            props.item.sales3_fee = mcht.sales3_fee
            props.item.sales2_fee = mcht.sales2_fee
            props.item.sales1_fee = mcht.sales1_fee
            props.item.sales0_fee = mcht.sales0_fee
            props.item.sales5_id = mcht.sales5_id
            props.item.sales4_id = mcht.sales4_id
            props.item.sales3_id = mcht.sales3_id
            props.item.sales2_id = mcht.sales2_id
            props.item.sales1_id = mcht.sales1_id
            props.item.sales0_id = mcht.sales0_id

            props.item.hold_fee = mcht.hold_fee
            props.item.mcht_fee = mcht.trx_fee
            props.item.custom_id = mcht.custom_id
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

initAllSales()
onMounted(async () => {
    props.item.dev_fee = (props.item.dev_fee * 100).toFixed(3)
    props.item.dev_realtime_fee = (props.item.dev_realtime_fee * 100).toFixed(3)
    pay_modules.value  = await getAllPayModules()

    trx_dttm.value = props.item.trx_dt + " " + props.item.trx_tm
    cxl_dttm.value = props.item.is_cancel ? props.item.cxl_dt + " " + props.item.cxl_tm : ''

    watchEffect(() => {
        const trx_date = new Date(trx_dttm.value)
        props.item.trx_dt = formatDate(trx_date)
        props.item.trx_tm = formatTime(trx_date)
        if (cxl_dttm.value != '') {
            const cxl_date = new Date(cxl_dttm.value)
            props.item.cxl_dt = formatDate(cxl_date)
            props.item.cxl_tm = formatTime(cxl_date)
        }
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
                        <template v-for="i in 6" :key="i">
                            <CreateHalfVCol :mdl="4" :mdr="8" v-if="levels['sales'+(6 - i)+'_use'] && getUserLevel() > getIndexByLevel(6-i)">
                                <template #name>{{ levels['sales'+(6 - i)+'_name'] }}/수수료율</template>
                                <template #input>
                                    <VRow>
                                        <VCol>
                                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item['sales'+(6 - i)+'_id']"
                                                :items="sales[6-i].value"
                                                prepend-inner-icon="ph:share-network" :label="levels['sales'+(6-i)+'_name']+' 선택'" 
                                                item-title="sales_name" item-value="id" persistent-hint single-line
                                                :hint="hintSalesApplyFee(props.item['sales'+(6-i)+'_id'])"/>
                                                <VTooltip activator="parent" location="top" v-if="props.item['sales'+(6-i)+'_id']">
                                                    {{ sales[6-i].value.find(obj => obj.id === props.item['sales'+(6-i)+'_id'])?.sales_name }}
                                                </VTooltip>
                                        </VCol>
                                        <VCol>
                                            <VTextField v-model="props.item['sales'+(6 - i)+'_fee']" type="number" suffix="%"
                                                :rules="[requiredValidator]" />
                                        </VCol>
                                    </VRow>
                                </template>
                            </CreateHalfVCol>
                        </template>
                        <!-- 👉 가맹점 수수료율 -->
                        <CreateHalfVCol :mdl="4" :mdr="8">
                            <template #name>
                                <BaseQuestionTooltip :location="'top'" :text="'가맹점/수수료율'"
                                    :content="'가맹점 선택시 가맹점 정보 및 결제모듈 선택란이 현재 설정값 기준으로 세팅됩니다.<br>수수료율을 주의해서 입력해주시길 바랍니다.'">
                                </BaseQuestionTooltip>
                            </template>
                            <template #input>
                                <VRow>                                    
                                    <VCol>
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.mcht_id"
                                            :items="[{ id: 0, mcht_name: '선택안함' }].concat(mchts)"
                                            prepend-inner-icon="ph:share-network" label="가맹점 선택" item-title="mcht_name"
                                            item-value="id" @update:modelValue="changeMchtEvent()" single-line />
                                    </VCol>
                                    <VCol>
                                        <VTextField v-model="props.item.mcht_fee" type="number" suffix="%"
                                            :rules="[requiredValidator]" />
                                    </VCol>
                                </VRow>
                            </template>
                            </CreateHalfVCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>유보금 수수료율</template>
                                    <template #input>
                                        <VTextField v-model="props.item.hold_fee" type="number" suffix="%"
                                            :rules="[requiredValidator]" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>커스텀 필터</template>
                                    <template #input>
                                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="props.item.custom_id"
                                            :items="[{ id: 0, name: '선택안함', type: 1 }].concat(cus_filters)"
                                            prepend-inner-icon="tabler:folder-question" label="커스텀 필터" item-title="name"
                                            item-value="id" single-line />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VDivider />
                    </VRow>
                </VCardItem>
                <VCardItem v-show="corp.pv_options.auth.levels.dev_use">
                    <VCardTitle>
                        <BaseQuestionTooltip :location="'top'" :text="`${corp.pv_options.auth.levels.dev_name} 수수료`"
                            :content="'해당 정보는 수정할 수 없습니다.'">
                        </BaseQuestionTooltip>
                    </VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="4" :mdr="8">
                            <template #name>{{ corp.pv_options.auth.levels.dev_name }} 정산타입</template>
                            <template #input>
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="corp.dev_settle_type"
                                    :items="dev_settle_types" prepend-inner-icon="ph-buildings" item-title="title"
                                    item-value="id" single-line readonly />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="4" :mdr="8">
                            <template #name>{{ corp.pv_options.auth.levels.dev_name }} 수수료</template>
                            <template #input>
                                <VTextField v-model="props.item.dev_fee" type="number" suffix="%" readonly />
                            </template>
                        </CreateHalfVCol>
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
                        <VCol cols="12" v-show="false">
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
                                            :items="[{ id: 0, name: '미선택' }].concat(terminals)"
                                            prepend-inner-icon="ic-outline-send-to-mobile" label="장비 선택" item-title="name"
                                            item-value="id" single-line />
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
                <VCardItem v-show="props.item.dev_realtime_fee">
                    <VCardTitle>
                        <BaseQuestionTooltip :location="'top'" :text="'실시간이체 수수료'" :content="'해당 정보는 수정할 수 없습니다.'">
                        </BaseQuestionTooltip>
                    </VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="4" :mdr="8">
                            <template #name>{{ corp.pv_options.auth.levels.dev_name }} 수수료</template>
                            <template #input>
                                <VTextField v-model="props.item.dev_realtime_fee" type="number" suffix="%" readonly />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
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
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>거래시간</template>
                                    <template #input>
                                        <VueDatePicker v-model="trx_dttm" :action-row="{ showNow: true }"
                                            :enable-seconds="true" :text-input="{ format: 'yyyy-MM-dd HH:mm:ss' }"
                                            locale="ko" :format-locale="ko" :dark="theme === 'dark'" autocomplete="on" utc
                                            :format="getDttmFormat" :teleport="true" />
                                    </template>
                                </CreateHalfVCol>
                            </VRow>
                        </VCol>
                        <VCol cols="12">
                            <VRow>
                                <CreateHalfVCol :mdl="4" :mdr="8">
                                    <template #name>취소시간</template>
                                    <template #input>
                                        <VueDatePicker v-model="cxl_dttm" :action-row="{ showNow: true }"
                                            :enable-seconds="true" :text-input="{ format: 'yyyy-MM-dd HH:mm:ss' }"
                                            locale="ko" :format-locale="ko" :dark="theme === 'dark'" autocomplete="on" utc
                                            :format="getDttmFormat" :teleport="true" />
                                    </template>
                                </CreateHalfVCol>
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
                                            :rules="[requiredValidator]" maxlength="18" />
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
    </VRow></template>
