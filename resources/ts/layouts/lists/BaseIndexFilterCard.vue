<script setup lang="ts">
import BaseIndexSalesFilter from '@/layouts/lists/BaseIndexSalesFilter.vue'
import BaseIndexPGFilter from '@/layouts/lists/BaseIndexPGFilter.vue'

interface Props {
    pg: boolean,
    ps: boolean,
    settle_type: boolean,
    terminal: boolean,
    cus_filter: boolean,
    sales: boolean,
}
const props = defineProps<Props>();
</script>
<template>
    <AppCardActions action-collapsed title="검색 옵션">
        <VDivider />
        <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
            <VCol cols="12" md="6">
                <VCardText style="padding: 1em;">
                    <BaseIndexSalesFilter :show="sales">
                        <template #extra_left>
                            <slot name="extra_left"></slot>
                        </template>
                    </BaseIndexSalesFilter>
                </VCardText>
            </VCol>
            <VDivider :vertical="$vuetify.display.mdAndUp" />
            <VCol cols="12" md="6">
                <VCardText style="padding: 1em;">
                    <BaseIndexPGFilter :pg="props.pg" :ps="props.ps" :settle_type="props.settle_type" :terminal="props.terminal"
                        :cus_filter="props.cus_filter">
                        <template #extra_right>
                            <slot name="extra_right"></slot>
                        </template>
                    </BaseIndexPGFilter>
                </VCardText>
            </VCol>
        </div>
    </AppCardActions>
</template>
<style scoped>
:deep(.v-card-item) {
  padding: 20px;
}
</style>
