<script lang="ts" setup>
import type { Post } from '@/views/types'
import { requiredValidator } from '@validators'
import Editor from '@/layouts/utils/Editor.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { types } from '@/views/posts/useStore'
import { axios } from '@axios'

interface Props {
    item: Post,
}
const props = defineProps<Props>()

const errorHandler = <any>(inject('$errorHandler'))
const ori_post = ref<Post>()

watchEffect(() => {
    axios.get('/api/v1/manager/posts/' + props.item.parent_id)
        .then(r => {
            ori_post.value = r.data
            props.item.type = ori_post.value?.type as number
            props.item.title = ori_post.value?.title as string
            props.item.parent_id = ori_post.value?.id as number
        })
        .catch(e => {
            const r = errorHandler(e)
        })
})
</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="12">
            <VCard>
                <VCardItem>
                    <VCardTitle>{{ types.find(obj => obj.id === ori_post?.type)?.title }} 원글</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="2" :mdr="10">
                            <template #name>제목</template>
                            <template #input>
                                <VTextField :value="ori_post?.title"
                                    prepend-inner-icon="ic-round-subtitles" placeholder="제목을 입력해주세요" persistent-placeholder
                                    readonly />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="2" :mdr="10" style='margin-bottom: 4em;'>
                            <template #name>내용</template>
                            <template #input>
                                <div v-html="ori_post?.content" class="ql-editor" style="border: 1px solid #d1d5db;">
                                </div>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                </VCardItem>
                <VDivider />
                <VCardItem>
                    <VCardTitle>답변 작성</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="2" :mdr="10">
                            <template #name>제목</template>
                            <template #input>
                                <VTextField id="nameHorizontalIcons" v-model="props.item.title"
                                    prepend-inner-icon="ic-round-subtitles" placeholder="제목을 입력해주세요" persistent-placeholder
                                    :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="2" :mdr="10" style='margin-bottom: 4em;'>
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
