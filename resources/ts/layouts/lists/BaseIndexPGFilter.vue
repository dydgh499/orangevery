<script setup lang="ts">
import { useStore } from '@/views/services/pay-gateways/useStore'
import { user_info } from '@axios'

interface Props {
    pg: boolean,
    ps: boolean,
    pay_cond: boolean,
    terminal: boolean,
    cus_filter: boolean,
}
const props = defineProps<Props>()

const { pgs, pss, settle_types, terminals, cus_filters, psFilter } = useStore()
const store = <any>(inject('store'))

const filterPgs = computed(() => {
    const filter = pss.filter(item => {
        return item.pg_id == store.params.pg_id;
    })
    store.params.ps_id = psFilter(filter, store.params.ps_id)
    return filter
})
</script>
<template>
    <VRow>
        <VCol cols="12" sm="3" v-if="props.pg && user_info.level > 30">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.pg_id"
                :items="[{ id: null, pg_name: '전체' }].concat(pgs)" label="PG사 선택" item-title="pg_name" item-value="id" create />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.ps && user_info.level > 30">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.ps_id"
                :items="[{ id: null, name: '전체' }].concat(filterPgs)" label="구간 선택" item-title="name" item-value="id"
                create />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.pay_cond">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.settle_type"
                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산일 선택" item-title="name" item-value="id"
                create />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.terminal">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.terminal_id"
                :items="[{ id: null, name: '전체' }].concat(terminals)" label="장비 선택" item-title="name" item-value="id"
                create />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.cus_filter && user_info.level > 30">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.custom_id"
                :items="[{ id: null, name: '전체' }].concat(cus_filters)" label="커스텀 필터" item-title="name" item-value="id"
                create />
        </VCol>
        <slot name="extra_right"></slot>
        <VCol cols="12" sm="3">
            <VSelect v-model="store.params.page_size" density="compact" variant="outlined"
                :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" />
        </VCol>
    </VRow>
</template>
