<script setup lang="ts">
import OperatorHistoryDetailDialog from '@/layouts/dialogs/histories/OperatorHistoryDetailDialog.vue'
import ImageDialog from '@/layouts/dialogs/utils/ImageDialog.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { history_types, useSearchStore } from '@/views/services/operator-histories2/useStore'
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
                    <template v-for="(sub_header, index) in head.getSubHeaderComputed" :key="index">
                        <th :colspan="head.getSubHeaderComputed.length - 1 == index ? sub_header.width + 1 : sub_header.width"
                            class='list-square sub-headers' v-show="sub_header.width">
                            <span>{{ sub_header.ko }}</span>
                        </th>
                    </template>
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
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key == `oper_id`">
                                #{{ item[_key] }}
                            </span>                                
                            <span v-else-if="_key === 'profile_img'">
                                <VAvatar :image="item[_key]" class="me-3 preview" @click="showAvatar(item['profile_img'])"/>
                            </span>
                            <span v-else-if="_key === 'history_target'">
                                <div style=" display: flex; min-width: 500px; flex-wrap: wrap;">
                                    <VChip v-for="(target, index) in (item[_key] ? item[_key].split(',') : [])"
                                        :color="'primary'" style="margin: 0.25em;">
                                            {{ target }}
                                    </VChip>
                                </div>
                            </span>
                            <b v-else-if="(['add_count', 'modify_count', 'remove_count']).includes(_key)"
                                class="text-error">
                                {{ item[_key] }}
                            </b>
                            <span v-else-if="_key === `detail_view`">
                                <VBtn size="small" variant="tonal" @click="operDetail.show(item)">상세보기</VBtn>
                            </span>
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
        <OperatorHistoryDetailDialog ref="operDetail" />
        <ImageDialog ref="imageDialog" :style="`inline-size:20em !important;`"/>
    </div>
</template>
