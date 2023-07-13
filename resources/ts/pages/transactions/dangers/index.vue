<script setup lang="ts">
import { useSearchStore } from '@/views/transactions/dangers/useStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'

const { store, head, exporter } = useSearchStore()
provide('store', store)
provide('head', head)
provide('exporter', exporter)
const metas = [
    {
        icon: 'jam-triangle-danger',
        color: 'primary',
        title: '금월 발생한 이상거래',
        stats: '0',
        percentage: +0,
    },
    {
        icon: 'jam-triangle-danger',
        color: 'error',
        title: '금주 발생한 이상거래',
        stats: '0',
        percentage: +0,
    },
    {
        icon: 'jam-triangle-danger',
        color: 'success',
        title: '금월 감소한 이상거래',
        stats: '0',
        percentage: -0,
    },
    {
        icon: 'jam-triangle-danger',
        color: 'warning',
        title: '금주 감소한 이상거래',
        stats: '0',
        percentage: +0,
    },
]
</script>
<template>
    <BaseIndexView placeholder="가맹점 상호, MID, TID, 승인번호 검색" :metas="metas" :add="false" add_name="가맹점" :is_range_date="false">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :pay_cond="true" :terminal="true" :cus_filter="true" :sales="true" />
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
                            <span v-if="_key == `id`" class="edit-link">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == 'amount'">
                                {{ (item[_key] as number).toLocaleString() }}
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
