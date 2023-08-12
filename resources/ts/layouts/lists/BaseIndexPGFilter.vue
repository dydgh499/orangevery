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
const props = defineProps<Props>()

const { pgs, pss, settle_types, terminals, cus_filters, psFilter } = useStore()
const store = <any>(inject('store'))

const pg = ref({ id: null, pg_name: '전체' })
const ps = ref({ id: null, name: '전체' })
const custom = ref({ id: null, name: '전체' })
const terminal = ref({ id: null, name: '전체' })
const settle_type = ref({ id: null, name: '전체' })

const filterPgs = computed(() => {
    const filter = pss.filter(item => {
        return item.pg_id == pg.value.id
    })
    ps.value.id = psFilter(filter, store.params.ps_id)
    return filter
})

watchEffect(() => {
    store.setChartProcess()
    store.params.pg_id = pg.value.id
    store.params.ps_id = ps.value.id
    store.params.terminal = terminal.value.id
    store.params.custom_id = custom.value.id
    store.params.settle_type = settle_type.value.id
})
</script>
<template>
    <VRow>
        <VCol cols="12" sm="3" v-if="props.pg && user_info.level > 30">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="pg" :items="[{ id: null, pg_name: '전체' }].concat(pgs)"
                label="PG사 선택" item-title="pg_name" item-value="id" return-object />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.ps && user_info.level > 30">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="ps"
                :items="[{ id: null, name: '전체' }].concat(filterPgs)" label="구간 선택" item-title="name" item-value="id"
                return-object id="ps-filter" :eager="true"  />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.settle_type">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="settle_type"
                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산일 선택" item-title="name" item-value="id"
                return-object id="settle_types-filter" :eager="true" />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.terminal">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="terminal"
                :items="[{ id: null, name: '전체' }].concat(terminals)" label="장비 선택" item-title="name" item-value="id"
                return-object id="terminal-filter" :eager="true"  />
        </VCol>
        <VCol cols="12" sm="3" v-if="props.cus_filter && user_info.level > 30">
            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="custom"
                :items="[{ id: null, name: '전체' }].concat(cus_filters)" label="커스텀 필터" item-title="name" item-value="id"
                return-object id="custom-filter" :eager="true" />
        </VCol>
        <slot name="pg_extra_field"></slot>
        <VCol cols="12" sm="3">
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" :eager="true" />
        </VCol>
    </VRow>
</template>
<style>
#paze-size-filter .v-menu__content {
  inset-inline-start: 100em !important;
}
</style>
