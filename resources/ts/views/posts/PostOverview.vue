<script lang="ts" setup>
import type { Post } from '@/views/types'
import { requiredValidator, nullValidator } from '@validators';
import Editor from '@/layouts/utils/Editor.vue';
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue';
import { types } from '@/views/posts/useStore'

interface Props {
    item: Post,
}
const props = defineProps<Props>()

</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="12">
            <VCard>
                <VCardItem>
                    <VCardTitle>게시글 작성</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="2" :mdr="10">
                            <template #name>제목</template>
                            <template #input>
                                <VTextField id="nameHorizontalIcons" v-model="props.item.title"
                                    prepend-inner-icon="ic-round-subtitles" placeholder="제목을 입력해주세요"
                                    persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol :mdl="2" :mdr="10" style='margin-bottom: 4em;'>
                            <template #name>내용</template>
                            <template #input>
                                <Editor :content="props.item.content" @update:content="props.item.content = $event"></Editor>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <VCol md="8">
                        </VCol>
                        <VCol md="4" style="padding: 0;">
                            <CreateHalfVCol :mdl="3" :mdr="9">
                                <template #name></template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.type"
                                    :items="types" prepend-inner-icon="fxemoji-notepage" label="게시글 타입 선택" 
                                    item-title="title" item-value="id" />
                                </template>
                            </CreateHalfVCol>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <!-- 👉 submit -->
    </VRow>
</template>
