<script setup lang="ts">
import { OperatorHistory } from '@/views/types'
import { axios } from '@axios'

interface Props {
    item: OperatorHistory,
}

const props = defineProps<Props>()

const snackbar = <any>(inject('snackbar'))
const errorHandler = <any>(inject('$errorHandler'))

const check = async () => {
    try {
        snackbar.value.show('준비중입니다.', 'warning')
        //const r = await axios.get(`/api/v1/manager/services/operator-histories/${props.item.id}`)
    }
    catch (e: any) {
        snackbar.value.show(e.response.data.message, 'error')
        const r = errorHandler(e)
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
                        <VIcon size="24" class="me-3" :icon="'tabler:check'" />
                    </template>
                    <VListItemTitle>상세이력확인</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
