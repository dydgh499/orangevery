<script setup lang="ts">
import { useSearchStore } from '@/views/salesforces/fee-change-histories/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import { allLevels } from '@/views/salesforces/useStore';

const { store, head, exporter } = useSearchStore()
provide('store', store)
provide('head', head)
provide('exporter', exporter)
provide('store', store)

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
const all_levels = allLevels()
</script>
<template>
    <BaseIndexView placeholder="영업점명 검색" :metas="metas" :add="false" add_name="가맹점" :is_range_date="true">
        <template #filter>
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
                        <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="!__header.hidden"
                            class='list-square'>
                            <span>
                                {{ item[_key][__key] }}
                            </span>
                        </td>
                    </template>
                    <template v-else>
                        <td v-show="!_header.hidden" class='list-square'>
                            <span v-if="_key == 'id'" class="edit-link">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == `level`">
                                {{ all_levels.find(level => level.id === item[_key])?.title }}
                            </span>
                            <span v-else-if="_key == `change_status`">
                                <VChip :color="store.booleanTypeColor(item[_key])">
                                    {{ item[_key] ? '변경예약' : '변경완료' }}
                                </VChip>
                            </span>
                            <span v-else-if="_key.includes('_fee')">
                                <VChip>
                                    {{ item[_key] + "%" }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == `level`">
                                {{ all_levels.find(level => level.id === item[_key])?.title }}
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
