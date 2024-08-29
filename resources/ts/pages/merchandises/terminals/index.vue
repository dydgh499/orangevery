<script setup lang="ts">
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { comm_settle_types, installments, module_types, ship_out_stats, under_sales_types } from '@/views/merchandises/pay-modules/useStore'
import { useSearchStore } from '@/views/merchandises/terminals/useStore'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { allLevels, getUserLevel, isAbleModiy } from '@axios'
import { DateFilters } from '@core/enums'

const { pgs, pss, settle_types, terminals } = useStore()
const { store, head, exporter } = useSearchStore()
const { findSalesName } = useSalesFilterStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <BaseIndexView placeholder="MID, TID, 시리얼 번호, 가맹점 상호 검색" :metas="[]" :add="isAbleModiy(0)" add_name="장비"
        :date_filter_type="DateFilters.NOT_USE">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="true" :terminal="true" :cus_filter="true" :sales="true"
                v-if="getUserLevel() > 10">
                <template #pg_extra_field>
                    <VCol cols="6" sm="3">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.ship_out_stat"
                            :items="[{ id: null, title: '전체' }].concat(ship_out_stats)" label="출고타입 필터" item-title="title"
                            item-value="id" @update:modelValue="store.updateQueryString({ship_out_stat: store.params.ship_out_stat})" />
                    </VCol>
                </template>
            </BaseIndexFilterCard>
        </template>
        <template #index_extra_field>
            <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.un_use" label="작월 미결제 단말기 조회" color="warning" @update:modelValue="store.updateQueryString({un_use: store.params.un_use})"/>
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
                <VTooltip activator="parent" location="end" open-delay="250">
                    {{ item['mcht_name'] }}
                </VTooltip>                
                <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                    <td v-show="_header.visible" class='list-square'>
                        <span v-if="_key === 'id' && getUserLevel() > 10" class="edit-link" @click="store.edit(item['id'])">
                            #{{ item[_key] }}
                        </span>
                        <span v-else-if="_key === 'id' && getUserLevel() === 10">
                            #{{ item[_key] }}
                        </span>
                        <span v-else-if="_key == 'note' && getUserLevel() > 10" class="edit-link" @click="store.edit(item['id'])">
                            {{ item[_key] }}
                        </span>
                        <span v-else-if="_key == 'note' && getUserLevel() === 10">
                            {{ item[_key] }}
                        </span>
                        <span v-else-if="_key == 'module_type'">
                            <VChip :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                {{ module_types.find(obj => obj.id === item[_key])?.title }}
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
                        <span v-else-if="(_key.includes('_limit') || _key === 'pay_dupe_least') && _key != 'pay_dupe_limit'">
                            <teamplate
                                v-if="(item.module_type != 0 || _key == 'abnormal_trans_limit' || _key == 'pay_dupe_least') && item[_key] != 0">
                                {{ item[_key] }}만원
                            </teamplate>
                            <template v-else>
                                -
                            </template>
                        </span>
                        <span v-else-if="_key == 'installment'">
                            {{ installments.find(item => item['id'] === item[_key])?.title }}
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
                        <span v-else-if="_key == 'terminal_id'">
                            {{ terminals.find(terminal => terminal['id'] === item[_key])?.name }}
                        </span>
                        <span v-else-if="_key == 'comm_settle_fee'">
                            {{ item[_key].toLocaleString() }}
                        </span>
                        <span v-else-if="_key == 'under_sales_amt'">
                            {{ item[_key].toLocaleString() }}
                        </span>                        
                        <span v-else-if="_key == 'under_sales_type'">
                            <VChip
                                :color="store.getSelectIdColor(under_sales_types.find(obj => obj.id === item[_key])?.id)">
                                {{ under_sales_types.find(obj => obj.id === item[_key])?.title }}
                            </VChip>
                        </span>
                        <span v-else-if="_key == 'comm_settle_type'">
                            <template v-if="item[_key] !== null">
                                <VChip
                                    :color="store.getSelectIdColor(comm_settle_types.find(obj => obj.id === item[_key])?.id)">
                                    {{ comm_settle_types.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </template>
                            <template v-else>
                                -
                            </template>
                        </span>
                        <span v-else-if="_key == 'ship_out_stat'">
                            <VChip :color="store.getSelectIdColor(ship_out_stats.find(obj => obj.id === item[_key])?.id)">
                                {{ ship_out_stats.find(obj => obj.id === item[_key])?.title }}
                            </VChip>
                        </span>
                        <span v-else-if="_key == 'comm_calc_level'">
                            <VChip :color="store.getAllLevelColor(item[_key])">
                                {{ allLevels().find(level => level['id'] === item[_key])?.title }}
                            </VChip>
                        </span>
                        <span v-else-if="_key == 'updated_at'" :class="item[_key] !== item['created_at'] ? 'text-primary' : ''">
                            {{ item[_key] }}
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
