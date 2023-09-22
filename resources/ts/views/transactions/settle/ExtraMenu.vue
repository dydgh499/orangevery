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
const { settle } = SettlementFunctionCollect(store)

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
            </VList>
        </VMenu>
    </VBtn>
</template>
