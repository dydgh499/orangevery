<script setup lang="ts">
import { cxl_types, fin_trx_delays, installments, module_types, pay_window_extend_hours, pay_window_secure_levels, useSearchStore } from '@/views/merchandises/pay-modules/useStore'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { selectFunctionCollect } from '@/views/selected'
import { useStore } from '@/views/services/pay-gateways/useStore'

import BatchDialog from '@/layouts/dialogs/BatchDialog.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'

import { getUserLevel, isAbleModiy } from '@axios'
import { DateFilters, ItemTypes } from '@core/enums'

const { pgs, pss, settle_types, finance_vans, terminals } = useStore()
const { findSalesName } = useSalesFilterStore()
const { store, head, exporter, metas } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const batchDialog = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

onMounted(() => {
    watchEffect(async () => {
        if (store.getChartProcess() === false) {
            const r = await store.getChartData()
            if(r.status === 200) {
                metas[0]['stats'] = r.data.this_month_add.toLocaleString()
                metas[1]['stats'] = (r.data.this_month_del * -1).toLocaleString()
                metas[2]['stats'] = r.data.this_week_add.toLocaleString()
                metas[3]['stats'] = (r.data.this_week_del * -1).toLocaleString()
                metas[0]['percentage'] = store.getPercentage(r.data.this_month_add, r.data.total)
                metas[1]['percentage'] = store.getPercentage((r.data.this_month_del * -1), r.data.total)
                metas[2]['percentage'] = store.getPercentage(r.data.this_week_add, r.data.total)
                metas[3]['percentage'] = store.getPercentage((r.data.this_week_del * -1), r.data.total)
            }
        }
    })
})
</script>
<template>
    <div>
        <BaseIndexView placeholder="MID, TID, 가맹점 상호, 별칭 검색" :metas="metas" :add="isAbleModiy(0)" add_name="결제모듈"
            :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="true" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #pg_extra_field>
                        <VCol cols="6" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.module_type"
                                :items="[{ id: null, title: '전체' }].concat(module_types)" label="모듈타입 필터"
                                item-title="title" item-value="id"
                                @update:modelValue="store.updateQueryString({ module_type: store.params.module_type })" />
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="carbon:batch-job" @click="batchDialog.show()" v-if="getUserLevel() >= 35"
                    color="primary" size="small" style="margin: 0.25em;">
                    일괄작업
                </VBtn>
                <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.un_use" label="작월 미결제 결제모듈 조회"
                    color="warning" @update:modelValue="store.updateQueryString({ un_use: store.params.un_use })" />
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
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible"
                        class='list-square'>
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
                    <VTooltip activator="parent" location="end" open-delay="250" transition="scroll-x-transition" v-if="$vuetify.display.smAndDown === false">
                        {{ item['mcht_name'] }}
                    </VTooltip>                    
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
                                    <div class='check-label-container'>
                                        <VCheckbox v-if="getUserLevel() >= 35" v-model="selected" :value="item[_key]"
                                            class="check-label" />
                                        <span class="edit-link" @click="store.edit(item['id'])">
                                            #{{ item[_key] }}
                                            <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                                상세보기
                                            </VTooltip>
                                        </span>
                                    </div>
                                </span>
                                <span v-else-if="_key == 'note'" class="edit-link" @click="store.edit(item['id'])">
                                    {{ item[_key] }}
                                    <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                        상세보기
                                    </VTooltip>
                                </span>
                                <span v-else-if="_key == 'module_type'">
                                    <VChip
                                        :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                        {{ module_types.find(module_type => module_type['id'] === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="(_key as string).includes('_id') && (_key as string).includes('sales')">
                                    {{ findSalesName(_key as string, item[_key]) }}
                                </span>
                                <span v-else-if="(_key as string).includes('_fee') && _key !== 'settle_fee' && _key !== 'comm_settle_fee'">
                                    <VChip v-if="item[_key]">
                                        {{ (item[_key] * 100).toFixed(3) }} %
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
                                <span v-else-if="_key == 'comm_settle_fee'">
                                    {{ Number(item[_key]).toLocaleString() }}
                                </span>
                                <span v-else-if="_key == 'terminal_id'">
                                    {{ terminals.find(obj => obj.id === item[_key])?.name }}
                                </span>
                                <span v-else-if="_key == 'cxl_type'">
                                    <VChip
                                        :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
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
                                <span
                                    v-else-if="(_key.includes('_limit') || _key === 'pay_dupe_least') && _key != 'pay_dupe_limit'">
                                    <template
                                        v-if="(item.module_type != 0 || _key == 'abnormal_trans_limit' || _key == 'pay_dupe_least') && item[_key] != 0">
                                        {{ item[_key] }}만원
                                    </template>
                                    <template v-else>
                                        -
                                    </template>
                                </span>
                                <span v-else-if="_key === 'pay_disable_tm'">
                                    <template v-if="item.module_type != 0">
                                        {{ item.pay_disable_s_tm }} ~ {{ item.pay_disable_e_tm }}
                                    </template>
                                </span>
                                <span v-else-if="_key === 'payment_term_min'">
                                    <template v-if="item.module_type != 0">
                                            {{ item[_key] }}분
                                    </template>
                                    <template v-else>
                                        -
                                    </template>
                                </span>
                                <span v-else-if="_key === 'pay_window_secure_level'">
                                    <template v-if="item.module_type != 0">
                                        <VChip
                                            :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                            {{ pay_window_secure_levels.find(obj => obj.id === item[_key])?.title }}
                                        </VChip>
                                    </template>
                                    <template v-else>
                                        -
                                    </template>
                                </span>
                                <span v-else-if="_key === 'pay_window_extend_hour'">
                                    <template v-if="item.module_type === 1">
                                        {{ pay_window_extend_hours.find(obj => obj.id === item[_key])?.title }}
                                    </template>
                                    <template v-else>
                                        -
                                    </template>
                                </span>
                                <span v-else-if="_key == 'updated_at'"
                                    :class="item[_key] !== item['created_at'] ? 'text-primary' : ''">
                                    {{ item[_key] }}
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
        <BatchDialog ref="batchDialog" :selected_idxs="selected" :item_type="ItemTypes.PaymentModule"
            @update:select_idxs="selected = $event; store.setTable(); store.getChartData()" />
    </div>
</template>
