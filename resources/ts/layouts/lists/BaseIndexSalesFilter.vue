<script setup lang="ts">
import { useSalesFilterStore } from '@/views/salesforces/useStore'

interface Props {
    is_use: boolean,
}
const props = defineProps<Props>();
const { sales } = useSalesFilterStore()
const store = <any>(inject('store'))
</script>
<template>
    <VRow>
        <VCol cols="12" sm="3" v-if="props.is_use">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales5_id" :items="sales[5].value"
                :label="`지사 선택`" item-title="nick_name" item-value="id"/>
        </VCol>
        <VCol cols="12" sm="3" v-if="props.is_use">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales4_id" :items="sales[4].value"
                :label="`하위지사 선택`" item-title="nick_name" item-value="id" />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.is_use">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales3_id" :items="sales[3].value"
                :label="`총판 선택`" item-title="nick_name" item-value="id" />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.is_use">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales2_id" :items="sales[2].value"
                :label="`하위총판 선택`" item-title="nick_name" item-value="id" />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.is_use">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales1_id" :items="sales[1].value"
                :label="`대리점 선택`" item-title="nick_name" item-value="id" />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.is_use">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.sales0_id" :items="sales[0].value"
                :label="`하위대리점 선택`" item-title="nick_name" item-value="id" />
        </VCol>
        <slot name="extra_left"></slot>
    </VRow>
</template>
