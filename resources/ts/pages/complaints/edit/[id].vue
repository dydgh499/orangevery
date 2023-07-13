
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import ComplaintOverview from '@/views/complaints/ComplaintOverview.vue'
import { defaultItemInfo } from '@/views/complaints/useStore'
import type { Tab } from '@/views/types'
const {path, item } = defaultItemInfo()
const tabs = <Tab[]>([
    { icon: 'tabler-user-check', title: '민원 정보' },
])
const id = ref<number>(0)
const route = useRoute()
watchEffect(() => {
    id.value = Number(route.params.id) || 0
})
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <ComplaintOverview :item="item" :id="id" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
