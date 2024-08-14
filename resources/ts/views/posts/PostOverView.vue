<script lang="ts" setup>
import Editor from '@/layouts/utils/Editor.vue'
import { getUserLevel } from '@/plugins/axios'
import { types } from '@/views/posts/useStore'
import type { Post } from '@/views/types'
import { requiredValidatorV2 } from '@validators'

interface Props {
    item: Post,
}
const props = defineProps<Props>()
const getPostTypes = computed(() => {
    if(getUserLevel() >= 35)
        return types
    else {
        props.item.type = 2
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
                    <VCardTitle class="pb-5">게시글 작성</VCardTitle>
                    <VRow v-if="getUserLevel() >= 35">                        
                        <VCol md="1">
                            작성타입
                        </VCol>
                        <VCol md="2" cols="8">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.type"
                                    :items="getPostTypes" prepend-inner-icon="fxemoji-notepage" 
                                    item-title="title" item-value="id" />
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol md="1">
                            제목
                        </VCol>
                        <VCol md="11" cols="10">
                            <VTextField v-model="props.item.title"
                                prepend-inner-icon="ic-round-subtitles" placeholder="제목을 입력해주세요"
                                persistent-placeholder :rules="[requiredValidatorV2(props.item.title, '제목')]" />
                        </VCol>
                    </VRow>
                    <VRow>
                        <VCol>
                            <Editor :content="props.item.content" @update:content="props.item.content = $event"/>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>
