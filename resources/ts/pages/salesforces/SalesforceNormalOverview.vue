<script setup lang="ts">
import { useSearchStore } from '@/views/salesforces/useStore';
import { selectFunctionCollect } from '@/views/selected';

import BatchDialog from '@/layouts/dialogs/BatchDialog.vue';
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue';
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import UserExtraMenu from '@/views/users/UserExtraMenu.vue';

import { getAutoSetting, settleCycles, settleDays, settleTaxTypes } from '@/views/salesforces/useStore';
import type { Options } from '@/views/types';
import { getLevelByIndex, getUserLevel, isAbleModiy, salesLevels } from '@axios';
import { DateFilters, ItemTypes } from '@core/enums';
import corp from '@corp';

const { store, head, exporter, metas } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)

const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()
const batchDialog = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

store.params.level = null

onMounted(() => {
    watchEffect(async() => {
        if(store.getChartProcess() === false) {
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
        <BaseIndexView placeholder="아이디, 영업점 상호 검색" :metas="metas" :add="isAbleModiy(0)" add_name="영업점" :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
                <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="false"
                    :sales="true">
                    <template #sales_extra_field>
                        <VCol cols="6" sm="3">
                            <VSelect v-model="store.params.level" :items="[<Options>({ id: null, title: '전체' })].concat(salesLevels())" density="compact" label="조회 등급"
                                item-title="title" item-value="id"
                                @update:modelValue="store.updateQueryString({ level: store.params.level })" />
                        </VCol>
                        <VCol cols="6" sm="3" v-if="true">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.settle_cycle"
                                :items="[{ id: null, title: '전체' }].concat(settleCycles())" :label="`영업점 정산주기 선택`"
                                item-title="title" item-value="id" @update:modelValue="store.updateQueryString({settle_cycle: store.params.settle_cycle})"/>
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="carbon:batch-job" @click="batchDialog.show()" v-if="getUserLevel() >= 35" color="primary" size="small"
                    style="margin: 0.25em;">
                    일괄작업
                </VBtn>
                <div>
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.is_lock" label="잠금계정 조회"
                        color="warning" @update:modelValue="store.updateQueryString({ is_lock: store.params.is_lock })" v-if="getUserLevel() >= 35"/>
                </div>
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
                <tr v-for="(item, index) in store.getItems" :key="index">
                    <VTooltip activator="parent" location="end" open-delay="250" transition="scroll-x-transition" v-if="$vuetify.display.smAndDown === false">
                        {{ item['sales_name'] }}
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
                                        <VCheckbox v-if="getUserLevel() >= 35" v-model="selected" :value="item[_key]" class="check-label"/>
                                        <span class="edit-link" @click="store.edit(item['id'])">
                                            #{{ item[_key] }}
                                            <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                                상세보기
                                            </VTooltip>
                                        </span>
                                    </div>
                                </span>
                                <span v-else-if="_key == 'user_name'" class="edit-link" @click="store.edit(item['id'])">
                                    {{ item[_key] }}
                                    <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                        상세보기
                                    </VTooltip>
                                </span>
                                <span v-else-if="_key == 'level'">
                                    <VChip :color="store.getSelectIdColor(getLevelByIndex(item[_key]))">
                                        {{ salesLevels().find(obj => obj.id === item[_key])?.title }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'settle_cycle'">
                                    <VChip
                                        :color="store.getSelectIdColor(all_cycles.find(obj => obj.id === item[_key]/7)?.id)">
                                        {{ all_cycles.find(sales => sales.id === item[_key])?.title }}
                                    </VChip>
                                </span>                                
                                <span v-else-if="_key == 'under_auto_settings'">
                                    <select class="custom-select">
                                        <option v-for="(setting, key) in getAutoSetting(item['under_auto_settings'])" :key="key">
                                            {{ setting }}
                                        </option>
                                    </select>
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
                                <span v-else-if="_key == 'is_able_modify_mcht'">
                                    <VChip
                                        :color="store.booleanTypeColor(!item[_key])">
                                        {{ item[_key] ? '가능' : '불가능' }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'is_lock'">
                                    <VChip :color="store.booleanTypeColor(item[_key])">
                                        {{ item[_key] ? 'LOCK' : 'X' }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'view_type'">
                                    <VChip
                                        :color="store.booleanTypeColor(!item[_key])">
                                        {{ item[_key] ? '상세보기' : '간편보기' }}
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'extra_col'">
                                    <UserExtraMenu :item="item" :type="1" :key="item['id']"/>
                                </span>
                                <span v-else-if="_key == 'is_2fa_use'">
                                    <VChip :color="store.booleanTypeColor(!item[_key])">
                                        {{ item[_key] ? 'O' : 'X' }}
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
                    </template>
                </tr>
            </template>
        </BaseIndexView>
        <BatchDialog ref="batchDialog" :selected_idxs="selected" :item_type="ItemTypes.Salesforce"
            @update:select_idxs="selected = $event; store.setTable(); store.getChartData()"/>
    </div>
</template>
