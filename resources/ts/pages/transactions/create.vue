
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import TransactionsOverview from '@/views/transactions/TransactionsOverview.vue'
import { defaultItemInfo } from '@/views/transactions/useStore'
import type { Tab } from '@/views/types'
const {path, item } = defaultItemInfo()
const tabs = <Tab[]>([
    { icon: 'ic-outline-payments', title: '매출 정보' },
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
                    <TransactionsOverview :item="item"/>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
