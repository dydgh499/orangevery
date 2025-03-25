<script setup lang="ts">
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import { useSearchStore } from '@/views/merchandises/bill-keys/useStore';
import { useRequestStore } from '@/views/request';
import { selectFunctionCollect } from '@/views/selected';
import { useStore } from '@/views/services/pay-gateways/useStore';
import { DateFilters } from '@core/enums';

const alert = <any>(inject('alert'))
const { request, remove } = useRequestStore()
const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { pgs, pss, settle_types, terminals, cus_filters } = useStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

const batchRemove = async() => {
    if (await alert.value.show(`정말 ${selected.value.length}개의 빌키를 삭제하시겠습니까?`)) {
        const r = await request({ url: `/api/v1/manager/merchandises/bill-keys/batch-updaters/remove`, method: 'delete', data: {
            selected_idxs: selected.value
        } }, true)
        selected.value = []
    }
}
</script>
<template>
    <div>
        <BaseIndexView :placeholder="'카드번호 검색'" :metas="[]" :add="false" add_name="빌키"
            :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
            </template>
            <template #index_extra_field>
                <VBtn type="button" color="error" @click="batchRemove()" style="float: inline-end;" size="small"
                    :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''" item-title="title" item-value="id">
                    일괄삭제
                    <VIcon size="18" icon="tabler-trash" />
                </VBtn>
            </template>
            <template #headers>
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
                            <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible"
                                class='list-square'>
                                <span>
                                    {{ item[_key][__key] }}
                                </span>
                            </td>
                        </template>
                        <template v-else>
                            <td v-show="_header.visible" class='list-square'>
                                <span v-if="_key === 'id'">
                                    <div
                                        class='check-label-container'>
                                        <VCheckbox v-model="selected" :value="item[_key]" class="check-label" />
                                        <span>#{{ item[_key] }}</span>
                                    </div>
                                </span>
                                <span v-else-if="_key == 'pg_id'">
                                    {{ pgs.find(pg => pg['id'] === item[_key])?.pg_name }}
                                </span>
                                <span v-else-if="_key === 'is_send_email'">
                                    <VChip :color="item[_key] ? 'success' : 'default'">
                                        {{ item[_key] ? '발송' : '미발송' }}
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
    </div>
</template>
