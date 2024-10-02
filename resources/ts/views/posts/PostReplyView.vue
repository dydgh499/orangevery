
<script setup lang="ts">
import router from '@/router'
import PostReplyView from '@/views/posts/PostReplyView.vue'
import { getContentTooltip, moveContent, types } from '@/views/posts/useStore'
import type { Post } from '@/views/types'
import { allLevels } from '@axios'

interface Props {
    post: Post,
    depth: number,
}
const props = defineProps<Props>()

const store = <any>(inject('store'))
const head = <any>(inject('head'))

const getChildDepth = computed(() => {
    return props.depth + 1
})
</script>
<template>
    <tr :style="`background: rgba(var(--v-theme-primary), 5%`">
        <template v-for="(header, key, idx) in head.headers" :key="idx">
            <td v-show="header.visible" :class="key == 'title' ? 'list-square title' : 'list-square'">
                <span v-if="key == 'id'" class="edit-link" @click="moveContent(props.post, store)">
                    #{{ props.post.id }}
                    <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                        {{ getContentTooltip(item) }}
                    </VTooltip>
                </span>
                <span v-else-if="key == 'type'">
                    <VChip :color="store.getSelectIdColor(types.find(obj => obj.id === props.post[key])?.id)">
                        {{ types.find(obj => obj.id === props.post[key])?.title }}
                    </VChip>
                </span>
                <span v-else-if="key == 'writer'">
                    <div>
                        <template v-if="props.post['level'] >= 35">                           
                            <VChip :color="store.getAllLevelColor(props.post['level'])">
                                운영자
                            </VChip>
                        </template>
                        <template v-else>
                            <span style="margin: 0.1em;">{{ props.post[key] }}</span>
                            <br>
                            <VChip :color="store.getAllLevelColor(props.post['level'])">
                                {{ allLevels().find(obj => obj.id === props.post['level'])?.title }}
                            </VChip>
                        </template>
                    </div>
                </span>
                <span v-else-if="key == 'title'" :style="`margin-left: ${props.depth * 20}px`" class="edit-link"
                    @click="moveContent(props.post, store)">
                    <VIcon icon="gridicons:reply" size="20" class="me-2" />
                    <span>
                        RE: {{ props.post.title }}
                        <VTooltip activator="parent" location="top" transition="scale-transition" v-if="$vuetify.display.smAndDown === false">
                            {{ getContentTooltip(item) }}
                        </VTooltip>
                    </span>
                </span>
                <span v-else-if="key == 'extra_col'">
                    <VBtn size="small" type="button" color="primary" @click="router.push('/posts/reply?parent_id=' + props.post['id'])">
                        <VIcon size="22" icon="gridicons:reply" />
                        답변하기
                    </VBtn>
                </span>
                <span v-else>
                    {{ props.post[key] }}
                </span>
            </td>
        </template>
    </tr>
    <PostReplyView v-for="(reply, _idx) in post.replies" :key="_idx" :post="reply" :depth="getChildDepth"/>
</template>
  