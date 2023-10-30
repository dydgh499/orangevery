<script setup lang="ts">
import { Settle } from '@/views/types'
import { SettlementFunctionCollect } from '@/views/transactions/settle/Settle'
import { getUserLevel } from '@axios'

interface Props {
    name: string,
    item: Settle,
    is_mcht: boolean
}
const props = defineProps<Props>()
const store = <any>(inject('store'))
const { settle, isAbleMchtDepositCollect, settleCollect } = SettlementFunctionCollect(store)
const depositCollect = <any>(inject('depositCollect'))
</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="230">
            <VList>
                <VListItem value="settle" @click="settle(props.name, props.item, props.is_mcht)" v-if="getUserLevel() >= 35">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler-calculator" />
                    </template>
                    <VListItemTitle>정산하기</VListItemTitle>
                </VListItem>
                <VListItem value="retry-realtime-deposit" class="retry-realtime-deposit" @click="depositCollect.show(props.item)"
                    v-if="isAbleMchtDepositCollect(props.is_mcht, props.item)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="fa6-solid:money-bill-transfer" />
                    </template>
                    <VListItemTitle>정산 및 이체</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
