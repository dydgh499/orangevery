<script lang="ts" setup>
import { useDynamicTabStore } from '@/@core/utils/dynamic_tab'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import Editor from '@/layouts/utils/Editor.vue'
import PostContentView from '@/views/posts/PostContentView.vue'
import { types } from '@/views/posts/useStore'
import type { Post } from '@/views/types'
import { axios } from '@axios'
import { requiredValidatorV2 } from '@validators'

interface Props {
    item: Post,
}
const props = defineProps<Props>()

const route = useRoute()
const store = useDynamicTabStore()
const errorHandler = <any>(inject('$errorHandler'))
const ori_posts = ref<Post[]>([])

watchEffect(() => {
    if(route.query.parent_id) {
        axios.get('/api/v1/manager/posts/' + route.query.parent_id + '/parent')
        .then(r => {
            ori_posts.value = r.data
            if(ori_posts.value.length) {
                const last_idx = ori_posts.value.length - 1
                props.item.type = ori_posts.value[last_idx]?.type
                props.item.title = ori_posts.value[last_idx]?.title
                props.item.parent_id = ori_posts.value[last_idx]?.id
            }
        })
        .catch(e => {
            const r = errorHandler(e)
        })
    }
})

watchEffect(() => {
    if(route.query.parent_id) {
        const type = types.find(obj => obj.id === props.item.type)
        if(type && (route.fullPath.includes('/reply?') && route.fullPath.includes(route.query.parent_id as string))) {
            const idx = store.tabs.findIndex(obj => obj.path === route.fullPath)
            if(idx !== -1) {
                store.tabs[idx].title = type.title + ` 답변(#${route.query.parent_id})`
            }
        }
    }
})
</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="12">
            <VCard>
                <template v-for="(ori_post, key) in ori_posts" :key="key">
                    <PostContentView :post="ori_post" :title="types.find(obj => obj.id === ori_post.type)?.title + ' 원글'"/>
                    <VDivider />
                </template>
                <VCardItem>
                    <VCardTitle>답변 작성</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="1" :mdr="11">
                            <template #name>제목</template>
                            <template #input>
                                <VTextField v-model="props.item.title"
                                    prepend-inner-icon="ic-round-subtitles" placeholder="제목을 입력해주세요" persistent-placeholder
                                    :rules="[requiredValidatorV2(props.item.title, '제목')]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="1" :mdr="11" style='margin-bottom: 1em;'>
                            <template #name>내용</template>
                            <template #input>
                                <Editor :content="props.item.content" @update:content="props.item.content = $event">
                                </Editor>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>
