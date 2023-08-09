<script setup lang="ts">
import type { NotiSendHistory } from '@/views/types'
import { useRequestStore } from '@/views/request'

interface Props {
    item: NotiSendHistory,
}

const props = defineProps<Props>()
const { remove, post, get } = useRequestStore()

const store = <any>(inject('store'))
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const notiDetail = <any>(inject('notiDetail'))

const retry = async () => {
    if(await alert.value.show('정말 재발송하시겠습니까?'))
    {
        const url = '/api/v1/manager/merchandises/noti-send-histories/'+props.item.trans_id+'/retry'
        const r = await post(url, {})
        if(r.status == 201)
            snackbar.value.show('성공하였습니다.', 'success')
        else
            snackbar.value.show(r.data.message, 'error')
        store.setTable()
    }
}

const detail = async () => {
    const url = '/api/v1/manager/merchandises/noti-send-histories/'+props.item.trans_id
    const r = await get(url)
    notiDetail.value.show(r.data)
}

</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="230">
            <VList>
                <VListItem value="retry" @click="retry()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="gridicons:reply" />
                    </template>
                    <VListItemTitle>재발송</VListItemTitle>
                </VListItem>
                <VListItem value="retry" @click="detail()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:check" />
                    </template>
                    <VListItemTitle>상세이력</VListItemTitle>
                </VListItem>
                <VListItem value="history" @click="remove('/merchandises/noti-send-histories', props.item.id, false)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:receipt" />
                    </template>
                    <VListItemTitle>삭제</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
