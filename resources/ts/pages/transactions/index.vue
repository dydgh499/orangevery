<script setup lang="ts">
import BatchDialog from '@/layouts/dialogs/BatchDialog.vue'
import CancelDepositDialog from '@/layouts/dialogs/transactions/CancelDepositDialog.vue'
import CancelPartDialog from '@/layouts/dialogs/transactions/CancelPartDialog.vue'
import CancelTransDialog from '@/layouts/dialogs/transactions/CancelTransDialog.vue'
import NotiSendHistoriesDialog from '@/layouts/dialogs/transactions/NotiSendHistoriesDialog.vue'
import RealtimeHistoriesDialog from '@/layouts/dialogs/transactions/RealtimeHistoriesDialog.vue'
import SalesSlipDialog from '@/layouts/dialogs/transactions/SalesSlipDialog.vue'
import MchtBlacklistCreateDialog from '@/layouts/dialogs/users/MchtBlacklistCreateDialog.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { issuers } from '@/views/complaints/useStore'
import { module_types } from '@/views/merchandises/pay-modules/useStore'
import { selectFunctionCollect } from '@/views/selected'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { getProfitColName } from '@/views/transactions/transacitonsHeader'
import TransactionsIndexTd from '@/views/transactions/TransactionsIndexTd.vue'
import { useSearchStore } from '@/views/transactions/useStore'
import { ItemTypes } from '@core/enums'

import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import type { Options } from '@/views/types'
import { getUserLevel, salesLevels, user_info } from '@axios'
import { DateFilters } from '@core/enums'
import corp from '@corp'

const { store, head, exporter, metas } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { pgs, pss, settle_types, terminals, cus_filters } = useStore()


const salesslip = ref()
const cancelTran = ref()
const cancelPart = ref()
const cancelDeposit = ref()
const realtimeHistoryDialog = ref()
const notiSendHistoriesDialog = ref()
const batchDialog = ref()
const mchtBlackListDlg = ref(null)

const levels = corp.pv_options.auth.levels

provide('store', store)
provide('head', head)
provide('exporter', exporter)

provide('salesslip', salesslip)
provide('cancelTran', cancelTran)
provide('cancelPart', cancelPart)
provide('cancelDeposit', cancelDeposit)
provide('realtimeHistoryDialog', realtimeHistoryDialog)
provide('notiSendHistoriesDialog', notiSendHistoriesDialog)
provide('mchtBlackListDlg', mchtBlackListDlg)

store.params.level = 10
store.params.issuer = '전체'
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

onMounted(() => {
    watchEffect(async () => {
        if (store.getChartProcess() === false) {
            const r = await store.getChartData()
            metas[0]['stats'] = r.data.appr.amount.toLocaleString() + ' ￦'
            metas[0]['percentage'] = r.data.appr.amount ? 100 : 0
            metas[0]['subtitle'] = r.data.appr.count.toLocaleString() + '건'

            metas[1]['stats'] = r.data.cxl.amount.toLocaleString() + ' ￦'
            metas[1]['subtitle'] = r.data.cxl.count.toLocaleString() + '건'
            metas[1]['percentage'] = store.getPercentage(r.data.cxl.amount, r.data.appr.amount)

            metas[2]['stats'] = r.data.amount.toLocaleString() + ' ￦'
            metas[2]['percentage'] = store.getPercentage(r.data.amount, r.data.appr.amount)
            metas[2]['subtitle'] = r.data.count.toLocaleString() + '건'
            if (getUserLevel() === 10 && user_info.value.is_show_fee === false) { 

            }
            else {
                metas[3]['percentage'] = store.getPercentage(r.data.profit, r.data.appr.amount)
                metas[3]['subtitle'] = r.data.count.toLocaleString() + '건'
                metas[3]['stats'] = r.data.profit.toLocaleString() + ' ￦'
            }
        }
    })
})
</script>
<template>
    <div>
        <BaseIndexView placeholder="상호, MID, TID, 승인번호, 거래번호, 결제모듈 별칭, 주민번호, 사업자번호, 휴대폰 번호 검색" :metas="metas"
            :add="getUserLevel() >= 35" add_name="매출" :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #sales_extra_field>
                        <VCol cols="6" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.level"
                                :items="getAllLevels()" :label="`등급 선택`" item-title="title" item-value="id"
                                @update:modelValue="store.updateQueryString({ level: store.params.level })" />
                        </VCol>
                    </template>
                    <template #pg_extra_field>
                        <VCol cols="6" sm="3" v-if="getUserLevel() >= 35">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.mcht_settle_type"
                                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산타입 필터"
                                item-title="name" item-value="id"
                                @update:modelValue="store.updateQueryString({ mcht_settle_type: store.params.mcht_settle_type })" />
                        </VCol>
                        <VCol cols="6" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.issuer"
                                :items="['전체'].concat(issuers)" label="발급사 필터"
                                @update:modelValue="store.updateQueryString({ issuer: store.params.issuer })" />
                        </VCol>
                        <VCol cols="6" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.module_type"
                                :items="[{ id: null, title: '전체' }].concat(module_types)" label="모듈타입 필터"
                                item-title="title" item-value="id"
                                @update:modelValue="[store.updateQueryString({ module_type: store.params.module_type })]" />
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="carbon:batch-job" @click="batchDialog.show()" v-if="getUserLevel() >= 35"
                    color="primary" size="small" style="margin: 0.25em;">
                    일괄작업
                </VBtn>
                <div>
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.only_realtime_fail"
                        label="즉시출금 실패건 조회" color="error"
                        @update:modelValue="store.updateQueryString({ only_realtime_fail: store.params.only_realtime_fail })"
                        v-if="corp.pv_options.paid.use_realtime_deposit && getUserLevel() >= 35" />
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.only_cancel"
                        label="취소 매출 조회" color="error"
                        @update:modelValue="store.updateQueryString({ only_cancel: store.params.only_cancel })" />
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.no_settlement"
                        label="미정산 매출 조회" color="warning"
                        @update:modelValue="store.updateQueryString({ no_settlement: store.params.no_settlement })" />
                </div>
            </template>
            <template #headers>
                <tr>
                    <template v-for="(sub_header, index) in head.getSubHeaderComputed" :key="index">
                        <th :colspan="head.getSubHeaderComputed.length - 1 == index ? sub_header.width + 1 : sub_header.width"
                            class='list-square sub-headers' v-show="sub_header.width">
                            <span>{{ sub_header.ko }}</span>
                        </th>
                    </template>
                </tr>
                <tr>
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible"
                        class='list-square'>
                        <span v-if="key == 'total_trx_amount'">
                            <BaseQuestionTooltip :location="'top'"
                                :text="store.params.level === 10 ? (header.ko as string) : '총 지급액'"
                                :content="'총 거래 수수료 = 금액 - (거래 수수료 + 유보금 + 이체 수수료)'" />
                        </span>
                        <span v-else-if="key == 'mcht_settle_fee' && store.params.level == 10">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'입금 수수료는 가맹점만 적용됩니다.'" />
                        </span>
                        <span v-else-if="key == 'trx_amount'">
                            <span>{{ store.params.level === 10 ? header.ko : '지급액' }}</span>
                        </span>
                        <span v-else-if="key == 'profit'">
                            <span>{{ getProfitColName(store.params.level) }}</span>
                        </span>
                        <span v-else-if="key == 'hold_amount' && store.params.level == 10">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'유보금은 가맹점만 적용됩니다.'" />
                        </span>
                        <span v-else>
                            <div class='check-label-container' v-if="key == 'id' && getUserLevel() >= 35">
                                <VCheckbox v-model="all_selected" class="check-label" />
                                <span>선택/취소</span>
                            </div>
                            <span v-else>
                                {{ header.ko }}
                            </span>
                        </span>
                    </th>
                </tr>
            </template>
            <template #body>
                <tr v-for="(item, index) in store.getItems" :key="item['id']">
                    <VTooltip activator="parent" location="end" open-delay="250" transition="scroll-x-transition" v-if="$vuetify.display.smAndDown === false">
                        <span>{{ `${item['mcht_name']} (${item['is_cancel'] ? "취소" : "승인"})` }}</span>
                        <br>
                        <span>{{ `${item['amount'].toLocaleString()}원` }}</span>
                    </VTooltip>
                    <template v-for="(_header, _key, _index) in head.headers" :key="_key">
                        <td v-if="_header.visible" :style="item['is_cancel'] ? 'color:red;' : ''" class='list-square'>
                            <span v-if="_key == 'id'">
                                <div class='check-label-container'>
                                    <VCheckbox v-if="getUserLevel() >= 35" v-model="selected" :value="item[_key]"
                                        class="check-label" />
                                    <span class="edit-link" @click="getUserLevel() >= 35 ? store.edit(item['id']) : ''">
                                        #{{ item[_key]}}
                                        <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                            상세보기
                                        </VTooltip>
                                    </span>
                                </div>
                            </span>
                            <TransactionsIndexTd v-else :item="item" :_key="_key"/>
                        </td>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
        <SalesSlipDialog ref="salesslip" :pgs="pgs" :style="`zoom: 100%;`"/>
        <CancelTransDialog ref="cancelTran" />
        <CancelPartDialog ref="cancelPart" />
        <CancelDepositDialog ref="cancelDeposit" />
        <RealtimeHistoriesDialog ref="realtimeHistoryDialog" />
        <NotiSendHistoriesDialog ref="notiSendHistoriesDialog" />
        <BatchDialog ref="batchDialog" :selected_idxs="selected" :item_type="ItemTypes.Transaction"
            @update:select_idxs="selected = $event; store.setTable(); store.getChartData()" />
        <MchtBlacklistCreateDialog ref="mchtBlackListDlg" />
    </div>
</template>
