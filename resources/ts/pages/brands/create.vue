
<script setup lang="ts">
import BrandOverview from '@/views/brands/BrandOverview.vue';
import BrandDesignOverview from '@/views/brands/BrandDesignOverview.vue';
import BrandOptionOverview from '@/views/brands/BrandOptionOverview.vue';
import CreateForm from '@/views/utils/CreateForm.vue'
import { useUpdateStore } from '@/views/brands/useStore'
import type { Tab } from '@/views/types'
const {path, item } = useUpdateStore()
const tabs = <Tab[]>([
    { icon: 'ph-buildings', title: '운영사정보' },
    { icon: 'tabler-color-filter', title: '테마디자인' },
    { icon: 'tabler-table-options', title: '추가옵션' },
])
const id = ref<number>(0)
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
