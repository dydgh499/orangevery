<script setup lang="ts">
import { useSearchStore } from '@/views/merchandises/pay-modules/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useRequestStore } from '@/views/request'
import { selectFunctionCollect } from '@/views/selected'
import { module_types, installments, fin_trx_delays, cxl_types } from '@/views/merchandises/pay-modules/useStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { DateFilters } from '@core/enums'
import { getUserLevel, isAbleModifyMcht } from '@axios'

const add_able = getUserLevel() >= 35 || isAbleModifyMcht()
const { request } = useRequestStore()
const { pgs, pss, settle_types, finance_vans } = useStore()
const { store, head, exporter, metas } = useSearchStore()
const { selected, all_selected, dialog } = selectFunctionCollect(store)

provide('store', store)
provide('head', head)
provide('exporter', exporter)

const batchDelete = async () => {
    const count = selected.value.length
    if (await dialog('정말 '+count+'개의 결제모듈을 일괄삭제 하시겠습니까?')) {
        const params = { selected: selected.value }
        const r = await request({ url: `/api/v1/manager/merchandises/pay-modules/batch-remove`, method: 'delete', params:params}, true)
        store.setChartProcess()
        store.setTable()
    }
}

onMounted(() => {
    watchEffect(async() => {
        if(store.getChartProcess() === false) {
            const r = await store.getChartData()
            metas[0]['stats'] = r.data.this_month_add.toLocaleString()
            metas[1]['stats'] = (r.data.this_month_del * -1).toLocaleString()
            metas[2]['stats'] = r.data.this_week_add.toLocaleString()
            metas[3]['stats'] = (r.data.this_week_del * -1).toLocaleString()  
            metas[0]['percentage'] = store.getPercentage(r.data.this_month_add, r.data.total)
            metas[1]['percentage'] = store.getPercentage((r.data.this_month_del * -1), r.data.total)
            metas[2]['percentage'] = store.getPercentage(r.data.this_week_add, r.data.total)
            metas[3]['percentage'] = store.getPercentage((r.data.this_week_del * -1), r.data.total)            
        }
    })
})
</script>
<template>
    <BaseIndexView placeholder="MID, TID, 가맹점 상호, 별칭 검색" :metas="metas" :add="add_able" add_name="결제모듈" :date_filter_type="DateFilters.NOT_USE">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="true" :terminal="true" :cus_filter="true" :sales="true">                
                <template #pg_extra_field>
                    <VCol cols="12" sm="3">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.module_type" :items="[{ id: null, title: '전체' }].concat(module_types)"
                             label="모듈타입 필터" item-title="title" item-value="id" @update:modelValue="store.updateQueryString({module_type: store.params.module_type})" />
                    </VCol>
                </template>
            </BaseIndexFilterCard>
        </template>
        <template #index_extra_field>            
            <VBtn prepend-icon="tabler:device-tablet-cancel" @click="batchDelete()" v-if="getUserLevel() >= 35" color="error" size="small">
                일괄 삭제
            </VBtn>
            <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.un_use" label="최근 1달 미결제 결제모듈 조회" color="warning" @update:modelValue="store.updateQueryString({un_use: store.params.un_use})" />
        </template>
        <template #headers>
            <tr>
                <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                    <div class='check-label-container' v-if="key == 'id' && getUserLevel() >= 35">
                        <VCheckbox v-model="all_selected" class="check-label"/>
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
                                <div class='check-label-container'>
                                    <VCheckbox v-if="getUserLevel() >= 35" v-model="selected" :value="item[_key]" class="check-label"/>
                                    <span class="edit-link" @click="store.edit(item['id'])">#{{ item[_key] }}</span>
                                </div>
                            </span>
                            <span v-else-if="_key == 'note'" class="edit-link" @click="store.edit(item['id'])">
                                {{ item[_key] }}
                            </span>
                            <span v-else-if="_key == 'module_type'">
                                <VChip :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                    {{ module_types.find(module_type => module_type['id'] === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'installment'">
                                {{ installments.find(inst => inst['id'] === item[_key])?.title }}
                            </span>
                            <span v-else-if="_key == 'pg_id'">
                                {{ pgs.find(pg => pg['id'] === item[_key])?.pg_name }}
                            </span>
                            <span v-else-if="_key == 'ps_id'">
                                {{ pss.find(ps => ps['id'] === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'settle_type'">
                                {{ settle_types.find(settle_type => settle_type['id'] === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'cxl_type'">
                                <VChip :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                    {{ cxl_types.find(settle_type => settle_type['id'] === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'use_realtime_deposit'">
                                <VChip :color="store.booleanTypeColor(!item[_key])">
                                    {{ item[_key] ? '사용' : '미사용' }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'fin_id'">
                                {{ finance_vans.find(settle_type => settle_type['id'] === item[_key])?.nick_name }}
                            </span>
                            <span v-else-if="_key == 'fin_trx_delay'">
                                {{ fin_trx_delays.find(settle_type => settle_type['id'] === item[_key])?.title }}
                            </span>
                            <span v-else-if="_key.includes('_limit') || _key === 'pay_dupe_least'">
                                <teamplate v-if="(item.module_type != 0 || _key == 'abnormal_trans_limit' || _key == 'pay_dupe_least') && item[_key] != 0">
                                    {{ item[_key] }}만원
                                </teamplate>
                                <template v-else>
                                    -
                                </template>
                            </span>
                            <span v-else-if="_key === 'pay_disable_tm'">
                                <teamplate v-if="item.module_type != 0">
                                    {{ item.pay_disable_s_tm }} ~ {{ item.pay_disable_e_tm }}
                                </teamplate>
                            </span>
                            <span v-else-if="_key === 'show_pay_view'">
                                <teamplate v-if="item.module_type != 0">
                                    <VChip :color="store.booleanTypeColor(!item[_key])">
                                        {{ item[_key] ? '노출' : '숨김' }}
                                    </VChip>
                                </teamplate>
                                <template v-else>
                                    -
                                </template>
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
