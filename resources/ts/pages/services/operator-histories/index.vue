<script setup lang="ts">
import { useSearchStore, history_types } from '@/views/services/operator-histories/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import ExtraMenu from '@/views/services/operator-histories/ExtraMenu.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'

const { store, head, exporter } = useSearchStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <div>
        <BaseIndexView placeholder="운영자명, 적용대상 검색" :metas="[]" :add="false" add_name="" :is_range_date="true">
            <template #filter>
            </template>
            <template #index_extra_field>
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                    :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" :eager="true" />
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
                        </template>
                        <template v-else>
                            <td v-show="_header.visible" class='list-square'>
                                <span v-if="_key == `id`">
                                    #{{ item[_key] }}
                                </span>                                
                                <span v-else-if="_key == 'profile_img'">
                                    <VAvatar :image="item[_key]" class="me-3" />
                                </span>
                                <span v-else-if="_key == `history_type`">
                                    <VChip
                                        :color="store.getSelectIdColor(history_types.find(obj => obj.id === item[_key])?.id)">
                                        {{ history_types.find(obj => obj.id === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'extra_col'">
                                    <ExtraMenu :item="item"/>
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
    </div>
</template>
