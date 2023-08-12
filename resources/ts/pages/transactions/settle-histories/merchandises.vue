<script setup lang="ts">
import { useSearchStore } from '@/views/transactions/settle-histories/useMerchandiseStore'
import ExtraMenu from '@/views/transactions/settle-histories/ExtraMenu.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'

const { store, head, exporter } = useSearchStore()
provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <BaseIndexView placeholder="가맹점 상호 검색" :metas="[]" :add="false" add_name="정산" :is_range_date="true">
        <template #filter>
            <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="true" :sales="true" />
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
                    <span>
                        {{ header.ko }}
                    </span>
                </th>
            </tr>
        </template>
        <template #body>
            <tr v-for="(item, index) in store.items" :key="index" style="height: 3.75rem;">
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
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="(_key).toString().includes('amount')" style="font-weight: bold;">
                                {{ (item[_key] as number).toLocaleString() }}
                            </span>
                            <span v-else-if="_key === 'deposit_status'">
                                {{ item[_key] ? '입금완료' : '미입금' }}
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
