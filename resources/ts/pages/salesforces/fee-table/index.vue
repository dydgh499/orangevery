<script setup lang="ts">
import SalesFeeTableDialog from '@/layouts/dialogs/salesforces/SalesFeeTableDialog.vue';
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import { getTotalFee, useSearchStore } from '@/views/salesforces/fee-table/useStore';
import { useSalesFilterStore } from '@/views/salesforces/useStore';
import { getUserLevel } from '@axios';
import { DateFilters } from '@core/enums';

const { store, head, exporter } = useSearchStore()
const { sales, all_sales } = useSalesFilterStore()
const salesFeeTableDialog = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

</script>
<template>
    <div>
        <BaseIndexView 
                placeholder="아이디, 상호 검색" 
                :metas="[]" :add="false" 
                add_name=""
                :date_filter_type="DateFilters.NOT_USE"
            >
            <template #filter>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="tabler-plus" @click="salesFeeTableDialog.show({
                        id:0,
                        sales5_id: null, sales4_id: null,
                        sales3_id: null, sales2_id: null,
                        sales1_id: null,
                        sales5_fee: 0, sales4_fee: 0,
                        sales3_fee: 0, sales2_fee: 0,
                        sales1_fee: 0,
                    })" v-if="getUserLevel() >= 35" color="primary" size="small"
                    style="margin: 0.25em;">
                    수수료율 테이블
                </VBtn>
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
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key === 'id'" class="edit-link" @click="salesFeeTableDialog.show(item)">
                                #{{ item[_key] }}
                                <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                                    상세보기
                                </VTooltip>
                            </span>
                            <span v-else-if="(_key as string).includes('_id')">
                                {{ all_sales[(_key as string).replace(/\D/g, '')].find(obj => obj.id === item[_key])?.sales_name }}
                            </span>                            
                            <span v-else-if="_key === 'total_fee'">
                                <VChip color="primary">
                                    {{ getTotalFee(item) }} %
                                </VChip>
                            </span>
                            <span v-else-if="(_key as string).includes('_fee')">
                                <VChip color="success">
                                    {{ item[_key]}} %
                                </VChip>
                            </span>
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
        <SalesFeeTableDialog ref="salesFeeTableDialog"/>
    </div>
</template>
