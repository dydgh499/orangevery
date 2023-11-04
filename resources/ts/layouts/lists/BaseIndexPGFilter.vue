<script setup lang="ts">
import { useStore } from '@/views/services/pay-gateways/useStore'
import { user_info } from '@axios'

interface Props {
    pg: boolean,
    ps: boolean,
    settle_type: boolean,
    terminal: boolean,
    cus_filter: boolean,
}
const route = useRoute()
const props = defineProps<Props>()

const { pgs, pss, settle_types, terminals, cus_filters, psFilter } = useStore()
const store = <any>(inject('store'))

const filterPgs = computed(() => {
    const filter = pss.filter(item => {
        return item.pg_id == store.params.pg_id
    })
    store.params.ps_id = psFilter(filter, store.params.ps_id)
    return filter
})

onMounted(() => {
    if(route.query.pg_id)
        store.params.pg_id = route.query.pg_id
    if(route.query.ps_id)
        store.params.ps_id = route.query.ps_id
    if(route.query.settle_type)
        store.params.settle_type = route.query.settle_type
    if(route.query.terminal)
        store.params.terminal = route.query.terminal
    if(route.query.custom_id)
        store.params.custom_id = route.query.custom_id
    watchEffect(() => {
        store.setChartProcess()
        store.params.pg_id = store.params.pg_id
        store.params.ps_id = store.params.ps_id
        store.params.terminal = store.params.terminal
        store.params.custom_id = store.params.custom_id
        store.params.settle_type = store.params.settle_type
    })
    })
</script>
<template>
    <VRow>
        <VCol cols="12" sm="3" v-if="props.pg && user_info.level > 30">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.pg_id" :items="[{ id: null, pg_name: '전체' }].concat(pgs)"
                label="PG사 선택" item-title="pg_name" item-value="id" @update:modelValue="store.updateQueryString({pg_id: store.params.pg_id})"/>
        </VCol>
        <VCol cols="12" sm="3" v-if="props.ps && user_info.level > 30">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.ps_id"
                :items="[{ id: null, name: '전체' }].concat(filterPgs)" label="구간 필터" item-title="name" item-value="id"
               id="ps-filter" eager @update:modelValue="store.updateQueryString({ps_id: store.params.ps_id})"  />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.settle_type">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.settle_type"
                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산일 필터" item-title="name" item-value="id"
               id="settle_types-filter" eager @update:modelValue="store.updateQueryString({settle_type: store.params.settle_type})" />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.terminal">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.terminal"
                :items="[{ id: null, name: '전체' }].concat(terminals)" label="장비 필터" item-title="name" item-value="id"
               id="terminal-filter" eager @update:modelValue="store.updateQueryString({terminal: store.params.terminal})"  />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.cus_filter && user_info.level > 30">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.custom_id"
                :items="[{ id: null, name: '전체' }].concat(cus_filters)" label="커스텀 필터" item-title="name" item-value="id"
               id="custom-filter" eager @update:modelValue="store.updateQueryString({custom_id: store.params.custom_id})" />
        </VCol>
        <slot name="pg_extra_field"></slot>
        <VCol cols="12" sm="3">
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" eager @update:modelValue="store.updateQueryString({page_size: store.params.page_size})" />
        </VCol>
    </VRow>
</template>
<style>
#paze-size-filter .v-menu__content {
  inset-inline-start: 100em !important;
}
</style>
