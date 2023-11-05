<script setup lang="ts">
import { useSalesFilterStore, getIndexByLevel } from '@/views/salesforces/useStore'
import corp from '@corp'
import { user_info } from '@axios'

interface Props {
    show: boolean,
}
const route = useRoute()
const props = defineProps<Props>();
const store = <any>(inject('store'))
const { sales, setUnderSalesFilter } = useSalesFilterStore()
const levels = corp.pv_options.auth.levels
for (let i = 0; i < 6; i++) {
    const idx = (5 - i)
    if (route.query['sales' + idx + '_id'])
        store.params['sales' + idx + '_id'] = parseInt(route.query['sales' + idx + '_id'] as string)            
}

</script>
<template>
    <VRow>
        <template v-for="i in 6" :key="i">
            <VCol :cols="12" :sm="3"
                v-if="levels['sales' + (6 - i) + '_use'] && props.show && user_info.level >= getIndexByLevel(6 - i)">
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params['sales' + (6 - i) + '_id']"
                    :items="sales[6 - i].value" :label="levels['sales' + (6 - i) + '_name'] + ' 필터'" item-title="sales_name"
                    item-value="id"
                    @update:modelValue="[store.updateQueryString({ ['sales' + (6 - i) + '_id']: store.params['sales' + (6 - i) + '_id'] }), setUnderSalesFilter(6 - i, store.params)]" />
            </VCol>
        </template>
        <slot name="sales_extra_field"></slot>
    </VRow>
</template>
