<script setup lang="ts">
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore'
import { useRequestStore } from '@/views/request'
import { selectFunctionCollect } from '@/views/selected'
import { useStore } from '@/views/services/pay-gateways/useStore'
import ExtraMenu from '@/views/transactions/dangers/ExtraMenu.vue'
import { danger_types, useSearchStore } from '@/views/transactions/dangers/useStore'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { post } = useRequestStore()
const { pgs, pss, terminals } = useStore()
const { settle_types } = useStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))

const batchCheck = async () => {
    if (await alert.value.show('정말 일괄 확인/확인취소 처리 하시겠습니까?')) {
        const params = {data:<any>([])}        
        for (let i = 0; i < selected.value.length; i++) {
            const item: any = store.items.find(obj => obj['id'] === selected.value[i])
            if (item)
                params.data.push({id: item.id, checked: !item.is_checked})
        }

        const res = await post(`/api/v1/manager/transactions/dangers/batch-checked`, params)
        snackbar.value.show('성공하였습니다.', 'success')
        store.setTable()
    }
}
</script>
<template>
    <BaseIndexView placeholder="가맹점 상호, MID, TID, 승인번호 검색" :metas="[]" :add="false" add_name="가맹점"
        :date_filter_type="DateFilters.DATE_RANGE">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true"
                :sales="true">
                <template #pg_extra_field>
                    <VCol cols="6" sm="3">
                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.mcht_settle_type"
                            :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산타입 필터" item-title="name"
                            item-value="id" @update:modelValue="store.updateQueryString({mcht_settle_type: store.params.mcht_settle_type})"/>
                    </VCol>
                </template>
            </BaseIndexFilterCard>
        </template>
        <template #index_extra_field>
            <VBtn prepend-icon="tabler:check" @click="batchCheck()" v-if="getUserLevel() >= 35"  size="small">
                일괄 확인/확인취소
            </VBtn>
        </template>
        <template #headers>
            <tr>
                <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                    <div class='check-label-container' v-if="key == 'id' && getUserLevel() >= 35">
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
                                <div class='check-label-container' v-if="getUserLevel() >= 35">
                                    <VCheckbox v-model="selected" :value="item[_key]" class="check-label" />
                                    <span>#{{ item[_key] }}</span>
                                </div>
                            </span>
                            <span v-else-if="_key == 'module_type'">
                                <VChip :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                    {{ module_types.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'installment'">
                                {{ installments.find(inst => inst['id'] === item[_key])?.title as string }}
                            </span>
                            <span v-else-if="_key == 'pg_id'">
                                {{ pgs.find(pg => pg['id'] === item[_key])?.pg_name as string }}
                            </span>
                            <span v-else-if="_key == 'ps_id'">
                                {{ pss.find(pg => pg['id'] === item[_key])?.name as string }}
                            </span>
                            <span v-else-if="_key == 'terminal_id'">
                                {{ terminals.find(inst => inst['id'] === item[_key])?.name as string }}
                            </span>
                            <span v-else-if="_key == 'amount'">
                                {{ (item[_key] as number).toLocaleString() }}
                            </span>
                            <span v-else-if="_key == `danger_type`">
                                <VChip :color="store.getSelectIdColor(item[_key])">
                                    {{ danger_types.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == `is_checked`">
                                <VChip :color="store.booleanTypeColor(!item[_key])">
                                    {{ item[_key] ? '확인' : '미확인' }}
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
    </BaseIndexView></template>
