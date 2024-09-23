<script setup lang="ts">
import { useRequestStore } from '@/views/request'
import { notiSendHistoryInterface } from '@/views/transactions/transactions'
import type { NotiSendHistory } from '@/views/types'
import { getUserLevel } from '@axios'

interface Props {
    item: NotiSendHistory,
}

const props = defineProps<Props>()
const { get } = useRequestStore()
const { notiSend, notiRemove } = notiSendHistoryInterface()
const notiDetail = <any>(inject('notiDetail'))

const detail = async () => {
    const url = '/api/v1/manager/merchandises/noti-send-histories/'+props.item.id
    const r = await get(url)
    notiDetail.value.show(r.data)
}

</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="230">
            <VList>
                <VListItem value="retry" @click="notiSend(props.item.id)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="gridicons:reply" />
                    </template>
                    <VListItemTitle>재발송</VListItemTitle>
                </VListItem>
                <VListItem value="detail" @click="detail()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:check" />
                    </template>
                    <VListItemTitle>상세이력</VListItemTitle>
                </VListItem>
                <VListItem value="history" @click="notiRemove(props.item)" v-if="getUserLevel() >= 35">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:receipt" />
                    </template>
                    <VListItemTitle>삭제</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
