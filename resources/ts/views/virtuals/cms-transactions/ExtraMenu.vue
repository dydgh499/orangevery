<script setup lang="ts">
import type { CmsTransactionHistory } from '@/views/types';
import { withdrawInterface } from '@/views/virtuals/cms-transactions/useStore';

interface Props {
    item: CmsTransactionHistory,
}

const props = defineProps<Props>()

const store = <any>(inject('store'))

const { 
    cancelJobs 
} = withdrawInterface()

const cancelWithdrawBook = async () => {
    const r = await cancelJobs([props.item.id])
    if(r.status === 201) {
        store.setTable()
    }
}

</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="250">
            <VList>
                <VListItem
                    v-if="props.item.withdraw_status === 0"
                    value="single-deposit-cancel-job" 
                    class="single-deposit-cancel-job" 
                    @click="cancelWithdrawBook()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="material-symbols:free-cancellation-outline" />
                    </template>
                    <VListItemTitle>이체예약취소</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
