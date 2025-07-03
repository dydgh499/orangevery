<script setup lang="ts">
import type { BankAccount } from '@/views/types';
import { axios } from '@axios';

interface Props {
    item: BankAccount,
}

const props = defineProps<Props>()
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))
const store = <any>(inject('store'))
const errorHandler = <any>(inject('$errorHandler'))

const remove = async () => {
    if (await alert.value.show('정말 삭제 하시겠습니까?')) {
        try {
            const r = await axios.delete(`/api/v1/manager/virtuals/bank-accounts/${props.item.id}`)
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
        <VMenu activator="parent" width="250">
            <VList>
                <VListItem value="remove" @click="remove()">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler-trash" />
                    </template>
                    <VListItemTitle>삭제하기</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
