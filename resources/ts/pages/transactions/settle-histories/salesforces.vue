<script setup lang="ts">
import { useSearchStore } from '@/views/transactions/settle-histories/useSalesforceStore'
import ExtraMenu from '@/views/transactions/settle-histories/ExtraMenu.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { salesLevels } from '@/views/salesforces/useStore'


const { store, head, exporter } = useSearchStore()
const all_sales = salesLevels()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

store.params.level = all_sales[0].id
const metas = [
    {
        icon: 'tabler-user',
        color: 'primary',
        title: '금월 추가된 가맹점',
        stats: '21,459',
        percentage: +29,
        subtitle: 'Total Users',
    },
    {
        icon: 'tabler-user-plus',
        color: 'error',
        title: '금주 추가된 가맹점',
        stats: '4,567',
        percentage: +18,
        subtitle: 'Last week analytics',
    },
    {
        icon: 'tabler-user-check',
        color: 'success',
        title: '금월 감소한 가맹점',
        stats: '19,860',
        percentage: -14,
        subtitle: 'Last week analytics',
    },
    {
        icon: 'tabler-user-exclamation',
        color: 'warning',
        title: '금주 감소한 가맹점',
        stats: '237',
        percentage: +42,
        subtitle: 'Last week analytics',
    },
]
</script>
<template>
    <BaseIndexView placeholder="영업점 상호 검색" :metas="metas" :add="false" add_name="정산" :is_range_date="true">
        <template #filter>
            <BaseIndexFilterCard :pg="false" :ps="false" :pay_cond="false" :terminal="false" :cus_filter="false"
                :sales="false">
                <template #extra_left>
                    <VCol cols="12" sm="3">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.level"
                            :items="[{ id: null, title: '전체' }].concat(salesLevels())" :label="`등급 선택`" item-title="title"
                            item-value="id" create />
                    </VCol>
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
            <tr v-for="(item, index) in store.items" :key="index" style="height: 3.75rem;">
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
                            <span v-if="_key === 'id'" class="edit-link">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key.toString().includes('amount')" style="font-weight: bold;">
                                {{ (item[_key] as number).toLocaleString() }}
                            </span>
                            <span v-else-if="_key == 'level'">
                                <VChip :color="store.getSelectIdColor(all_sales.find(obj => obj.id === item[_key])?.id)">
                                    {{ all_sales.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key === 'deposit_status'">
                                {{ item[_key] ? '입금완료' : '미입금' }}
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
