<script setup lang="ts">
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useSearchStore } from '@/views/transactions/realtime-histories/useStore'
import type { RealtimeHistory } from '@/views/types'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const { finance_vans } = useStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

const getLogStyle = (item: RealtimeHistory) => {
    if(item.result_code === '0000' && item.request_type === 6170)
        return 'color:blue';
    else if(item.result_code !== '0000' && item.request_type === 6170)
        return 'color:red';
    else
        return '';
}
</script>
<template>
    <BaseIndexView placeholder="가맹점 상호, 계좌번호, 승인번호 검색" :metas="[]" :add="false" add_name="실시간 이체 이력" :date_filter_type="DateFilters.DATE_RANGE">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true" :sales="true">
            </BaseIndexFilterCard>
        </template>
        <template #index_extra_field>
            <table>
                <tr v-for="(finance_van, key) in finance_vans.filter(t => t.is_agency_van === 0)" :key="key" :style="finance_van.balance_status ? '' : 'color:red'">
                    <th style="text-align: start;">{{ finance_van.nick_name }} 잔액: </th>
                    <td style="text-align: end;">{{ finance_van.balance ? finance_van.balance.toLocaleString() : 0 }} &#8361;</td>
                </tr>
            </table>
        </template>
        <template #headers>
            <tr>
                <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                    <span>
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
                        <td v-show="_header.visible" class='list-square' :class="getLogStyle(item)">
                            <span v-if="_key == 'id' || _key == 'trans_id'">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == 'amount'">
                                {{ item[_key] ? (item[_key] as number).toLocaleString() : 0 }}
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
