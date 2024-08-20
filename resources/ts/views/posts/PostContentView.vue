<script lang="ts" setup>
import type { Post } from '@/views/types';

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
            <VCol cols="12">
                <VRow no-gutters style="align-items: center;">
                    <VCol cols="12" :md="1">
                        <label>제목</label>
                    </VCol>
                    <VCol cols="12" :md="11">
                        <div class="post-title">{{ props.post?.title }}</div>     
                    </VCol>
                </VRow>
            </VCol>
        </VRow>
        <VRow>
            <VCol cols="12">
                <VRow no-gutters style="align-items: center;">
                    <VCol cols="12">
                        <div v-html="props.post?.content" class="ql-editor" style=" min-height: 15em;border: 1px solid #d1d5db;">
                        </div>
                    </VCol>
                </VRow>
            </VCol>
        </VRow>
        <VCol cols="12">
            <div style="float: inline-end;">
                <span class="text-sm" style="margin-right: 1em;">
                    작성자: <span class="text-primary">{{ props.post?.writer}}</span>
                </span>
                <span class="text-sm">
                    활동 시간: <span class="text-primary">{{ props.post?.updated_at }}</span>
                </span>
            </div>
        </VCol>
    </VCardItem>
</template>
<style scoped>
.post-title {
  border-block-end: 1px solid rgba(var(--v-border-color), 0.5) !important;
  margin-inline-start: 0.5em;
  padding-block-end: 0.3em;
}

.ql-editor {
  box-sizing: border-box;
  border: 1px solid rgba(var(--v-border-color), 0.5) !important;
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

:deep(.ql-editor img) {
  inline-size: 100% !important;
}
</style>
