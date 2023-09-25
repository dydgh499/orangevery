<script setup lang="ts">
import { SettlesHistories } from '@/views/types'
import { settlementHistoryFunctionCollect } from '@/views/transactions/settle-histories/SettleHistory'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'

interface Props {
    name: string,
    item: SettlesHistories,
    is_mcht: boolean
}

const props = defineProps<Props>()
const store = <any>(inject('store'))
const { deposit, cancel, download } = settlementHistoryFunctionCollect(store)
</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="230">
            <VList>
                <VListItem value="deposit" @click="deposit(props.item, props.is_mcht)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:report-money" />
                    </template>
                    <VListItemTitle>{{ props.item.status ? '입금취소처리' : '입금처리' }}</VListItemTitle>
                </VListItem>
                <VListItem value="cancel" @click="cancel(props.item, props.is_mcht)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:device-tablet-cancel" />
                    </template>
                    <VListItemTitle>정산취소</VListItemTitle>
                </VListItem>
                <VListItem value="download" @click="download(props.item, props.is_mcht)" style="width: fit-content;">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="vscode-icons:file-type-excel" />
                    </template>
                    <VListItemTitle style="width: fit-content;">
                        <BaseQuestionTooltip :location="'bottom'" :text="'정산매출 다운로드'" :content="'해당 정산에 사용되었던 매출건들이 다운로드 됩니다.(추가차감액은 추출되지 않습니다.)'">
                        </BaseQuestionTooltip>
                    </VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
