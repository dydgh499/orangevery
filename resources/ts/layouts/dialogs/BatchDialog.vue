<script lang="ts" setup>
import MchtBatchOverview from '@/layouts/components/batch-updaters/MchtBatchOverview.vue'
import PayModuleBatchOverview from '@/layouts/components/batch-updaters/PayModuleBatchOverview.vue'
import SalesBatchOverview from '@/layouts/components/batch-updaters/SalesBatchOverview.vue'
import TransactionBatchOverview from '@/layouts/components/batch-updaters/TransactionBatchOverview.vue'
import { ItemTypes } from '@core/enums'

interface Props {
    selected_idxs: number[],
    selected_sales_id?: number,
    selected_level?: number,
    item_type: number,
}
const props = withDefaults(defineProps<Props>(), {
    selected_sales_id: 0,
    selected_level: 0,
})
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
    <VDialog v-model="visible" persistent :style="`max-width: ${props.item_type === ItemTypes.Transaction ? '700' : '1100'}px;`">
        <DialogCloseBtn @click="visible = !visible" />
        <MchtBatchOverview :selected_idxs="props.selected_idxs" :selected_sales_id="props.selected_sales_id" :selected_level="props.selected_level" v-if="props.item_type === ItemTypes.Merchandise"
            @update:select_idxs="emits('update:select_idxs', $event)"/>
        <PayModuleBatchOverview :selected_idxs="props.selected_idxs" :selected_sales_id="props.selected_sales_id" :selected_level="props.selected_level" v-if="props.item_type === ItemTypes.PaymentModule"
            @update:select_idxs="emits('update:select_idxs', $event)"/>
        <SalesBatchOverview :selected_idxs="props.selected_idxs" v-if="props.item_type === ItemTypes.Salesforce"
            @update:select_idxs="emits('update:select_idxs', $event)"/>
        <TransactionBatchOverview :selected_idxs="props.selected_idxs" v-if="props.item_type === ItemTypes.Transaction"
            @update:select_idxs="emits('update:select_idxs', $event)"/>
    </VDialog>
</template>
<style scoped>
:deep(.v-input--density-compact) {
  --v-input-control-height: 30px !important;
}
</style>
