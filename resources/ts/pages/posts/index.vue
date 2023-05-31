<script setup lang="ts">
import { useSearchStore } from '@/views/posts/useStore'
import BaseIndexOverview from '@/layouts/lists/BaseIndexOverview.vue';

const { store, setHeaders } = useSearchStore()
provide('store', store)
provide('setHeaders', setHeaders)
</script>
<template>
    <BaseIndexOverview :placeholder="`제목 검색`" :metas="[]" :add="true" :update="true">
        <template #options>
        </template>
        <template #name>게시글</template>
    </BaseIndexOverview>
</template>
