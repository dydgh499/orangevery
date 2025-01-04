<script setup lang="ts">
import SelectOptionDialog from '@/layouts/dialogs/transactions/SelectOptionDialog.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { selectFunctionCollect } from '@/views/selected'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useSearchStore } from '@/views/transactions/orders/useStore'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const { pgs, pss } = useStore()
const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const selectOptionDialog = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <div>
        <BaseIndexView placeholder="구매자 연락처, 상품명, 승인번호 검색" :metas="[]" :add="false" add_name="주문" :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="true" :terminal="true" :cus_filter="true" :sales="true" />
            </template>
            <template #index_extra_field>
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
                                <span v-if="_key == `id`">
                                    <div class='check-label-container'>
                                        <VCheckbox v-if="getUserLevel() >= 35" v-model="selected" :value="item[_key]"
                                            class="check-label" />
                                        <span>
                                            #{{ item[_key]}}
                                        </span>
                                    </div>
                                </span>
                                <span v-else-if="_key == 'pg_id'">
                                    {{ pgs.find(pg => pg['id'] === item[_key])?.pg_name }}
                                </span>        
                                <span v-else-if="_key == 'ps_id'">
                                    {{ pss.find(ps => ps['id'] === item[_key])?.name }}
                                </span>
                                <span v-else-if="_key == 'amount'">
                                    {{ (item[_key] as number).toLocaleString() }}
                                </span>
                                <span v-else-if="_key.toString().includes('_fee') && _key != 'mcht_settle_fee'">
                                    <VChip v-if="item[_key]">
                                        {{ (item[_key] * 100).toFixed(3) }} %
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'option_groups'">
                                    <VBtn size="small" @click="selectOptionDialog.show(item[_key])">옵션확인</VBtn>
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
        <SelectOptionDialog ref="selectOptionDialog"/>
    </div>
</template>
