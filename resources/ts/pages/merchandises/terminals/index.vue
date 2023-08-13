<script setup lang="ts">
import { useSearchStore } from '@/views/merchandises/terminals/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { allLevels } from '@/views/salesforces/useStore'
import { module_types, installments, shipOutStats } from '@/views/merchandises/pay-modules/useStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { user_info, getUserLevel } from '@axios'

const { pgs, pss, settle_types, terminals } = useStore()

const { store, head, exporter } = useSearchStore()
provide('store', store)
provide('head', head)
provide('exporter', exporter)

const all_levels = allLevels()
const isMchtUnableCol = (key: string) => {
    if(getUserLevel() > 10)
        return false
    else {
        const cols = [
            'module_type', 'installment', 'pg_id', 'ps_id', 
            'settle_type', 'terminal_id', 'comm_settle_fee', 'ship_out_stat',
            'comm_calc_level', 'mid', 'tid', 'begin_dt', 'ship_out_dt',
            'under_sales_amt', 'comm_settle_type',
        ]
        return cols.includes(key);
    }
}
watchEffect(() => {    
    store.setChartProcess()
    store.params.ship_out_stat = store.params.ship_out_stat
})
</script>
<template>
    <BaseIndexView placeholder="MID, TID, 시리얼 번호, 가맹점 상호 검색" :metas="[]" :add="user_info.level >= 35" add_name="장비"
        :is_range_date="null">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="true" :terminal="true" :cus_filter="true" :sales="true"
                v-if="getUserLevel() > 10">
                <template #pg_extra_field>
                    <VCol cols="12" sm="3">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.ship_out_stat"
                            :items="[{ id: null, title: '전체' }].concat(shipOutStats)" label="출고타입 선택" item-title="title"
                            item-value="id" />
                    </VCol>
                </template>
            </BaseIndexFilterCard>
        </template>
        <template #headers>
            <tr>
                <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                    <span v-if="isMchtUnableCol(key as string) == false">
                        {{ header.ko }}
                    </span>
                </th>
            </tr>
        </template>

        <template #body>
            <tr v-for="(item, index) in store.getItems" :key="index">
                <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                    <td v-show="_header.visible" class='list-square'>
                        <span v-if="_key == 'id'" class="edit-link" @click="store.edit(item['id'])">
                            #{{ item[_key] }}
                        </span>
                        <span v-else-if="_key == 'note'" class="edit-link" @click="store.edit(item['id'])">
                            {{ item[_key] }}
                        </span>
                        <span v-else-if="_key == 'module_type' && isMchtUnableCol(_key) == false">
                            <VChip :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                {{ module_types.find(obj => obj.id === item[_key])?.title }}
                            </VChip>
                        </span>
                        <span v-else-if="_key == 'installment' && isMchtUnableCol(_key) == false">
                            {{ installments.find(item => item['id'] === item[_key])?.title }}
                        </span>
                        <span v-else-if="_key == 'pg_id' && isMchtUnableCol(_key) == false">
                            {{ pgs.find(pg => pg['id'] === item[_key])?.pg_name }}
                        </span>
                        <span v-else-if="_key == 'ps_id' && isMchtUnableCol(_key) == false">
                            {{ pss.find(ps => ps['id'] === item[_key])?.name }}
                        </span>
                        <span v-else-if="_key == 'settle_type' && isMchtUnableCol(_key) == false">
                            {{ settle_types.find(settle_type => settle_type['id'] === item[_key])?.name }}
                        </span>
                        <span v-else-if="_key == 'terminal_id' && isMchtUnableCol(_key) == false">
                            {{ terminals.find(terminal => terminal['id'] === item[_key])?.name }}
                        </span>
                        <span v-else-if="_key == 'comm_settle_fee' && isMchtUnableCol(_key) == false">
                            {{ item[_key].toLocaleString() }}
                        </span>
                        <span v-else-if="_key == 'ship_out_stat' && isMchtUnableCol(_key) == false">
                            <VChip :color="store.getSelectIdColor(shipOutStats.find(obj => obj.id === item[_key])?.id)">
                                {{ shipOutStats.find(obj => obj.id === item[_key])?.title }}
                            </VChip>
                        </span>
                        <span v-else-if="_key == 'comm_calc_level' && isMchtUnableCol(_key) == false">
                            {{ all_levels.find(level => level['id'] === item[_key])?.title }}
                        </span>
                        <span v-else-if="isMchtUnableCol(_key) == false">
                            {{ item[_key] }}
                        </span>
                    </td>
                </template>
            </tr>
        </template>
    </BaseIndexView>
</template>
