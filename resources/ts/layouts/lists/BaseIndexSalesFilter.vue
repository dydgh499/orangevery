<script setup lang="ts">
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import corp from '@corp'
import { user_info } from '@axios'

interface Props {
    show: boolean,
}
const props = defineProps<Props>();
const store = <any>(inject('store'))
const { sales } = useSalesFilterStore()

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
        <VCol cols="12" sm="3" v-if="levels.sales5_use && props.show && user_info.level >= 30">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales5_id"
                :items="[{ id: null, sales_name: '전체' }].concat(sales[5].value)" :label="levels.sales5_name + ' 필터'"
                item-title="sales_name" item-value="id"/>
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales4_use && props.show && user_info.level >= 25">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales4_id"
                :items="[{ id: null, sales_name: '전체' }].concat(sales[4].value)" :label="levels.sales4_name + ' 필터'"
                item-title="sales_name" item-value="id"/>
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales3_use && props.show && user_info.level >= 20">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales3_id"
                :items="[{ id: null, sales_name: '전체' }].concat(sales[3].value)" :label="levels.sales3_name + ' 필터'"
                item-title="sales_name" item-value="id"/>
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales2_use && props.show && user_info.level >= 17">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales2_id"
                :items="[{ id: null, sales_name: '전체' }].concat(sales[2].value)" :label="levels.sales2_name + ' 필터'"
                item-title="sales_name" item-value="id"/>
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales1_use && props.show && user_info.level >= 15">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales1_id"
                :items="[{ id: null, sales_name: '전체' }].concat(sales[1].value)" :label="levels.sales1_name + ' 필터'"
                item-title="sales_name" item-value="id"/>
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales0_use && props.show && user_info.level >= 13">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales0_id"
                :items="[{ id: null, sales_name: '전체' }].concat(sales[0].value)" :label="levels.sales0_name + ' 필터'"
                item-title="sales_name" item-value="id"/>
        </VCol>
        <slot name="sales_extra_field"></slot>
    </VRow>
</template>
