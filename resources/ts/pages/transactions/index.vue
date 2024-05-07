<script setup lang="ts">
import TransactionBatchDialog from '@/layouts/dialogs/TransactionBatchDialog.vue'
import CancelDepositDialog from '@/layouts/dialogs/transactions/CancelDepositDialog.vue'
import CancelPartDialog from '@/layouts/dialogs/transactions/CancelPartDialog.vue'
import CancelTransDialog from '@/layouts/dialogs/transactions/CancelTransDialog.vue'
import RealtimeHistoriesDialog from '@/layouts/dialogs/transactions/RealtimeHistoriesDialog.vue'
import SalesSlipDialog from '@/layouts/dialogs/transactions/SalesSlipDialog.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore'
import { selectFunctionCollect } from '@/views/selected'
import { useStore } from '@/views/services/pay-gateways/useStore'
import ExtraMenu from '@/views/transactions/ExtraMenu.vue'
import { getDateFormat, realtimeResult, settleIdCol, useSearchStore } from '@/views/transactions/useStore'

import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import type { Options } from '@/views/types'
import { getUserLevel, salesLevels, user_info } from '@axios'
import { DateFilters } from '@core/enums'
import corp from '@corp'

const { store, head, exporter, metas, realtimeMessage, mchtGroup } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { pgs, pss, settle_types, terminals, cus_filters } = useStore()

const salesslip = ref()
const cancelTran = ref()
const cancelPart = ref()
const cancelDeposit = ref()
const realtimeHistories = ref()
const transactionBatchDialog = ref()
const levels = corp.pv_options.auth.levels

provide('store', store)
provide('head', head)
provide('exporter', exporter)

provide('salesslip', salesslip)
provide('cancelTran', cancelTran)
provide('cancelPart', cancelPart)
provide('cancelDeposit', cancelDeposit)
provide('realtimeHistories', realtimeHistories)

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))

store.params.level = 10
// 개발사 사용여부
if (Number(corp.pv_options.auth.levels.dev_use))
    store.params.dev_use = 1
// 실시간이체 사용여부
if (Number(corp.pv_options.paid.use_realtime_deposit))
    store.params.use_realtime_deposit = 1

const getAllLevels = () => {
    const sales = salesLevels()
    if (getUserLevel() >= 10)
        sales.unshift(<Options>({ id: 10, title: '가맹점' }))
    if (getUserLevel() >= 35) {
        sales.push(<Options>({ id: 40, title: '본사' }))
    }
    if (levels.dev_use && getUserLevel() >= 35)
        sales.push(<Options>({ id: 50, title: levels.dev_name }))
    return sales
}

const isSalesCol = (key: string) => {
    const sales_cols = ['amount', 'trx_amount', 'mcht_settle_fee', 'hold_amount', 'total_trx_amount', 'profit']
    for (let i = 0; i < sales_cols.length; i++) {
        if (sales_cols[i] === key)
            return true
    }
    return false
}


onMounted(() => {
    watchEffect(async () => {
        if (store.getChartProcess() === false) {
            const r = await store.getChartData()
                metas[0]['stats'] = r.data.appr.amount.toLocaleString() + ' ￦'
                metas[1]['stats'] = r.data.cxl.amount.toLocaleString() + ' ￦'
                metas[2]['stats'] = r.data.amount.toLocaleString() + ' ￦'
                metas[3]['stats'] = r.data.profit.toLocaleString() + ' ￦'
                metas[0]['subtitle'] = r.data.appr.count.toLocaleString() + '건'
                metas[1]['subtitle'] = r.data.cxl.count.toLocaleString() + '건'
                metas[2]['subtitle'] = r.data.count.toLocaleString() + '건'
                metas[3]['subtitle'] = r.data.count.toLocaleString() + '건'
                metas[0]['percentage'] = r.data.appr.amount ? 100 : 0

            if((getUserLevel() == 10 && user_info.value.is_show_fee) || getUserLevel() >= 13) {
                metas[1]['percentage'] = store.getPercentage(r.data.cxl.amount, r.data.appr.amount)
                metas[2]['percentage'] = store.getPercentage(r.data.amount, r.data.appr.amount)
                metas[3]['percentage'] = store.getPercentage(r.data.profit, r.data.appr.amount)                
            }
        }
    })
})
</script>
<template>
    <div>
        <BaseIndexView placeholder="상호, MID, TID, 승인번호, 거래번호, 결제모듈 별칭, 주민번호, 사업자번호, 발급사, 매입사, 휴대폰 번호 검색" :metas="metas"
            :add="user_info.level >= 35" add_name="매출" :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #sales_extra_field>
                        <VCol cols="12" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.level" :items="getAllLevels()"
                                :label="`등급 선택`" item-title="title" item-value="id"
                                @update:modelValue="store.updateQueryString({ level: store.params.level })" />
                        </VCol>
                    </template>
                    <template #pg_extra_field>
                        <VCol cols="12" sm="3" v-if="getUserLevel() >= 35">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.mcht_settle_type"
                                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산타입 필터" item-title="name"
                                item-value="id"
                                @update:modelValue="store.updateQueryString({ mcht_settle_type: store.params.mcht_settle_type })" />                                
                        </VCol>
                        <VCol cols="12" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.module_type"
                                    :items="[{ id: null, title: '전체' }].concat(module_types)" label="모듈타입 필터" item-title="title"
                                    item-value="id"
                                    @update:modelValue="[store.updateQueryString({ module_type: store.params.module_type })]" />
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="tabler-calculator" @click="mchtGroup()" size="small">
                    가맹점별 매출집계
                </VBtn>
                <VBtn prepend-icon="carbon:batch-job" @click="transactionBatchDialog.show()" v-if="getUserLevel() >= 35" color="primary" size="small">
                    일괄 작업
                </VBtn>
                <div>
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.only_realtime_fail" label="즉시출금 실패건 조회" color="error"
                        @update:modelValue="store.updateQueryString({ only_realtime_fail: store.params.only_realtime_fail })" 
                        v-if="corp.pv_options.paid.use_realtime_deposit && getUserLevel() >= 35"/>
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.only_cancel" label="취소 매출 조회" color="error"
                        @update:modelValue="store.updateQueryString({ only_cancel: store.params.only_cancel })" />
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.no_settlement" label="미정산 매출 조회" color="warning"
                        @update:modelValue="store.updateQueryString({ no_settlement: store.params.no_settlement })" />
                </div>
            </template>
            <template #headers>
                <tr>
                    <template v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                        <th v-if="key == 'total_trx_amount'">
                            <BaseQuestionTooltip :location="'top'" :text="store.params.level === 10 ? (header.ko as string) : '총 지급액'"
                                :content="'총 거래 수수료 = 금액 - (거래 수수료 + 유보금 + 이체 수수료)'">
                            </BaseQuestionTooltip>
                        </th>
                        <th v-else-if="key == 'mcht_settle_fee' && store.params.level == 10">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'입금 수수료는 가맹점만 적용됩니다.'">
                            </BaseQuestionTooltip>
                        </th>
                        <th v-else-if="key == 'trx_amount'">
                            <span>{{ store.params.level === 10 ? header.ko : '지급액' }}</span>
                        </th>
                        <th v-else-if="key == 'profit'">
                            <span>{{ store.params.level === 10 ? header.ko : '수익금' }}</span>
                        </th>
                        <th v-else-if="key == 'hold_amount' && store.params.level == 10">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'유보금은 가맹점만 적용됩니다.'">
                            </BaseQuestionTooltip>
                        </th>
                        <template v-else-if="key === 'settle_dt' && store.params.level !== 10">
                        </template>
                        <th v-else>
                            <div class='check-label-container' v-if="key == 'id' && getUserLevel() >= 50">
                                <VCheckbox v-model="all_selected" class="check-label" />
                                <span>선택/취소</span>
                            </div>
                            <span v-else>
                                {{ header.ko }}
                            </span>
                        </th>
                    </template>
                </tr>
            </template>
            <template #body>
                <tr v-for="(item, index) in store.getItems" :key="item['id']">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_key">
                        <template v-if="_header.visible" :style="item['is_cancel'] ? 'color:red;' : ''" class='list-square'>
                            <td v-if="_key == 'id'">
                                <div class='check-label-container'>
                                    <VCheckbox v-if="getUserLevel() >= 50" v-model="selected" :value="item[_key]"
                                        class="check-label" />
                                    <span class="edit-link" @click="getUserLevel() >= 35 ? store.edit(item['id']) : ''">#{{ item[_key] }}</span>
                                </div>
                            </td>
                            <td v-else-if="_key == 'module_type'">
                                <VChip :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                    {{ module_types.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </td>
                            <td v-else-if="_key == 'settle_id'">
                                <VChip :color="settleIdCol(item, store.params.level) === null ? 'default' : 'success'">
                                    {{ settleIdCol(item, store.params.level) === null ? '정산안함' : "#"+settleIdCol(item, store.params.level)}}
                                </VChip>
                            </td>
                            <td v-else-if="_key == 'settle_dt' && store.params.level === 10">
                                {{ getDateFormat(item[_key]) }}
                            </td>
                            <template v-else-if="_key == 'settle_dt' && store.params.level !== 10">
                            </template>
                            <td v-else-if="_key == 'installment'">
                                {{ installments.find(inst => inst['id'] === item[_key])?.title }}
                            </td>
                            <td v-else-if="_key == 'pg_id'">
                                {{ pgs.find(pg => pg['id'] === item[_key])?.pg_name }}
                            </td>
                            <td v-else-if="_key == 'ps_id'">
                                {{ pss.find(ps => ps['id'] === item[_key])?.name }}
                            </td>
                            <td v-else-if="_key == 'mcht_settle_type'">
                                {{ settle_types.find(settle_type => settle_type['id'] === item[_key])?.name }}
                            </td>
                            <td v-else-if="_key == 'terminal_id'">
                                {{ terminals.find(terminal => terminal['id'] === item[_key])?.name }}
                            </td>
                            <td v-else-if="isSalesCol(_key as string)">
                                {{ Number(item[_key]).toLocaleString() }}
                            </td>
                            <td v-else-if="_key.toString().includes('_fee') && _key != 'mcht_settle_fee'">
                                <VChip v-if="item[_key]">
                                    {{ (item[_key] * 100).toFixed(3) }} %
                                </VChip>
                            </td>
                            <td v-else-if="_key == 'resident_num'">
                                <span>{{ item['resident_num_front'] }}</span>
                                <span style="margin: 0 0.25em;">-</span>
                                <span v-if="corp.pv_options.free.resident_num_masking">*******</span>
                                <span v-else>{{ item['resident_num_back'] }}</span>
                            </td>
                            <td v-else-if="_key == 'custom_id'">
                                {{ cus_filters.find(cus => cus.id === item[_key])?.name }}
                            </td>
                            <td v-else-if="_key == 'realtime_result'">
                                <VChip :color="store.getSelectIdColor(realtimeResult(item))">
                                    {{ realtimeMessage(item) }}
                                </VChip>
                            </td>
                            <td v-else-if="_key == 'extra_col'">
                                <ExtraMenu :item="item"></ExtraMenu>
                            </td>
                            <td v-else>
                                {{ item[_key] }}
                            </td>
                        </template>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
        <SalesSlipDialog ref="salesslip" :pgs="pgs" />
        <CancelTransDialog ref="cancelTran" />
        <CancelPartDialog ref="cancelPart" />
        <CancelDepositDialog ref="cancelDeposit" />
        <RealtimeHistoriesDialog ref="realtimeHistories" />
        <TransactionBatchDialog ref="transactionBatchDialog" :selected_idxs="selected" :store="store" :is_mcht="true"/>
</div>
</template>
