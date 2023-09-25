<script setup lang="ts">
import { Settle } from '@/views/types'
import { SettlementFunctionCollect } from '@/views/transactions/settle/Settle'

interface Props {
    name: string,
    item: Settle,
    is_mcht: boolean
}
const props = defineProps<Props>()
const store = <any>(inject('store'))
const { settle, isAbleMchtDepositCollect, settleCollect } = SettlementFunctionCollect(store)

</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="230">
            <VList>
                <VListItem value="settle" @click="settle(props.name, props.item, props.is_mcht)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler-calculator" />
                    </template>
                    <VListItemTitle>정산하기</VListItemTitle>
                </VListItem>
                <VListItem value="retry-realtime-deposit" class="retry-realtime-deposit" @click="settleCollect(props.name, props.item)"
                    v-if="isAbleMchtDepositCollect(props.is_mcht)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="fa6-solid:money-bill-transfer" />
                    </template>
                    <VListItemTitle>정산 및 이체</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
