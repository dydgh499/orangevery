<script setup lang="ts">
import { useSearchStore, complaint_types } from '@/views/complaints/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <BaseIndexView placeholder="TID 검색" :metas="[]" :add="true" add_name="민원" :date_filter_type="DateFilters.NOT_USE">
        <template #index_extra_field>
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" eager />
        </template>
        <template #headers>
            <tr>
                <template v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible">
                    <th :style="key == 'note' ? 'min-width: 50em;' : ''"  class='list-square'>
                        <span>
                            {{ header.ko }}
                        </span>
                    </th>
                </template>
            </tr>
        </template>
        <template #body>
            <tr v-for="(item, index) in store.getItems" :key="index">
                <template v-for="(_header, _key, _index) in head.headers" :key="_index">
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
            </tr>

        </template>
    </BaseIndexView>
</template>
