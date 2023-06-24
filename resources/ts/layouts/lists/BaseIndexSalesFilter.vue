<script setup lang="ts">
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import corp from '@corp'

interface Props {
    is_delete: boolean,
}
const props = defineProps<Props>();
const store = <any>(inject('store'))
const { sales } = useSalesFilterStore()

const levels = corp.pv_options.auth.levels
</script>
<template>
    <VRow>
        <VCol cols="12" sm="3" v-if="levels.sales5_use && props.is_delete">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales5_id"
                :items="[{ id: null, user_name: levels.sales5_name + ' 선택' }].concat(sales[5].value)"
                :label="levels.sales5_name + ' 선택'" item-title="user_name" item-value="id" />
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales4_use && props.is_delete">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales4_id"
                :items="[{ id: null, user_name: levels.sales4_name + ' 선택' }].concat(sales[4].value)"
                :label="levels.sales4_name + ' 선택'" item-title="user_name" item-value="id" />
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales3_use && props.is_delete">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales3_id"
                :items="[{ id: null, user_name: levels.sales3_name + ' 선택' }].concat(sales[3].value)"
                :label="levels.sales3_name + ' 선택'" item-title="user_name" item-value="id" />
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales2_use && props.is_delete">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales2_id"
                :items="[{ id: null, user_name: levels.sales2_name + ' 선택' }].concat(sales[2].value)"
                :label="levels.sales2_name + ' 선택'" item-title="user_name" item-value="id" />
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales1_use && props.is_delete">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales1_id"
                :items="[{ id: null, user_name: levels.sales1_name + ' 선택' }].concat(sales[1].value)"
                :label="levels.sales1_name + ' 선택'" item-title="user_name" item-value="id" />
        </VCol>
        <VCol cols="12" sm="3" v-if="levels.sales0_use && props.is_delete">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales0_id"
                :items="[{ id: null, user_name: levels.sales0_name + ' 선택' }].concat(sales[0].value)"
                :label="levels.sales0_name + ' 선택'" item-title="user_name" item-value="id" />
        </VCol>
    <slot name="extra_left"></slot>
</VRow></template>
