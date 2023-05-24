<script setup lang="ts">
import { useSearchStore } from '@/views/complaints/useStore'
import BaseIndexOverview from '@/views/utils/BaseIndexOverview.vue';

const { store, setHeaders } = useSearchStore()
provide('store', store)
provide('setHeaders', setHeaders)

const metas = []
</script>
<template>
    <BaseIndexOverview :placeholder="`TID 검색`" :metas="metas" :add="true" :update="true">
        <template #options>
        </template>
        <template #name>민원</template>
    </BaseIndexOverview>
</template>
