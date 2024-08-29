<script setup lang="ts">
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { useSearchStore } from '@/views/merchandises/fee-change-histories/useStore'
import { useRequestStore } from '@/views/request'
import { selectFunctionCollect } from '@/views/selected'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const alert = <any>(inject('alert'))
const { request, remove } = useRequestStore()
const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)

provide('store', store)
provide('head', head)
provide('exporter', exporter)

const isFeeColumn = (key: string) => {
    return key.includes('_trx_fee') || key.includes('_hold_fee')
}

const batchRemove = async() => {
    if (await alert.value.show(`정말 ${selected.value.length}개의 이력을 삭제하시겠습니까?`)) {
        const r = await request({ url: `/api/v1/manager/merchandises/fee-change-histories/batch-remove`, method: 'delete', data: {
            selected_idxs: selected.value
        } }, true)
        selected.value = []
    }
}
</script>
<template>
    <BaseIndexView placeholder="가맹점명 검색" :metas="[]" :add="false" add_name="가맹점" :date_filter_type="DateFilters.DATE_RANGE">
        <template #index_extra_field>
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                :items="[10, 20, 30, 50, 100, 200]" label="조회 개수" id="page-size-filter" eager @update:modelValue="store.updateQueryString({page_size: store.params.page_size})"
                :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''"/>

            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.change_status" density="compact" variant="outlined"
                :items="[{ id: null, title: '전체' },{ id: 0, title: '변경예약' },{ id: 1, title: '변경완료' }]" label="변경 상태"  @update:modelValue="store.updateQueryString({change_status: store.params.change_status})" 
                :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''" item-title="title" item-value="id"/>

            <VBtn type="button" color="error" @click="batchRemove()" style="float: inline-end;" size="small" v-if="getUserLevel() >= 35"
                :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''">
                일괄삭제
                <VIcon size="18" icon="tabler-trash" />
            </VBtn>
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
                    <div class='check-label-container' v-if="key == 'id'">
                        <VCheckbox v-model="all_selected" class="check-label" />
                        <span>선택/취소</span>
                    </div>
                    <span v-else>
                        {{ header.ko }}
                    </span>
                </th>
            </tr>
        </template>
        <template #body>
            <tr v-for="(item, index) in store.getItems" :key="index">
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
                            <span v-if="_key == 'id'">
                                <div
                                    class='check-label-container'>
                                    <VCheckbox v-model="selected" :value="item[_key]" class="check-label" />
                                    <span>#{{ item[_key] }}</span>
                                </div>
                            </span>
                            <span v-else-if="_key == `change_status`">
                                <VChip :color="store.booleanTypeColor(!item[_key])" >
                                    {{ item[_key] ? '변경완료' : '변경예약' }}
                                </VChip>
                            </span>
                            <span v-else-if="isFeeColumn(_key)"> 
                                <VChip v-if="item[_key]" :color="_key.includes('aft_') ? 'primary': 'default'">
                                    {{ item[_key] + "%" }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'remove'">
                                <VBtn size="small" type="button" color="error" @click="remove('/merchandises/fee-change-histories', item, false)">
                                    삭제
                                    <VIcon size="22" icon="tabler-trash"/>
                                </VBtn>
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
