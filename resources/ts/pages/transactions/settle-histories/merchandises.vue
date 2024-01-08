<script setup lang="ts">
import { useSearchStore } from '@/views/transactions/settle-histories/useMerchandiseStore'
import { selectFunctionCollect } from '@/views/selected'
import { settlementHistoryFunctionCollect } from '@/views/transactions/settle-histories/SettleHistory'
import ExtraMenu from '@/views/transactions/settle-histories/ExtraMenu.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { batchDeposit, batchCancel } = settlementHistoryFunctionCollect(store)

provide('store', store)
provide('head', head)
provide('exporter', exporter)

const totals = ref(<any[]>([]))
const isNumberFormatCol = (_key: string) => {
    return _key.includes('amount') || _key.includes('_fee') || _key.includes('_deposit')
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
    <BaseIndexView placeholder="가맹점 상호 검색" :metas="[]" :add="false" add_name="정산" :date_filter_type="DateFilters.SETTLE_RANGE">
        <template #filter>
            <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="true" :sales="true" />
        </template>
        <template #index_extra_field>
            <VBtn prepend-icon="tabler:report-money" @click="batchDeposit(selected, true)" v-if="getUserLevel() >= 35"  size="small">
                일괄 입금/미입금처리
            </VBtn>
            <VBtn prepend-icon="tabler:device-tablet-cancel" @click="batchCancel(selected, true)" v-if="getUserLevel() >= 35" color="error"  size="small">
                일괄 정산취소
            </VBtn>
        </template>
        <template #headers>
            <tr>
                <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                    <div class='check-label-container' v-if="key == 'id' && getUserLevel() >= 35">
                        <VCheckbox v-model="all_selected" class="check-label"/>
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
                                {{ item[_key] ? parseInt(item[_key])?.toLocaleString() : 0}}
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
                                <VCheckbox v-model="selected" :value="item[_key]" class="check-label"/>
                                <span>#{{ item[_key] }}</span>
                            </div>
                            <span v-else> #{{ item[_key] }}</span>
                        </span>
                        <span v-else-if="isNumberFormatCol(_key.toString())" style="font-weight: bold;">
                            {{ (item[_key] as number)?.toLocaleString() }}
                        </span>
                        <span v-else-if="_key === 'deposit_status'">
                            <VChip :color="store.booleanTypeColor(!item[_key])">
                                {{ item[_key] ? '입금완료' : '미입금' }}
                            </VChip>
                        </span>
                        <span v-else-if="_key === 'extra_col'">
                            <ExtraMenu :id="item['id']" :name="item['mcht_name']" :is_mcht="true" :item="item">
                            </ExtraMenu>
                        </span>
                        <span v-else>
                            {{ item[_key] }}
                        </span>
                    </td>
                </template>
            </tr>
        </template>
    </BaseIndexView>
</template>
