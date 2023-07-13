<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import PostPreView from '@/views/posts/PostPreView.vue'
import { defaultItemInfo } from '@/views/posts/useStore'
import type { Tab } from '@/views/types'
const {path, item } = defaultItemInfo()
const tabs = <Tab[]>([
    { icon: 'fxemoji-notepage', title: '게시글 정보' },
])
const id = ref<number>(0)
const route = useRoute()

watchEffect(() => {
    item.parent_id = Number(route.query.parent_id)
})
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <PostPreView :item="item"/>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
