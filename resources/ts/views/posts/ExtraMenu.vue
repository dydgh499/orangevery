<script setup lang="ts">
import type { Post } from '@/views/types'
import router from '@/router'

interface Props {
    item: Post,
}

const props = defineProps<Props>()

</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="230">
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
