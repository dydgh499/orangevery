
<script setup lang="ts">
import CreateForm from '@/views/utils/CreateForm.vue'
import ComplaintOverview from '@/views/complaints/ComplaintOverview.vue';
import { useUpdateStore } from '@/views/complaints/useStore'
import type { Tab } from '@/views/types'
const {path, item } = useUpdateStore()
const tabs = <Tab[]>([
    { icon: 'tabler-user-check', title: '민원 정보' },
])
const id = ref<number>(0)
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
