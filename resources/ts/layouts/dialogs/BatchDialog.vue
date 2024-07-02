<script lang="ts" setup>
import MchtBatchOverview from '@/layouts/components/batch-updaters/MchtBatchOverview.vue'
import PayModuleBatchOverview from '@/layouts/components/batch-updaters/PayModuleBatchOverview.vue'
import SalesBatchOverview from '@/layouts/components/batch-updaters/SalesBatchOverview.vue'
import { ItemTypes } from '@core/enums'

interface Props {
    selected_idxs: number[],
    item_type: number,
}
const props = defineProps<Props>()
const emits = defineEmits(['update:select_idxs'])

const visible = ref(false)
const show = () => {
    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent style="max-width: 1000px;">
        <DialogCloseBtn @click="visible = !visible" />
        <MchtBatchOverview :selected_idxs="props.selected_idxs" :selected_sales_id="0" :selected_level="0" v-if="props.item_type === ItemTypes.Merchandise"
            @update:select_idxs="emits('update:select_idxs', $event)"/>
        <PayModuleBatchOverview :selected_idxs="props.selected_idxs" :selected_sales_id="0" :selected_level="0" v-if="props.item_type === ItemTypes.PaymentModule"
            @update:select_idxs="emits('update:select_idxs', $event)"/>
        <SalesBatchOverview :selected_idxs="props.selected_idxs" v-if="props.item_type === ItemTypes.Salesforce"
            @update:select_idxs="emits('update:select_idxs', $event)"/>
    </VDialog>
</template>
