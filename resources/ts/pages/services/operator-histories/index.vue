<script setup lang="ts">
import ImageDialog from '@/layouts/dialogs/ImageDialog.vue'
import OperDetailDialog from '@/layouts/dialogs/OperDetailDialog.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import ExtraMenu from '@/views/services/operator-histories/ExtraMenu.vue'
import { history_types, useSearchStore } from '@/views/services/operator-histories/useStore'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()

const operDetail = ref()
const imageDialog = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)
provide('operDetail', operDetail)

const showAvatar = (preview: string) => {
    imageDialog.value.show(preview)
}

</script>
<template>
    <div>
        <BaseIndexView placeholder="운영자명, 적용대상, 상세이력 검색" :metas="[]" :add="false" add_name="" :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
            </template>
            <template #index_extra_field>
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                        :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})" />
                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.history_type" density="compact" variant="outlined" item-title="title" item-value="id"
                    :items="[{ id: null, title: '전체' }].concat(history_types)" label="활동 타입" eager  @update:modelValue="store.updateQueryString({history_type: store.params.history_type})" />
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
                        </template>
                        <template v-else>
                            <td v-show="_header.visible" class='list-square'>
                                <span v-if="_key == `id`">
                                    #{{ item[_key] }}
                                </span>                                
                                <span v-else-if="_key == 'profile_img'">
                                    <VAvatar :image="item[_key]" class="me-3 preview" @click="showAvatar(item['profile_img'])"/>
                                </span>
                                <span v-else-if="_key == `history_type`">
                                    <VChip
                                        :color="store.getSelectIdColor(history_types.find(obj => obj.id === item[_key])?.id as number)">
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
        <OperDetailDialog ref="operDetail" />
        <ImageDialog ref="imageDialog" :style="`inline-size:20em !important;`"/>
    </div>
</template>
