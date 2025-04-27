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

watchEffect(() => {
    if(props.item.id === 0 && getUserLevel() < 35)
        props.item.type = 2
})
</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="12">
            <VCard>
                <VCardItem>
                    <VCardTitle class="pb-5">게시글 작성</VCardTitle>
                    <VRow v-if="getUserLevel() >= 30" style="align-items: center;">
                        <VCol md="2">
                            작성타입
                        </VCol>
                        <VCol md="3" cols="8">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.type"
                                    :items="types" prepend-inner-icon="fxemoji-notepage" 
                                    item-title="title" item-value="id" />
                        </VCol>
                    </VRow>
                    <VRow style="align-items: center;">
                        <VCol md="2">
                            제목
                        </VCol>
                        <VCol md="10" cols="10">
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
