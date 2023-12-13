<script setup lang="ts">
import { OperatorHistory } from '@/views/types'
import { useRequestStore } from '@/views/request'

interface Props {
    item: OperatorHistory,
}

const props = defineProps<Props>()

const { get } = useRequestStore()
const operDetail = <any>(inject('operDetail'))

const check = async () => {
    const r = await get(`/api/v1/manager/services/operator-histories/${props.item.id}`)
    operDetail.value.show(r.data)
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
                    <VListItemTitle>상세이력</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
