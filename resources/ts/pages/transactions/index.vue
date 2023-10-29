<script setup lang="ts">
import { module_types, installments, fin_trx_delays, cxl_types } from '@/views/merchandises/pay-modules/useStore'
import { useSearchStore, realtimeResult, realtimeMessage, realtimeRetryMessage } from '@/views/transactions/useStore'
import { useRequestStore } from '@/views/request'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { selectFunctionCollect } from '@/views/selected'
import { salesLevels } from '@/views/salesforces/useStore'
import ExtraMenu from '@/views/transactions/ExtraMenu.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import SalesSlipDialog from '@/layouts/dialogs/SalesSlipDialog.vue'
import CancelTransDialog from '@/layouts/dialogs/CancelTransDialog.vue'
import RealtimeHistoriesDialog from '@/layouts/dialogs/RealtimeHistoriesDialog.vue'

import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { user_info, getUserLevel } from '@axios'
import type { Options } from '@/views/types'
import { DateFilters } from '@core/enums'
import corp from '@corp'

const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { post } = useRequestStore()
const { pgs, pss, settle_types, terminals, cus_filters } = useStore()

const salesslip = ref()
const cancelTran = ref()
const realtimeHistories = ref()
const levels = corp.pv_options.auth.levels

provide('store', store)
provide('head', head)
provide('exporter', exporter)

provide('salesslip', salesslip)
provide('cancelTran', cancelTran)
provide('realtimeHistories', realtimeHistories)

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))

store.params.level = 10
store.params.dev_use = corp.pv_options.auth.levels.dev_use
store.params.use_realtime_deposit = Number(corp.pv_options.paid.use_realtime_deposit)

const metas = ref([
    {
        icon: 'ic-outline-payments',
        color: 'primary',
        title: '승인액 합계',
        stats: '0',
        percentage: 0,
        subtitle: '0건',
    },
    {
        icon: 'ic-outline-payments',
        color: 'error',
        title: '취소액 합계',
        stats: '0',
        percentage: 0,
        subtitle: '0건',
    },
    {
        icon: 'ic-outline-payments',
        color: 'success',
        title: '매출액 합계',
        stats: '0',
        percentage: 0,
        subtitle: '0건',
    },
    {
        icon: 'ic-outline-payments',
        color: 'warning',
        title: '정산액 합계',
        stats: '0',
        percentage: 0,
        subtitle: '0건',
    },
])
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
const batchRetry = async() => {
    if(await alert.value.show('정말 일괄 재발송하시겠습니까?'))
    {
        const params = { selected : selected.value }
        const url = '/api/v1/manager/transactions/batch-retry'
        const r = await post(url, params)
        if(r.status == 201)
            snackbar.value.show('성공하였습니다.', 'success')
        else
            snackbar.value.show(r.data.message, 'error')
        store.setTable()
    }
}
onMounted(() => {
    watchEffect(async () => {
        if (store.getChartProcess() === false) {
            const r = await store.getChartData()
            metas.value[0]['stats'] = r.data.appr.amount.toLocaleString() + ' ￦'
            metas.value[1]['stats'] = r.data.cxl.amount.toLocaleString() + ' ￦'
            metas.value[2]['stats'] = r.data.amount.toLocaleString() + ' ￦'
            metas.value[3]['stats'] = r.data.profit.toLocaleString() + ' ￦'
            metas.value[0]['subtitle'] = r.data.appr.count.toLocaleString() + '건'
            metas.value[1]['subtitle'] = r.data.cxl.count.toLocaleString() + '건'
            metas.value[2]['subtitle'] = r.data.count.toLocaleString() + '건'
            metas.value[3]['subtitle'] = r.data.count.toLocaleString() + '건'
            metas.value[0]['percentage'] = r.data.appr.amount ? 100 : 0
            metas.value[1]['percentage'] = store.getPercentage(r.data.cxl.amount, r.data.appr.amount)
            metas.value[2]['percentage'] = store.getPercentage(r.data.amount, r.data.appr.amount)
            metas.value[3]['percentage'] = store.getPercentage(r.data.profit, r.data.appr.amount)
        }
    })
})
watchEffect(() => {
    store.setChartProcess()
    store.params.level = store.params.level
})
</script>
<template>
    <div>
        <BaseIndexView placeholder="MID, TID, 승인번호, 거래번호, 결제모듈 별칭, 주민번호, 사업자번호, 발급사, 매입사 검색" :metas="metas" :add="user_info.level >= 35" add_name="매출"
            :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #sales_extra_field>
                        <VCol cols="12" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.level" :items="getAllLevels()"
                                :label="`등급 선택`" item-title="title" item-value="id" create />
                        </VCol>
                    </template>
                    <template #pg_extra_field>
                        <VCol cols="12" sm="3" v-if="getUserLevel() >= 35">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.mcht_settle_type"
                                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산타입 필터" item-title="name"
                                item-value="id" />
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="tabler-calculator" @click="batchRetry()" v-if="getUserLevel() >= 50">
                    일괄 재발송
                </VBtn>
            </template>
            <template #headers>
                <tr>
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                        <template v-if="key == 'total_trx_amount'">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'총 거래 수수료 = 금액 - (거래 수수료 + 유보금 + 입금 수수료)'">
                            </BaseQuestionTooltip>
                        </template>
                        <template v-else-if="key == 'mcht_settle_fee'">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'입금 수수료는 가맹점만 적용됩니다.'">
                            </BaseQuestionTooltip>
                        </template>
                        <template v-else-if="key == 'hold_amount'">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'유보금은 가맹점만 적용됩니다.'">
                            </BaseQuestionTooltip>
                        </template>
                        <template v-else>
                            <VCheckbox v-model="all_selected" label="선택/취소" class="check-label" v-if="key == 'id' && getUserLevel() >= 50"/>
                            <span v-else>
                                {{ header.ko }}
                            </span>
                        </template>
                    </th>
                </tr>
            </template>
            <template #body>
                <tr v-for="(item, index) in store.getItems" :key="item['id']">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_key">
                        <td v-if="_header.visible" :style="item['is_cancel'] ? 'color:red;' : ''" class='list-square'>
                            <span v-if="_key == 'id'">
                                <div style="display: inline-flex; align-items: center; vertical-align: middle;">
                                    <VCheckbox v-if="getUserLevel() >= 50" v-model="selected" :value="item[_key]" class="check-label"/>
                                    <span class="edit-link" @click="store.edit(item['id'])">#{{ item[_key] }}</span>
                                </div>
                            </span>
                            <span v-else-if="_key == 'module_type'">
                                <VChip :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                    {{ module_types.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'installment'">
                                {{ installments.find(inst => inst['id'] === item[_key])?.title }}
                            </span>
                            <span v-else-if="_key == 'pg_id'">
                                {{ pgs.find(pg => pg['id'] === item[_key])?.pg_name }}
                            </span>
                            <span v-else-if="_key == 'ps_id'">
                                {{ pss.find(ps => ps['id'] === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'mcht_settle_type'">
                                {{ settle_types.find(settle_type => settle_type['id'] === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'terminal_id'">
                                {{ terminals.find(terminal => terminal['id'] === item[_key])?.name }}
                            </span>
                            <span v-else-if="isSalesCol(_key as string)">
                                {{ Number(item[_key]).toLocaleString() }}
                            </span>
                            <span v-else-if="_key.toString().includes('_fee') && _key != 'mcht_settle_fee'">
                                <VChip v-if="item[_key]">
                                    {{ (item[_key] * 100).toFixed(3) }} %
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'pay_cond_price'">
                                {{ item['settle_fee'] }}
                            </span>
                            <span v-else-if="_key == 'custom_id'">
                                {{ cus_filters.find(cus => cus.id === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'realtime_result'">
                                <VChip :color="store.getSelectIdColor(realtimeResult(item))">
                                    {{ realtimeMessage(item) }}
                                    <br>
                                    {{ realtimeRetryMessage(item) }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'extra_col'">
                                <ExtraMenu :item="item"></ExtraMenu>
                            </span>
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
        <SalesSlipDialog ref="salesslip" :pgs="pgs" />
        <CancelTransDialog ref="cancelTran" />
        <RealtimeHistoriesDialog ref="realtimeHistories" />
    </div>
</template>
