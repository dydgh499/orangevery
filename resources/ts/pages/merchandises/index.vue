<script setup lang="ts">
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { useSearchStore } from '@/views/merchandises/useStore'
import { selectFunctionCollect } from '@/views/selected'
import { useStore } from '@/views/services/pay-gateways/useStore'
import UserExtraMenu from '@/views/users/UserExtraMenu.vue'

import BatchDialog from '@/layouts/dialogs/BatchDialog.vue'
import PasswordChangeDialog from '@/layouts/dialogs/users/PasswordChangeDialog.vue'

import { module_types } from '@/views/merchandises/pay-modules/useStore'
import { getUserLevel, isAbleModiy } from '@axios'
import { DateFilters, ItemTypes } from '@core/enums'
import corp from '@corp'
import { template } from 'lodash'

const { store, head, exporter, metas } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { pgs, settle_types } = useStore()
const password  = ref()
const batchDialog = ref()

provide('password', password)
provide('store', store)
provide('head', head)
provide('exporter', exporter)

onMounted(() => {
    watchEffect(async() => {
        if(store.getChartProcess() === false && getUserLevel() > 10) {
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
    <div>
        <BaseIndexView placeholder="아이디, 상호, 연락처, 대표자명, 사업자번호, 예금주, 계좌번호 검색" :metas="metas" :add="isAbleModiy(0)" add_name="가맹점"
            :date_filter_type="DateFilters.NOT_USE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="true" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #pg_extra_field>
                        <VCol cols="6" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.module_type"
                                :items="[{ id: null, title: '전체' }].concat(module_types)" label="모듈타입 필터" item-title="title"
                                item-value="id"
                                @update:modelValue="[store.updateQueryString({ module_type: store.params.module_type })]" />
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="carbon:batch-job" @click="batchDialog.show()" v-if="getUserLevel() >= 35" color="primary" size="small">
                    일괄 작업
                </VBtn>
                <div>
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.settle_hold" label="지급보류건 조회"
                        color="error" @update:modelValue="store.updateQueryString({ settle_hold: store.params.settle_hold })" v-if="getUserLevel() >= 35 && corp.pv_options.paid.use_settle_hold"/>
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
                <tr v-for="(item, index) in store.getItems" :key="index">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key == 'id'">
                                <div class='check-label-container'>
                                    <VCheckbox v-if="getUserLevel() >= 35" v-model="selected" :value="item[_key]" class="check-label"/>
                                    <span class="edit-link" @click="store.edit(item['id'])">#{{ item[_key] }}</span>
                                </div>
                            </span>
                            <span v-else-if="_key == `user_name`" class="edit-link" @click="store.edit(item['id'])">
                                {{ item[_key] }}
                            </span>
                            <span v-else-if="(_key as string).includes('_fee')">
                                <VChip v-if="item[_key]">
                                    {{ (item[_key] as number).toFixed(3) }} %
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'settle_types'">
                                <select class="custom-select">
                                    <option v-for="(settle_type, key) in item['settle_types']" :key="key">
                                        {{ settle_types.find(obj => obj.id === settle_type)?.name }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'mids'">
                                <select class="custom-select">
                                    <option v-for="(mid, key) in item['mids']" :key="key">
                                        {{ mid }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'tids'">
                                <select class="custom-select">
                                    <option v-for="(tid, key) in item['tids']" :key="key">
                                        {{ tid }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'module_types'">
                                <select class="custom-select">
                                    <option v-for="(module_type, key) in item['module_types']" :key="key">
                                        {{ module_types.find(module => module.id === module_type)?.title }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'pgs'">
                                <select class="custom-select">
                                    <option v-for="(_pg, key) in item['pgs']" :key="key">
                                        {{ pgs.find(pg => pg.id === _pg)?.pg_name }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'serial_nums'">
                                <select class="custom-select">
                                    <option v-for="(serial_num, key) in item['serial_nums']" :key="key">
                                        {{ serial_num }}
                                    </option>
                                </select>
                            </span>
                            <span v-else-if="_key == 'resident_num'">
                                <span>{{ item['resident_num_front'] }}</span>
                                <span style="margin: 0 0.25em;">-</span>
                                <span v-if="corp.pv_options.free.resident_num_masking">*******</span>
                                <span v-else>{{ item['resident_num_back'] }}</span>
                            </span>
                            <span v-else-if="_key == 'settle_hold_s_dt'">
                                <VChip color="error" v-if="item[_key]">
                                    {{ item[_key]  }}
                                </VChip>
                                <span v-else>-</span>
                            </span>
                            <span v-else-if="_key == 'enabled'">
                                <VChip :color="store.booleanTypeColor(!item[_key])">
                                    {{ item[_key] ? 'ON' : 'OFF' }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'is_lock'">
                                <VChip :color="store.booleanTypeColor(item[_key])">
                                    {{ item[_key] ? 'LOCK' : 'X' }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'extra_col'">
                                <UserExtraMenu :item="item" :type="0" :key="item['id']"/>
                            </span>                            
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
         <BatchDialog ref="batchDialog" :selected_idxs="selected" :item_type="ItemTypes.Merchandise"
            @update:select_idxs="selected = $event; store.setTable(); store.getChartData()"/>
        <PasswordChangeDialog ref="password" />
    </div>
</template>

