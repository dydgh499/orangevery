<script setup lang="ts">
import type { SalesSlip } from '@/views/types'
import { user_info } from '@axios'

interface Props {
    item: Post,
}

const props = defineProps<Props>()
const router = useRouter()

</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent">
            <VList>
                <VListItem value="saleslip" @click="router.push('/posts/reply?parent_id=' + props.item.id)">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="gridicons:reply" />
                    </template>
                    <VListItemTitle>답변하기</VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
