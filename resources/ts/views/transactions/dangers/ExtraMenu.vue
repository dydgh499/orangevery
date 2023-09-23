<script setup lang="ts">
import { Danger } from '@/views/types'
import { axios } from '@axios'

interface Props {
    item: Danger,
}

const props = defineProps<Props>()
const store = <any>(inject('store'))
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const check = async () => {
    const deposit_after_text = props.item.is_checked ? '미확인' : '확인'
    if (await alert.value.show('정말 ' + deposit_after_text + '처리 하시겠습니까?')) {
        try {
            const r = await axios.post(`/api/v1/manager/transactions/dangers/${props.item.id}/checked`, { checked : !props.item.is_checked })
            snackbar.value.show('성공하였습니다.', 'success')
            store.setTable()
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}
const destory = async () => {
    if (await alert.value.show('정말 삭제 하시겠습니까?')) {
        try {
            const r = await axios.delete(`/api/v1/manager/transactions/dangers/${props.item.id}`)
            snackbar.value.show('성공하였습니다.', 'success')
            store.setTable()
        }
        catch (e: any) {
            snackbar.value.show(e.response.data.message, 'error')
            const r = errorHandler(e)
        }
    }
}

</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="230">
            <VList>
                <VListItem value="deposit" @click="check()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" :icon="props.item.is_checked ? 'tabler:rotate-clockwise-2' : 'tabler:check'" />
                    </template>
                    <VListItemTitle>{{ props.item.is_checked ? '미확인' : '확인' }}</VListItemTitle>
                </VListItem>
                <VListItem value="cancel" @click="destory()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler-trash" />
                    </template>
                    <VListItemTitle>삭제하기</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
