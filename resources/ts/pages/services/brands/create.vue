
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import BrandAuthOverview from '@/views/services/brands/BrandAuthOverview.vue'
import BrandOptionOverview from '@/views/services/brands/BrandOptionOverview.vue'
import BrandDesignOverview from '@/views/services/brands/BrandDesignOverview.vue'

import corp from '@/plugins/corp'
import BrandOverview from '@/views/services/brands/BrandOverview.vue'
import { defaultItemInfo } from '@/views/services/brands/useStore'
import type { Tab } from '@/views/types'

const {path, item } = defaultItemInfo()
const tabs = <Tab[]>([
    { icon: 'ph-buildings', title: '운영사정보' },
    { icon: 'tabler-color-filter', title: '테마디자인' },
    { icon: 'tabler-table-options', title: '추가옵션' },
    { icon: 'carbon:two-factor-authentication', title: '유료옵션' },
])

const id = ref<number>(0)
</script>
<template>
    <section>
        <CreateForm :id="corp.id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <BrandOverview :item="item" :id="corp.id" />
                </VWindowItem>
                <VWindowItem>
                    <BrandDesignOverview :item="item" />
                </VWindowItem>
                <VWindowItem>
                    <BrandOptionOverview :item="item.pv_options" :key="item.id"/>
                </VWindowItem>
                <VWindowItem>
                    <BrandAuthOverview :item="item.pv_options"/>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
