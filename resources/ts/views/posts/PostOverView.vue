<script lang="ts" setup>
import type { Post } from '@/views/types'
import { requiredValidator } from '@validators'
import Editor from '@/layouts/utils/Editor.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { types } from '@/views/posts/useStore'
import { getUserLevel } from '@/plugins/axios'

interface Props {
    item: Post,
}
const props = defineProps<Props>()
const getPostTypes = computed(() => {
    if(getUserLevel() >= 35)
        return types
    else {
        return [{ id: 2, title: "1:1 문의" }]
    }
})
</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="12">
            <VCard>
                <VCardItem>
                    <VCardTitle>게시글 작성</VCardTitle>
                    <VRow  class="pt-5">                        
                        <VCol md="1">
                            작성타입
                        </VCol>
                        <VCol md="2">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.type"
                                    :items="getPostTypes" prepend-inner-icon="fxemoji-notepage" label="게시글 타입 선택" 
                                    item-title="title" item-value="id" />
                        </VCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="1" :mdr="11">
                            <template #name>제목</template>
                            <template #input>
                                <VTextField id="nameHorizontalIcons" v-model="props.item.title"
                                    prepend-inner-icon="ic-round-subtitles" placeholder="제목을 입력해주세요"
                                    persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <CreateHalfVCol :mdl="1" :mdr="11">
                            <template #name>내용</template>
                            <template #input>
                                <Editor :content="props.item.content" @update:content="props.item.content = $event"></Editor>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>
