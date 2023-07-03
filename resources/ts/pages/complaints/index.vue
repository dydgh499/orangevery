<script setup lang="ts">
import { useSearchStore } from '@/views/complaints/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { complaint_types } from '@/views/complaints/useStore'

const { store, head, exporter } = useSearchStore()
provide('store', store)
provide('head', head)
provide('exporter', exporter)

const metas = []
</script>
<template>
    <BaseIndexView placeholder="TID 검색" :metas="[]" :add="true" add_name="민원" :is_range_date="null">
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
                    </template>
                    <template v-else>
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key == `id`" class="edit-link" @click="store.edit(item['id'])">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == `type`">
                                <VChip :color="store.getSelectIdColor(complaint_types.find(types => types.id === item[_key])?.id)">
                                    {{ complaint_types.find(types => types.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == `is_deposit`">
                                <VChip :color="store.booleanTypeColor(!item[_key])">
                                    {{ item[_key] ? '입금' : '미입금' }}
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
