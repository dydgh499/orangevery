<script setup lang="ts">
import { useSearchStore } from '@/views/salesforces/useStore';
import { selectFunctionCollect } from '@/views/selected';

import BatchDialog from '@/layouts/dialogs/BatchDialog.vue';
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue';
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import SalesforceChildOverview from '@/pages/salesforces/SalesforceChildOverview.vue';
import UserExtraMenu from '@/views/users/UserExtraMenu.vue';

import { settleCycles, settleDays, settleTaxTypes } from '@/views/salesforces/useStore';
import { getLevelByIndex, getUserLevel, isAbleModiy, salesLevels } from '@axios';
import { DateFilters, ItemTypes } from '@core/enums';
import corp from '@corp';

const { store, head, exporter, metas } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)

const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()
const sales_levels = salesLevels()
const batchDialog = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

store.params.level = sales_levels[sales_levels.length - 1].id as number
</script>
<template>
  
  <div>
        <BaseIndexView placeholder="아이디, 영업점 상호 검색" :metas="metas" :add="isAbleModiy(0)" add_name="영업점" :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
                <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="false"
                    :sales="false">
                    <template #sales_extra_field>
                        <VCol cols="6" sm="3">
                            <VSelect v-model="store.params.level" :items="sales_levels" density="compact" label="조회 등급"
                                item-title="title" item-value="id"
                                @update:modelValue="store.updateQueryString({ level: store.params.level })" />
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="carbon:batch-job" @click="batchDialog.show()" v-if="getUserLevel() >= 35" color="primary" size="small">
                    일괄 작업
                </VBtn>
                <div>
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.is_lock" label="잠금계정 조회"
                        color="warning" @update:modelValue="store.updateQueryString({ is_lock: store.params.is_lock })" v-if="getUserLevel() >= 35"/>
                </div>
            </template>
            <template #headers>
                <tr>
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                        <div class='check-label-container' v-if="key == 'id'">
                            <VCheckbox v-if="getUserLevel() >= 35" v-model="all_selected" class="check-label"/>
                            <span v-if="getUserLevel() >= 35">선택/취소</span>
                            <span v-else>{{ header.ko }}</span>
                        </div>
                        <span v-else>
                            {{ header.ko }}
                        </span>
                    </th>
                </tr>
            </template>
            <template #body>
                <template v-for="(item, index) in store.getItems" :key="index">
                    <tr>
                        <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                                <td v-show="_header.visible" class='list-square'>
                                    <span v-if="_key == 'id'">
                                        <div class='check-label-container'>
                                            <VCheckbox v-if="getUserLevel() >= 35" v-model="selected" :value="item[_key]" class="check-label"/>
                                            <span class="edit-link" @click="store.edit(item['id'])">#{{ item[_key] }}</span>
                                        </div>
                                    </span>
                                    <span v-else-if="_key == 'user_name'" class="edit-link" @click="store.edit(item['id'])">
                                        {{ item[_key] }}
                                    </span>
                                    <span v-else-if="_key == 'level'">
                                        <VChip :color="store.getSelectIdColor(getLevelByIndex(item[_key]))">
                                            {{ salesLevels().find(obj => obj.id === item[_key])?.title }}
                                        </VChip>
                                    </span>
                                    <span v-else-if="_key == 'settle_cycle'">
                                        <VChip
                                            :color="store.getSelectIdColor(all_cycles.find(obj => obj.id === item[_key])?.id)">
                                            {{ all_cycles.find(sales => sales.id === item[_key])?.title }}
                                        </VChip>
                                    </span>
                                    <span v-else-if="_key == 'sales_fee'">
                                        {{ item[_key].toLocaleString() }}%
                                    </span>
                                    <span v-else-if="_key == 'settle_day'">
                                        {{ all_days.find(sales => sales.id === item[_key])?.title }}
                                    </span>
                                    <span v-else-if="_key == 'resident_num'">
                                        <span>{{ item['resident_num_front'] }}</span>
                                        <span style="margin: 0 0.25em;">-</span>
                                        <span v-if="corp.pv_options.free.resident_num_masking">*******</span>
                                        <span v-else>{{ item['resident_num_back'] }}</span>
                                    </span>
                                    <span v-else-if="_key == 'settle_tax_type'">
                                        <VChip
                                            :color="store.getSelectIdColor(tax_types.find(obj => obj.id === item[_key])?.id)">
                                            {{ tax_types.find(sales => sales.id === item[_key])?.title }}
                                        </VChip>
                                    </span>
                                    <span v-else-if="_key == 'view_type'">
                                        <VChip
                                            :color="store.booleanTypeColor(!item[_key])">
                                            {{ item[_key] ? '상세보기' : '간편보기' }}
                                        </VChip>
                                    </span>
                                    <span v-else-if="_key == 'is_lock'">
                                    <VChip :color="store.booleanTypeColor(item[_key])">
                                            {{ item[_key] ? 'LOCK' : 'X' }}
                                        </VChip>
                                    </span>
                                    <span v-else-if="_key == 'extra_col'">
                                        <UserExtraMenu :item="item" :type="1" :key="item['id']" />
                                    </span>
                                    <span v-else>
                                        {{ item[_key] }}
                                    </span>
                                </td>
                        </template>
                    </tr>
                    <SalesforceChildOverview v-for="(child, _key, _index) in item.childs" :key="_index" :salesforce="child" :depth="1"/>
                </template>
            </template>
        </BaseIndexView>
        <BatchDialog ref="batchDialog" :selected_idxs="selected" :item_type="ItemTypes.Salesforce"
        @update:select_idxs="selected = $event; store.setTable()"/>
    </div>
</template>
