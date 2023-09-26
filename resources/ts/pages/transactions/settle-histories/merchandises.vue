<script setup lang="ts">
import { useSearchStore } from '@/views/transactions/settle-histories/useMerchandiseStore'
import { selectFunctionCollect } from '@/views/selected'
import { settlementHistoryFunctionCollect } from '@/views/transactions/settle-histories/SettleHistory'
import ExtraMenu from '@/views/transactions/settle-histories/ExtraMenu.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { getUserLevel } from '@axios'

const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { batchDeposit, batchCancel } = settlementHistoryFunctionCollect(store)

provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <BaseIndexView placeholder="가맹점 상호 검색" :metas="[]" :add="false" add_name="정산" :is_range_date="true">
        <template #filter>
            <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="true" :sales="true" />
        </template>
        <template #index_extra_field>
            <VBtn prepend-icon="tabler:report-money" @click="batchDeposit(selected, true)" v-if="getUserLevel() >= 35">
                일괄 입금/미입금처리
            </VBtn>
            <VBtn prepend-icon="tabler:device-tablet-cancel" @click="batchCancel(selected, true)" v-if="getUserLevel() >= 35" color="error">
                일괄 정산취소
            </VBtn>
        </template>
        <template #headers>
            <tr>
                <th v-for="(colspan, index) in head.getColspansComputed" :colspan="colspan" :key="index" class='list-square'>
                    <span>
                        {{ head.main_headers[index] }}
                    </span>
                </th>
            </tr>
            <tr>
                <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>                    
                    <VCheckbox v-model="all_selected" :label="`${header.ko}`" class="check-label" v-if="key == 'id' && getUserLevel() >= 35" style="min-width: 1em;"/>
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
                        <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible" class='list-square'>
                            <span>
                                {{ item[_key][__key] }}
                            </span>
                        </td>
                    </template>
                    <template v-else>
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key === 'id'">
                                <VCheckbox v-model="selected" :value="item[_key]" :label="`#${item[_key]}`" class="check-label" style="min-inline-size: 1em;" v-if="getUserLevel() >= 35"/>
                                <span v-else> #{{ item[_key] }}</span>
                            </span>
                            <span v-else-if="(_key).toString().includes('amount') || (_key).toString().includes('_fee')" style="font-weight: bold;">
                                {{ (item[_key] as number).toLocaleString() }}
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
                </template>
            </tr>
        </template>
    </BaseIndexView>
</template>
