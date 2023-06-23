<script setup lang="ts">
import { useSearchStore } from '@/views/salesforces/useStore'
import { settleCycles } from '@/views/salesforces/useStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue';
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import { salesLevels } from '@/views/salesforces/useStore';

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
const getSalesTypeColor = (_class: number) => {
    const id = all_sales.find(item => item.id === _class)?.id
    if (id == 0)
        return "default"
    else if (id == 1)
        return "primary"
    else if (id == 2)
        return "success"
    else if (id == 3)
        return "info"
    else if (id == 4)
        return "warning"
    else if (id == 5)
        return "error"
    else
        return 'default';
}
</script>
<template>
    <BaseIndexView placeholder="ID, 대표자명 검색" :metas="metas" :add="true" add_name="영업점" :is_range_date="true">
        <template #filter>
            <BaseIndexFilterCard :pg="false" :ps="false" :pay_cond="false" :terminal="false" :cus_filter="false"
                :sales="false">
                <template #extra_left>
                    <VCol cols="12" sm="3">
                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.level"
                            :items="[{ id: null, title: '등급 선택' }].concat(salesLevels())" :label="`등급 선택`"
                            item-title="title" item-value="id" />
                    </VCol>
                    <VCol cols="12" sm="3">
                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.settle_cycle"
                            :items="[{ id: null, title: '정산주기 선택' }].concat(settleCycles())" :label="`정산주기 선택`"
                            item-title="name" item-value="id" />
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
                <th v-for="(header, key) in head.flat_headers" :key="key" v-show="!header.hidden" class='list-square'>
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
                        <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="!__header.hidden" class='list-square'>
                            <span>
                                {{ item[_key][__key] }}
                            </span>
                        </td>
                    </template>
                    <template v-else>
                        <td v-show="!_header.hidden" class='list-square'>
                            <span v-if="_key == 'id'" class="edit-link" @click="store.edit(item['id'])">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == 'user_name'" class="edit-link" @click="store.edit(item['id'])">
                                {{ item[_key] }}
                            </span>
                            <span v-else-if="_key == 'level'">
                                <VChip :color="getSalesTypeColor(item[_key])">
                                    {{ all_sales.find(sales => sales.id === item[_key])?.title }}
                                </VChip>
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
