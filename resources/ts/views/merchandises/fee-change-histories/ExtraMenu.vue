<script setup lang="ts">
import type { FeeChangeHistory } from '@/views/types'
import { useRequestStore } from '@/views/request'

interface Props {
    item: FeeChangeHistory,
    type: string,
}

const props = defineProps<Props>()
const { remove } = useRequestStore()

</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="230" offset="14px">
            <VList>
                <VListItem value="history" @click="remove('/' + props.type + '/fee-change-histories', props.item.id, false)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:receipt" />
                    </template>
                    <VListItemTitle>삭제</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
<style scoped>
/deep/ .v-overlay__content {
  z-index: 99999999999 !important;
  inset-block-start: 4em !important;
  inset-inline-start: -19em !important;
}
</style>
