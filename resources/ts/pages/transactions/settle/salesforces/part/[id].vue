<script setup lang="ts">
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { Searcher } from '@/views/searcher'
import { selectFunctionCollect } from '@/views/selected'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { partSettleStore } from '@/views/transactions/settle/part/partSettleStore'
import PartTotalChart from '@/views/transactions/settle/part/PartTotalChart.vue'
import { useSearchStore } from '@/views/transactions/settle/part/useSalesforceStore'
import { getProfitColName } from '@/views/transactions/transacitonsHeader'
import TransactionsIndexTd from '@/views/transactions/TransactionsIndexTd.vue'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'


const { head, table, metas } = useSearchStore()
const store = Searcher('transactions/settle/salesforces/part')
const { selected, dialog } = selectFunctionCollect(store)
const { settle, partSettle, exporter } = partSettleStore(store, selected, dialog, table, head, 'salesforces')
const { settle_types } = useStore()
const all_selected = ref()

const dataToChart = async() => {
    if (store.getChartProcess() === false && store.params.s_dt && store.params.e_dt) {
        const r = await store.getChartData()
        Object.assign(metas, table.dataToChart(metas, r, store))
    }
}

watchEffect(async () => {
    dataToChart()
})
watchEffect(() => {
    selected.value = all_selected.value ? store.getItems.value.map(item => item['id']) : []
})

provide('store', store)
provide('head', head)
provide('settle', settle)
provide('exporter', exporter)
provide('partSettle', partSettle)

</script>
<template>
    <div>
        <BaseIndexView placeholder="MID, TID, 승인번호, 거래번호 검색" :metas="metas" :add="false" add_name=""
            :date_filter_type="DateFilters.SETTLE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #pg_extra_field>
                        <VCol cols="6" sm="3" v-if="getUserLevel() >= 35">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.mcht_settle_type"
                                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산타입 필터" item-title="name"
                                item-value="id" @update:modelValue="[store.updateQueryString({mcht_settle_type: store.params.mcht_settle_type})]"/>
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <PartTotalChart/>
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
                        <span v-if="key == 'total_trx_amount'">
                            <BaseQuestionTooltip :location="'top'" :text="store.params.level === 10 ? (header.ko as string) : '총 지급액'"
                                :content="'총 거래 수수료 = 금액 - (거래 수수료 + 유보금 + 이체 수수료)'"/>
                        </span>
                        <span v-else-if="key == 'mcht_settle_fee' && store.params.level == 10">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'입금 수수료는 가맹점만 적용됩니다.'"/>
                        </span>
                        <span v-else-if="key == 'trx_amount'">
                            <span>{{ store.params.level === 10 ? header.ko : '지급액' }}</span>
                        </span>
                        <span v-else-if="key == 'profit'">
                            <span>{{ getProfitColName(store.params.level) }}</span>
                        </span>
                        <span v-else-if="key == 'hold_amount' && store.params.level == 10">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'유보금은 가맹점만 적용됩니다.'"/>
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
                <tr v-for="(item, index) in store.getItems.value" :key="index">
                    <VTooltip activator="parent" location="end" open-delay="250" transition="scroll-x-transition" v-if="$vuetify.display.smAndDown === false">
                        <span>{{ `${item['mcht_name']} (${item['is_cancel'] ? "취소" : "승인"})` }}</span>
                        <br>
                        <span>{{ `${item['amount'].toLocaleString()}원` }}</span>
                    </VTooltip>
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <td v-show="_header.visible" :style="item['is_cancel'] ? 'color:red;' : ''" class='list-square'>
                            <span v-if="_key == 'id'">
                                <div class='check-label-container'>
                                    <VCheckbox v-model="selected" :value="item[_key]" class="check-label"/>
                                    <span>#{{ item[_key] }}</span>
                                </div>
                            </span>
                            <TransactionsIndexTd v-else :item="item" :_key="_key"/>
                        </td>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
    </div>
</template>
