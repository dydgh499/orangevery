
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import PostOverview from '@/views/posts/PostOverview.vue'
import { useUpdateStore } from '@/views/posts/useStore'
import type { Tab } from '@/views/types'
const {path, item } = useUpdateStore()
const tabs = <Tab[]>([
    { icon: 'tabler-user-check', title: '게시글 정보' },
])
const id = ref<number>(0)
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <PostOverview :item="item" :id="id" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
