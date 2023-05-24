<script lang="ts" setup>
import type { Post } from '@/views/types'
import { requiredValidator, nullValidator } from '@validators';
import Editor from '@/views/utils/Editor.vue';
import CreateHalfVCol from '@/views/utils/CreateHalfVCol.vue';

interface Props {
    item: Post,
}

interface Options {
    id: number,
    title:string,
}

const props = defineProps<Props>()

const types = <Options[]>([
    { id: 0, title: "공지사항" }, 
    { id: 2, title: "FAQ" },
    { id: 2, title: "1:1 문의" },
])

</script>
<template>
    <VRow class="match-height">
        <!-- 👉 개인정보 -->
        <VCol cols="12" md="12">
            <VCard>
                <VCardItem>
                    <VCardTitle>게시글 작성</VCardTitle>
                    <VRow class="pt-5">
                        <CreateHalfVCol>
                            <template #name>제목</template>
                            <template #input>
                                <VTextField id="nameHorizontalIcons" v-model="props.item.title"
                                    prepend-inner-icon="material-symbols-subtitles" placeholder="제목을 입력해주세요"
                                    persistent-placeholder :rules="[requiredValidator]" />
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <CreateHalfVCol style='margin-bottom: 4em;'>
                            <template #name>내용</template>
                            <template #input>
                                <Editor :content="toRef(props.item, 'content')"></Editor>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow class="pt-5">
                        <VCol md="8">
                        </VCol>
                        <VCol md="4" style="padding: 0;">
                            <CreateHalfVCol>
                                <template #name></template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="props.item.type"
                                    :items="types" prepend-inner-icon="fxemoji-notepage" label="게시글 타입 선택" />
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
