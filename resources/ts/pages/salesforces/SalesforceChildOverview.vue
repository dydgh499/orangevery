
<script setup lang="ts">

import SalesforceChildOverview from '@/pages/salesforces/SalesforceChildOverview.vue';
import { selectFunctionCollect } from '@/views/selected';
import UserExtraMenu from '@/views/users/UserExtraMenu.vue';

import { settleCycles, settleDays, settleTaxTypes } from '@/views/salesforces/useStore';
import { Salesforce } from '@/views/types';
import { getLevelByIndex, getUserLevel, salesLevels } from '@axios';
import corp from '@corp';

interface Props {
    salesforce: Salesforce,
    depth: number,
}
const props = defineProps<Props>()
const store = <any>(inject('store'))
const head = <any>(inject('head')) 
const { selected, all_selected } = selectFunctionCollect(store)

const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()

const getChildDepth = computed(() => {
    return props.depth + 1
})

</script>

<template>
    <tr :style="(depth+1)%2 === 0 ? 'background: rgba(var(--v-theme-primary), 20%);' : 'background: rgba(var(--v-theme-primary), 10%);'">
        <template v-for="(header, key, idx) in head.headers" :key="idx">
            <td v-show="header.visible" :class="key == 'title' ? 'list-square title' : 'list-square'">
                <span v-if="key == 'id'">
                    <div class='check-label-container'>
                        <VCheckbox v-if="getUserLevel() >= 35" v-model="selected" :value="props.salesforce[key]" class="check-label"/>
                        <span class="edit-link" @click="store.edit(props.salesforce['id'])">#{{ props.salesforce[key] }}</span>
                    </div>
                </span>
                <span v-else-if="key == 'user_name'" class="edit-link" @click="store.edit(props.salesforce['id'])">
                    {{ props.salesforce[key] }}
                </span>
                <span v-else-if="key == 'level'">
                    <VChip :color="store.getSelectIdColor(getLevelByIndex(props.salesforce[key]))">
                        {{ salesLevels().find(obj => obj.id === props.salesforce[key])?.title }}
                    </VChip>
                </span>
                <span v-else-if="key == 'settle_cycle'">
                    <VChip
                        :color="store.getSelectIdColor(all_cycles.find(obj => obj.id === props.salesforce[key])?.id)">
                        {{ all_cycles.find(sales => sales.id === props.salesforce[key])?.title }}
                    </VChip>
                </span>
                <span v-else-if="key == 'sales_fee'">
                    {{ props.salesforce[key].toLocaleString() }}%
                </span>
                <span v-else-if="key == 'settle_day'">
                    {{ all_days.find(sales => sales.id === props.salesforce[key])?.title }}
                </span>
                <span v-else-if="key == 'resident_num'">
                    <span>{{ props.salesforce['resident_num_front'] }}</span>
                    <span style="margin: 0 0.25em;">-</span>
                    <span v-if="corp.pv_options.free.resident_num_masking">*******</span>
                    <span v-else>{{ props.salesforce['resident_num_back'] }}</span>
                </span>
                <span v-else-if="key == 'settle_tax_type'">
                    <VChip
                        :color="store.getSelectIdColor(tax_types.find(obj => obj.id === props.salesforce[key])?.id)">
                        {{ tax_types.find(sales => sales.id === props.salesforce[key])?.title }}
                    </VChip>
                </span>
                <span v-else-if="key == 'view_type'">
                    <VChip
                        :color="store.booleanTypeColor(!props.salesforce[key])">
                        {{ props.salesforce[key] ? '상세보기' : '간편보기' }}
                    </VChip>
                </span>
                <span v-else-if="key == 'extra_col'">
                    <UserExtraMenu :item="props.salesforce" :type="1" :key="item['id']"/>
                </span>
                <span v-else>
                    {{ props.salesforce[key] }}
                </span>
            </td>
        </template>
    </tr>
    <SalesforceChildOverview v-for="(child, _idx) in salesforce.childs" :key="_idx" :salesforce="child" :depth="getChildDepth"/>
</template>
