<script lang="ts" setup>
import corp from '@/plugins/corp';
import type { PayModule } from '@/views/types';
import { isAbleModiyV2 } from '@axios';

interface Props {
    item: PayModule,
}

const props = defineProps<Props>()

</script>
<template>
    <VCardItem>
        <VCardSubtitle style="display: flex; justify-content: space-between;">
            <VChip variant="outlined">계약 및 결제 정보</VChip>
        </VCardSubtitle>
        <br>
        <VRow v-if="isAbleModiyV2(props.item, 'services/pay-modules')">
            <VCol md="6" cols="12">
                <VTextField type="text" v-model="props.item.mid" prepend-inner-icon="tabler-user"
                    placeholder="MID 입력" persistent-placeholder label="MID"
                    maxlength="50"/>
            </VCol>
            <VCol md="6">
                <VTextField type="text" v-model="props.item.tid" prepend-inner-icon="jam-key-f"
                    placeholder="TID 입력" persistent-placeholder label="TID"
                    maxlength="50"/>
            </VCol>
        </VRow>
        <VRow v-else>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">MID</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ props.item.mid }}
            </VCol>
            <VCol md="5" cols="6">
                <span class="font-weight-bold">TID</span>
            </VCol>
            <VCol md="7" cols="6">
                {{ props.item.tid }}
            </VCol>
        </VRow>
    </VCardItem>
    <slot name="edit"></slot>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}
</style>
