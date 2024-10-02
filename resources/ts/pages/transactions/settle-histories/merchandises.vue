<script setup lang="ts">
import SettleHistoryBatchDialog from '@/layouts/dialogs/SettleHistoryBatchDialog.vue'
import FinanceVanDialog from '@/layouts/dialogs/services/FinanceVanDialog.vue'
import AddDeductDialog from '@/layouts/dialogs/transactions/AddDeductDialog.vue'

import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { selectFunctionCollect } from '@/views/selected'
import ExtraMenu from '@/views/transactions/settle-histories/ExtraMenu.vue'
import { getDepositsStatusColor } from '@/views/transactions/settle-histories/SettleHistory'
import { deposit_statuses, useSearchStore } from '@/views/transactions/settle-histories/useMerchandiseStore'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'
import corp from '@corp'

const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const totals = ref(<any[]>([]))
const financeDialog = ref()
const addDeductDialog = ref()
const settleHistoryBatchDialog = ref()

store.params.use_finance_van_deposit = Number(corp.pv_options.paid.use_finance_van_deposit)

provide('store', store)
provide('head', head)
provide('exporter', exporter)
provide('financeDialog', financeDialog)
provide('addDeductDialog', addDeductDialog)

const isNumberFormatCol = (_key: string) => {
    return _key.includes('amount') || _key.includes('_fee') || _key.includes('_deposit') || _key.includes('_count')
}

onMounted(() => {
    watchEffect(async () => {
        if (store.getChartProcess() === false) {
            const r = await store.getChartData()
            totals.value = [r.data]
        }
    })
})
</script>
<template>
    <div>
        <BaseIndexView placeholder="가맹점 상호 검색" :metas="[]" :add="false" add_name="정산"
            :date_filter_type="DateFilters.SETTLE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="true"
                    :sales="true">
                    <template #pg_extra_field>
                        <VCol cols="6" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.deposit_status" :items="deposit_statuses"
                                label="입금 타입" item-title="title" item-value="id"
                                @update:modelValue="[store.updateQueryString({ deposit_status: store.params.deposit_status })]" />
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>                
                <VBtn prepend-icon="carbon:batch-job" @click="settleHistoryBatchDialog.show()" v-if="getUserLevel() >= 35" color="primary" size="small">
                    일괄작업
                </VBtn>
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
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                        <div class='check-label-container' v-if="key == 'id' && getUserLevel() >= 35">
                            <VCheckbox v-model="all_selected" class="check-label" />
                            <span>선택/취소</span>
                        </div>
                        <span v-else>
                            {{ header.ko }}
                        </span>
                    </th>
                </tr>
            </template>
            <template #body>
                <!-- chart -->
                <tr v-for="(item, key) in totals" :key="key">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key === 'id'">합계</span>
                            <span v-else-if="isNumberFormatCol(_key.toString())" style="font-weight: bold;">
                                {{ item[_key] ? parseInt(item[_key])?.toLocaleString() : 0 }}
                            </span>
                        </td>
                    </template>
                </tr>
                <!-- normal -->
                <tr v-for="(item, index) in store.getItems" :key="index">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key === 'id'">
                                <div class='check-label-container' v-if="getUserLevel() >= 35">
                                    <VCheckbox v-model="selected" :value="item[_key]" class="check-label" />
                                    <span>#{{ item[_key] }}</span>
                                </div>
                                <span v-else> #{{ item[_key] }}</span>
                            </span>
                            <span v-else-if="isNumberFormatCol(_key.toString())" style="font-weight: bold;">
                                {{ item[_key] ? (item[_key] as number)?.toLocaleString() : 0}}
                            </span>
                            <span v-else-if="_key === 'deposit_status'">
                                <VChip :color="getDepositsStatusColor(item[_key])">
                                    {{ deposit_statuses.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key === 'extra_col'">
                                <ExtraMenu :id="item['id']" :name="item['mcht_name']" :is_mcht="true" :item="item"/>
                            </span>
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
        <FinanceVanDialog ref="financeDialog" />
        <AddDeductDialog ref="addDeductDialog" />
        <SettleHistoryBatchDialog ref="settleHistoryBatchDialog" :selected_idxs="selected" :store="store" :is_mcht="true"/>
    </div>
</template>
