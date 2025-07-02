<script setup lang="ts">
import { transactionColors } from '@/@core/enums';
import type { CmsTransactionBooks } from '@/views/types';
import { withdrawInterface, withdrawStatusCode } from '@/views/virtuals/cms-transaction-books/useStore';
import { getUserLevel } from '@axios';

interface Props {
    item: CmsTransactionBooks,
}

const props = defineProps<Props>()

const store = <any>(inject('store'))

const { 
    cancelJobs 
} = withdrawInterface()

const cancelWithdrawBook = async () => {
    const r = await cancelJobs([props.item.trans_seq_num])
    if(r.status == 201) {
        store.setTable()
    }
}

const isBookCancelAble = () => {
    const code = withdrawStatusCode(props.item)
    return code === transactionColors.Book && getUserLevel() >= 35
}

</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="250">
            <VList>
                <VListItem
                    v-if="props.item.is_withdraw === 1 && isBookCancelAble()"
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
