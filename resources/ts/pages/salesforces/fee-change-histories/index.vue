<script setup lang="ts">
import { useSearchStore } from '@/views/salesforces/fee-change-histories/useStore'
import ExtraMenu from '@/views/merchandises/fee-change-histories/ExtraMenu.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { allLevels } from '@axios'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
provide('store', store)
provide('head', head)
provide('exporter', exporter)
provide('store', store)

const all_levels = allLevels()
</script>
<template>
    <BaseIndexView placeholder="영업점 상호 검색" :metas="[]" :add="false" add_name="가맹점" :date_filter_type="DateFilters.DATE_RANGE">
        <template #index_extra_field>
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})" />
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
                        <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible"
                            class='list-square'>
                            <span>
                                {{ item[_key][__key] }}
                            </span>
                        </td>
                    </template>
                    <template v-else>
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key == 'id'">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == `level`">
                                {{ all_levels.find(level => level.id === item[_key])?.title }}
                            </span>
                            <span v-else-if="_key == `change_status`">
                                <VChip :color="store.booleanTypeColor(!item[_key])" >
                                    {{ item[_key] ? '변경완료' : '변경예약' }}
                                </VChip>
                            </span>
                            <span v-else-if="_key.includes('_fee')">
                                <VChip v-if="item[_key]" :color="_key.includes('aft_') ? 'primary': 'default'">
                                    {{ item[_key] + "%" }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == `level`">
                                {{ all_levels.find(level => level.id === item[_key])?.title }}
                            </span>
                            <span v-else-if="_key == 'extra_col'">
                                <ExtraMenu :item="item" :type="'salesforces'"></ExtraMenu>
                            </span>
                            <span v-else :class="_key.includes('aft_') ? 'text-primary': ''">
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </template>
            </tr>

        </template>
    </BaseIndexView>
</template>
