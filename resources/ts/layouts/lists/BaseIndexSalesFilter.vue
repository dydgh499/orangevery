<script setup lang="ts">
import { overlap } from '@/views/salesforces/overlap';
import { useSalesFilterStore } from '@/views/salesforces/useStore';
import { getIndexByLevel, getUserLevel } from '@axios';
import corp from '@corp';

interface Props {
    show: boolean,
}
const route = useRoute()
const props = defineProps<Props>();
const store = <any>(inject('store'))
const { sales, all_sales, setUnderSalesFilter, initAllSales } = useSalesFilterStore()
const { recursionSalesFilter } = overlap(all_sales, sales)
const levels = corp.pv_options.auth.levels

const selectedSalesEvent = (select_idx:number) => {
    const sales_id = `sales${(6 - select_idx)}_id`
    if(corp.pv_options.paid.sales_parent_structure && getUserLevel() < 40) {
        return [
            recursionSalesFilter(6 - select_idx, store.params),
            store.updateQueryString({
                'sales0_id': store.params.sales0_id,
                'sales1_id': store.params.sales1_id,
                'sales2_id': store.params.sales2_id,
                'sales3_id': store.params.sales3_id,
                'sales4_id': store.params.sales4_id,
                'sales5_id': store.params.sales5_id,
            })
        ]
    }
    else
        return [store.updateQueryString({ [sales_id]: store.params[sales_id] }), setUnderSalesFilter(6 - select_idx, store.params)]
}

</script>
<template>
    <VRow>
        <template v-for="i in 6" :key="i">
            <VCol cols="6" sm="3"
                v-if="levels[`sales${(6 - i)}_use`] && props.show && getUserLevel() > getIndexByLevel(6 - i)">
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params[`sales${(6 - i)}_id`]"
                    :items="sales[6 - i].value" :label="levels[`sales${(6 - i)}_name`] + ' 필터'" item-title="sales_name"
                    item-value="id" hide-details style="height: 40px !important;"
                    @update:modelValue="selectedSalesEvent(i)" />
            </VCol>
        </template>
        <slot name="sales_extra_field"></slot>
    </VRow>
</template>
