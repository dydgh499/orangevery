<script setup lang="ts">
import { useSearchStore } from '@/views/transactions/realtime-histories/useStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import type { RealtimeHistory } from '@/views/types'

const { store, head, exporter } = useSearchStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

const getLogStyle = (item: RealtimeHistory) => {
    if(item.result_code == '0000' && item.key == '6170')
        return 'color:blue';
    else if(item.result_code != '0000' && item.key == '6170')
        return 'color:red';
    else
        return '';
}
</script>
<template>
    <BaseIndexView placeholder="가맹점 상호, 계좌번호, 승인번호 검색" :metas="[]" :add="false" add_name="실시간 이체 이력" :is_range_date="true">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true" :sales="true">
                <template #pg_extra_field>
                </template>
            </BaseIndexFilterCard>
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
                            <span>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </template>
            </tr>
        </template>
    </BaseIndexView>
</template>
