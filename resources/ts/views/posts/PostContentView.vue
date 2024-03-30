<script lang="ts" setup>
import type { Post } from '@/views/types'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { types } from '@/views/posts/useStore'

interface Props {
    post: Post,
    title: string
}
const props = defineProps<Props>()

</script>
<template>
    <VCardItem>
        <VCardTitle>
            <b>{{ title }}</b>
        </VCardTitle>
        <VRow class="pt-5">
            <CreateHalfVCol :mdl="1" :mdr="11">
                <template #name>제목</template>
                <template #input>
                    <div class="post-title">{{ props.post?.title }}</div>                    
                </template>
            </CreateHalfVCol>
        </VRow>
        <VRow>
            <CreateHalfVCol :mdl="1" :mdr="11" style='margin-bottom: 1em;'>
                <template #name>내용</template>
                <template #input>
                    <div v-html="props.post?.content" class="ql-editor" style=" min-height: 15em;border: 1px solid #d1d5db;">
                    </div>
                </template>
            </CreateHalfVCol>
        </VRow>
        <div style="float: right;">
            <span class="text-sm" style="margin-right: 1em">
                작성자: <span class="text-primary">{{ props.post?.writer}}</span>
            </span>
            <span class="text-sm">
                활동 시간: <span class="text-primary">{{ props.post?.updated_at }}</span>
            </span>
        </div>
    </VCardItem>
</template>
<style scoped>
.post-title {
    margin-left: 0.5em; 
    border-bottom:1px solid rgba(var(--v-border-color), 0.5); 
    padding-bottom: 0.3em;
}
.ql-editor {
  box-sizing: border-box;
  border: 1px solid rgba(var(--v-border-color), 0.5);
  border-radius: 0.5em;
  block-size: 100%;
  line-height: 1.42;
  min-block-size: 20em;
  outline: none;
  overflow-y: auto;
  padding-block: 12px;
  padding-inline: 15px;
  tab-size: 4;
  text-align: start;
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>
