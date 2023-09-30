<script setup lang="ts">
import { useSalesFilterStore, getIndexByLevel } from '@/views/salesforces/useStore'
import corp from '@corp'
import { user_info } from '@axios'

interface Props {
    show: boolean,
}
const props = defineProps<Props>();
const store = <any>(inject('store'))
const { sales, setUnderSalesFilter } = useSalesFilterStore()
setUnderSalesFilter(5, store.params)

const levels = corp.pv_options.auth.levels

watchEffect(() => {    
    store.setChartProcess()
    store.params.sales5_id = store.params.sales5_id
    store.params.sales4_id = store.params.sales4_id
    store.params.sales3_id = store.params.sales3_id
    store.params.sales2_id = store.params.sales2_id
    store.params.sales1_id = store.params.sales1_id
    store.params.sales0_id = store.params.sales0_id
})
</script>
<template>
    <VRow>
        <template v-for="i in 6" :key="i">
            <VCol :cols="12" :sm="3" v-if="levels['sales'+(5-i)+'_use'] && props.show && user_info.level >= getIndexByLevel(5-i)">
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params['sales'+(5-i)+'_id']"
                    :items="sales[5-i].value" :label="levels['sales'+(5-i)+'_name'] + ' 필터'"
                    item-title="sales_name" item-value="id" @update:modelValue="setUnderSalesFilter(5-i, store.params)"/>
            </VCol>
        </template>
        <slot name="sales_extra_field"></slot>
    </VRow>
</template>
