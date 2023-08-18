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

const sales5 = ref({ id: null, sales_name: '전체' })
const sales4 = ref({ id: null, sales_name: '전체' })
const sales3 = ref({ id: null, sales_name: '전체' })
const sales2 = ref({ id: null, sales_name: '전체' })
const sales1 = ref({ id: null, sales_name: '전체' })
const sales0 = ref({ id: null, sales_name: '전체' })

watchEffect(() => {    
    store.setChartProcess()
    store.params.sales5_id = sales5.value.id
    store.params.sales4_id = sales4.value.id
    store.params.sales3_id = sales3.value.id
    store.params.sales2_id = sales2.value.id
    store.params.sales1_id = sales1.value.id
    store.params.sales0_id = sales0.value.id
})
</script>
<template>
    <VRow>
        <VCol cols="12" sm="3" v-if="levels.sales5_use && props.show && user_info.level >= 30">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales5"
                :items="[{ id: null, sales_name: '전체' }].concat(sales[5].value)" :label="levels.sales5_name + ' 필터'"
                item-title="sales_name" item-value="id" return-object />
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales4_use && props.show && user_info.level >= 25">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales4"
                :items="[{ id: null, sales_name: '전체' }].concat(sales[4].value)" :label="levels.sales4_name + ' 필터'"
                item-title="sales_name" item-value="id" return-object />
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales3_use && props.show && user_info.level >= 20">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales3"
                :items="[{ id: null, sales_name: '전체' }].concat(sales[3].value)" :label="levels.sales3_name + ' 필터'"
                item-title="sales_name" item-value="id" return-object />
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales2_use && props.show && user_info.level >= 17">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales2"
                :items="[{ id: null, sales_name: '전체' }].concat(sales[2].value)" :label="levels.sales2_name + ' 필터'"
                item-title="sales_name" item-value="id" return-object />
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales1_use && props.show && user_info.level >= 15">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales1"
                :items="[{ id: null, sales_name: '전체' }].concat(sales[1].value)" :label="levels.sales1_name + ' 필터'"
                item-title="sales_name" item-value="id" return-object />
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales0_use && props.show && user_info.level >= 13">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="sales0"
                :items="[{ id: null, sales_name: '전체' }].concat(sales[0].value)" :label="levels.sales0_name + ' 필터'"
                item-title="sales_name" item-value="id" return-object />
        </VCol>
        <slot name="sales_extra_field"></slot>
    </VRow>
</template>
