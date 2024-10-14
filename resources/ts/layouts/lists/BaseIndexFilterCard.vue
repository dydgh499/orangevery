<script setup lang="ts">
import BaseIndexPGFilter from '@/layouts/lists/BaseIndexPGFilter.vue';
import BaseIndexSalesFilter from '@/layouts/lists/BaseIndexSalesFilter.vue';
import { getUserLevel } from '@axios';

interface Props {
    pg: boolean,
    ps: boolean,
    settle_type: boolean,
    terminal: boolean,
    cus_filter: boolean,
    sales: boolean,
}
const props = defineProps<Props>()
</script>
<template>
    <AppCardActions action-collapsed title="검색 옵션">
        <VDivider />
        <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
            <VCol cols="12" md="6" v-if="getUserLevel() > 10">
                <VCardText style="padding: 1em;">
                    <BaseIndexSalesFilter :show="sales">
                        <template #sales_extra_field>
                            <slot name="sales_extra_field"></slot>
                        </template>
                    </BaseIndexSalesFilter>
                </VCardText>
            </VCol>
            <VDivider :vertical="$vuetify.display.mdAndUp" v-if="getUserLevel() > 10"/>
            <VCol cols="12" md="6">
                <VCardText style="padding: 1em;">
                    <BaseIndexPGFilter :pg="props.pg" :ps="props.ps" :settle_type="props.settle_type" :terminal="props.terminal"
                        :cus_filter="props.cus_filter">
                        <template #pg_extra_field>
                            <slot name="pg_extra_field"></slot>
                        </template>
                    </BaseIndexPGFilter>
                </VCardText>
            </VCol>
        </div>
    </AppCardActions>
    <br>
</template>
<style scoped>
:deep(.v-card-item) {
  padding: 20px;
}
</style>
