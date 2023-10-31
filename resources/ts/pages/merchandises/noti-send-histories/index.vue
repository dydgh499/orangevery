<script setup lang="ts">
import { useSearchStore } from '@/views/merchandises/noti-send-histories/useStore'
import { useRequestStore } from '@/views/request'
import { selectFunctionCollect } from '@/views/selected'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import ExtraMenu from '@/views/merchandises/noti-send-histories/ExtraMenu.vue'
import NotiDetailDialog from '@/layouts/dialogs/NotiDetailDialog.vue'
import { module_types } from '@/views/merchandises/pay-modules/useStore'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { post } = useRequestStore()
const notiDetail = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)
provide('notiDetail', notiDetail)
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))

const httpCodeColor = (http_code: number) => {
    if (http_code < 300)
        return "success"
    else if (http_code < 500)
        return "warning"
    else
        return "error"
}
const batchRetry = async () => {
    if (await alert.value.show('정말 일괄 재발송하시겠습니까?')) {
        const params = { selected: selected.value }
        const url = '/api/v1/manager/merchandises/noti-send-histories/batch-retry'
        const r = await post(url, params)
        if (r.status == 201)
            snackbar.value.show('성공하였습니다.', 'success')
        else
            snackbar.value.show(r.data.message, 'error')
        store.setTable()
    }
}
</script>
<template>
    <div>
        <BaseIndexView placeholder="MID, TID, 승인번호 검색" :metas="[]" :add="false" add_name="가맹점"
            :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #pg_extra_field>
                        <VCol cols="12" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.module_type"
                                :items="[{ id: null, title: '전체' }].concat(module_types)" label="모듈타입 필터" item-title="title"
                                item-value="id" />
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="tabler-calculator" @click="batchRetry()">
                    일괄 재발송
                </VBtn>
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
                                <span v-if="_key == 'id'">
                                    <div
                                        class='check-label-container'>
                                        <VCheckbox v-model="selected" :value="item[_key]" class="check-label" />
                                        <span>#{{ item[_key] }}</span>
                                    </div>
                                </span>
                                <span v-else-if="_key == `trans_id`">
                                    #{{ item[_key] }}
                                </span>
                                <span v-else-if="_key == 'module_type'">
                                    <VChip
                                        :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                        {{ module_types.find(module_type => module_type['id'] === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == `is_cancel`">
                                    <VChip :color="store.booleanTypeColor(item[_key])">
                                        {{ item[_key] ? '취소' : '승인' }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == `http_code`">
                                    <VChip :color="httpCodeColor(item[_key])">
                                        {{ item[_key] }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'extra_col'">
                                    <ExtraMenu :item="item"></ExtraMenu>
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
        <NotiDetailDialog ref="notiDetail" />
    </div>
</template>
