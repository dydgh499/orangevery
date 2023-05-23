
<script setup lang="ts">
import BrandOverview from '@/views/services/brands/BrandOverview.vue';
import BrandDesignOverview from '@/views/services/brands/BrandDesignOverview.vue';
import BrandOptionOverview from '@/views/services/brands/BrandOptionOverview.vue';
import { useUpdateStore } from '@/views/services/brands/useStore'
import CreateForm from '@/views/utils/CreateForm.vue'
import type { Tab } from '@/views/types'

const {path, item } = useUpdateStore()
const tabs = <Tab[]>([
    { icon: 'ph-buildings', title: '운영사정보' },
    { icon: 'tabler-color-filter', title: '테마디자인' },
    { icon: 'tabler-table-options', title: '추가옵션' },
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
                    <BrandOverview :item="item" :id="id" />
                </VWindowItem>
                <VWindowItem>
                    <BrandDesignOverview :item="item" />
                </VWindowItem>
                <VWindowItem>
                    <BrandOptionOverview :item="item" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
