
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import PostOverView from '@/views/posts/PostOverView.vue'
import { defaultItemInfo } from '@/views/posts/useStore'
import type { Tab } from '@/views/types'

const {path, item } = defaultItemInfo()
const tabs = <Tab[]>([
    { icon: 'fxemoji-notepage', title: '게시글 정보' },
])
const id = ref<number>(0)
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <PostOverView :item="item" :id="id" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
