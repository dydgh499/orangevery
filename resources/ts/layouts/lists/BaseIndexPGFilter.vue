<script setup lang="ts">
import { useStore } from '@/views/services/pay-gateways/useStore';

interface Props {
    pg : boolean,
    ps : boolean,
    pay_cond : boolean,
    terminal : boolean,
    cus_filter : boolean,
}
const props = defineProps<Props>()

const { pgs, pss, pay_conds, ternimals, cus_filters, setFee, setAmount } = useStore()
const store = <any>(inject('store'))

const filterPgs = computed(() => {
    const filter = pss.filter(item => {
        return item.pg_id == store.params.custom.pg_id;
    })
    if (pss.length > 0) {
        if (filter.length > 0) {
            let item = pss.find(item => item.id === store.params.custom.ps_id)
            if (item != undefined && filter[0].pg_id != item.pg_id)
                store.params.custom.ps_id = null
        }
        else
            store.params.custom.ps_id = null
    }
    return filter
})
</script>
<template>
        <VRow>
            <VCol cols="12" sm="3" v-if="props.pg">
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.custom.pg_id" :items="pgs"
                    label="PG사 선택" item-title="pg_nm" item-value="id"/>
            </VCol>
            <VCol cols="12" sm="3" v-if="props.ps">
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.custom.ps_id" :items="filterPgs"
                   label="구간 선택" item-title="name" item-value="id"
                    persistent-hint />
            </VCol>
            <VCol cols="12" sm="3" v-if="props.pay_cond">
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.custom.pay_cond_id" :items="pay_conds"
                   label="결제조건 선택" item-title="name" item-value="id"
                    persistent-hint />
            </VCol>
            <VCol cols="12" sm="3" v-if="props.terminal">
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.custom.ternimal_id" :items="ternimals"
                    label="단말기 선택" item-title="name" item-value="id"
                    persistent-hint />
            </VCol>
            <VCol cols="12" sm="3" v-if="props.cus_filter">
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.custom.custom_id" :items="cus_filters"
                    label="커스텀 필터" item-title="name" item-value="id"
                    persistent-hint />
            </VCol>
            <VCol cols="12" sm="3">
                <VSelect v-model="store.params.page_size" density="compact" variant="outlined" :items="[10, 20, 30, 50, 100, 200, 500, 1000]"
                    label="표시 개수" />
            </VCol>
            <slot name="extra_right_col"></slot>
        </VRow>
</template>
