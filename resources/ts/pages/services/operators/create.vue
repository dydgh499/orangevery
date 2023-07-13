
<script setup lang="ts">
import OperatorOverview from '@/views/services/operators/OperatorOverview.vue'
import { defaultItemInfo } from '@/views/services/operators/useStore'
import CreateForm from '@/layouts/utils/CreateForm.vue'
import type { Tab } from '@/views/types'
const {path, item } = defaultItemInfo()
const tabs = <Tab[]>([
    { icon: 'tabler-user-check', title: '개인정보' },
])
const id = ref<number>(0)
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <OperatorOverview :item="item" :id="id" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
