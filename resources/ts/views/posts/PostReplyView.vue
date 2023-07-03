
<script setup lang="ts">
import type { Post } from '@/views/types'
import { types } from '@/views/posts/useStore'
import PostReplyView from '@/views/posts/PostReplyView.vue'
import ExtraMenu from '@/views/posts/ExtraMenu.vue'

interface Props {
    post: Post,
    depth: number,
}
const props = defineProps<Props>()

const store = <any>(inject('store'))
const head = <any>(inject('head'))
const router = useRouter()

provide('store', store)
provide('head', head)
</script>
<template>
    <tr>
        <template v-for="(header, key, idx) in head.headers" :key="idx">
            <td v-show="header.visible" :class="key == 'title' ? 'list-square title' : 'list-square'">
                <span v-if="key == 'id'" class="edit-link" @click="store.edit(props.post['id'])">
                    #{{ props.post.id }}
                </span>
                <span v-else-if="key == 'type'">
                    <VChip :color="store.getSelectIdColor(types.find(obj => obj.id === props.post[key])?.id)">
                        {{ types.find(obj => obj.id === props.post[key])?.title }}
                    </VChip>
                </span>
                <span v-else-if="key == 'title'" :style="{ 'margin-left': `${props.depth * 20}px` }" class="edit-link"
                    @click="router.push('/posts/view/' + props.post['id'])">
                    <VIcon icon="gridicons:reply" size="20" class="me-2" />
                    <span>
                        {{ props.post.title }}
                    </span>
                </span>
                <span v-else-if="_key == 'extra_col'">
                    <ExtraMenu :item="props.post">
                    </ExtraMenu>
                </span>
                <span v-else>
                    {{ props.post[key] }}
                </span>
            </td>
        </template>
    </tr>
    <PostReplyView v-for="(reply, _idx) in post.replies" :key="_idx" :post="reply" :depth="++props.depth">
    </PostReplyView>
</template>
  