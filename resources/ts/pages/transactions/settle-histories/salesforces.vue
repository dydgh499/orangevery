<script setup lang="ts">
import { useSearchStore } from '@/views/transactions/settle-histories/useSalesforceStore'
import { selectFunctionCollect } from '@/views/selected'
import { settlementHistoryFunctionCollect } from '@/views/transactions/settle-histories/SettleHistory'
import ExtraMenu from '@/views/transactions/settle-histories/ExtraMenu.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { salesLevels, getLevelByIndex } from '@/views/salesforces/useStore'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { batchDeposit, batchCancel } = settlementHistoryFunctionCollect(store)
const all_sales = salesLevels()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <BaseIndexView placeholder="영업점 상호 검색" :metas="[]" :add="false" add_name="정산" :date_filter_type="DateFilters.DATE_RANGE">
        <template #filter>
        </template>
        <template #index_extra_field>            
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" eager @update:modelValue="[store.updateQueryString({page_size: store.params.page_size})]"/>
            <VBtn prepend-icon="tabler:report-money" @click="batchDeposit(selected, false)" v-if="getUserLevel() >= 35"  size="small">
                일괄 입금/미입금처리
            </VBtn>
            <VBtn prepend-icon="tabler:device-tablet-cancel" @click="batchCancel(selected, false)" v-if="getUserLevel() >= 35" color="error"  size="small">
                일괄 정산취소
            </VBtn>
        </template>
        <template #headers>
            <tr>
                <th v-for="(colspan, index) in head.getColspansComputed" :colspan="colspan" :key="index"
                    class='list-square'>
                    <span>
                        {{ head.main_headers[index] }}
                    </span>
                </th>
            </tr>
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
            <tr v-for="(item, index) in store.getItems" :key="index">
                <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                    <template v-if="head.getDepth(_header, 0) != 1">
                        <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible"
                            class='list-square'>
                            <span>
                                {{ item[_key][__key] }}
                            </span>
                        </td>
                    </template>
                    <template v-else>
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key === 'id'">
                                <div class='check-label-container' v-if="getUserLevel() >= 35">
                                    <VCheckbox v-model="selected" :value="item[_key]" class="check-label"/>
                                    <span>#{{ item[_key] }}</span>
                                </div>
                                <span v-else> #{{ item[_key] }}</span>
                            </span>
                            <span v-else-if="_key.toString().includes('amount')" style="font-weight: bold;">
                                {{ (item[_key] as number).toLocaleString() }}
                            </span>
                            <span v-else-if="_key == 'level'">
                                <VChip :color="store.getSelectIdColor(getLevelByIndex(item[_key]))">
                                    {{ all_sales.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key === 'deposit_status'">
                                <VChip :color="store.booleanTypeColor(!item[_key])">
                                    {{ item[_key] ? '입금완료' : '미입금' }}
                                </VChip>
                            </span>
                            <span v-else-if="_key === 'extra_col'">
                                <ExtraMenu :id="item['id']" :name="item['mcht_name']" :is_mcht="false" :item="item">
                                </ExtraMenu>
                            </span>
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </template>
            </tr>
        </template>
    </BaseIndexView>
</template>
