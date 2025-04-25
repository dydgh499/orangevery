<script setup lang="ts">
import type { VirtualAccountHistory } from '@/views/types';
import { withdrawInterface } from '@/views/virtual-accounts/histories/useStore';
import { getUserLevel } from '@axios';

interface Props {
    item: VirtualAccountHistory,
}

const props = defineProps<Props>()

const store = <any>(inject('store'))
    
const tradeAmbassadorDialog = <any>(inject('tradeAmbassadorDialog'))
const withdrawHistoriesDialog = <any>(inject('withdrawHistoriesDialog'))
const withdrawStatusmentDialog = <any>(inject('withdrawStatusmentDialog'))

const { 
    withdrawRetry, getSuccessResultId,
    cancelJobs 
} = withdrawInterface()

const retryDeposit = async () => {
    const r = await withdrawRetry(props.item.id)
    if(r) {
        if(r.status == 201) {
            store.setChartProcess()
            store.setTable()
        }
    }
}
</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="250">
            <VList>
                <VListItem 
                    v-if="props.item.withdraw_status === 1"
                    value="withdraw-print" 
                    @click="withdrawStatusmentDialog.show(getSuccessResultId(props.item.withdraws), 0)"
                    >
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:receipt-2" />
                    </template>
                    <VListItemTitle>이체내역서</VListItemTitle>
                </VListItem>
                <VListItem 
                    v-else-if="props.item.trans_type === 1 && [2, 4].includes(props.item.withdraw_status) && getUserLevel() >= 35"
                    value="retry-realtime-deposit" 
                    class="retry-realtime-deposit"
                    @click="retryDeposit()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="fa6-solid:money-bill-transfer" />
                    </template>
                    <VListItemTitle>재이체</VListItemTitle>
                </VListItem>
                <VListItem
                    v-else-if="props.item.trans_type === 1 && props.item.withdraw_status === 0 && getUserLevel() >= 35"
                    value="single-deposit-cancel-job" 
                    class="single-deposit-cancel-job" 
                    @click="cancelJobs([props.item.trx_id])">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="material-symbols:free-cancellation-outline" />
                    </template>
                    <VListItemTitle>이체예약취소</VListItemTitle>
                </VListItem>
                <VListItem 
                    v-if="props.item.trans_type === 1"                
                    value="withdraw-histories" 
                    @click="withdrawHistoriesDialog.show(props.item['withdraws'])">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:history" />
                    </template>
                    <VListItemTitle>출금시도이력</VListItemTitle>
                </VListItem>
                <VListItem 
                    v-if="props.item.trans_type === 0 || props.item.withdraw_status === 1 && props.item.withdraw_type === 1"
                    value="trade-ambassador" 
                    @click="tradeAmbassadorDialog.show(props.item)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:history" />
                    </template>
                    <VListItemTitle>거래대사</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
