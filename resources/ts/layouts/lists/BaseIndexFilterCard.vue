<script setup lang="ts">
import BaseIndexPGFilter from '@/layouts/lists/BaseIndexPGFilter.vue';
import { getUserLevel } from '@axios';

interface Props {
    pg: boolean,
    ps: boolean,
    settle_type: boolean,
    terminal: boolean,
    cus_filter: boolean,
    sales: boolean,
    page: boolean
}
const props = withDefaults(defineProps<Props>(), {
    page: true
})
</script>
<template>
    <AppCardActions action-collapsed title="검색 옵션">
        <VDivider />
        <div class="d-flex justify-space-between flex-wrap flex-md-nowrap flex-column flex-md-row">
            <VCol cols="12" md="6">
                <VCardText style="padding: 1em;">
                    <BaseIndexPGFilter :pg="props.pg" :ps="props.ps" :settle_type="props.settle_type" :terminal="props.terminal"
                        :cus_filter="props.cus_filter" :page="props.page">
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
