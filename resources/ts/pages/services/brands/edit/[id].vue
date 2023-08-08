
<script setup lang="ts">
import BrandOverview from '@/views/services/brands/BrandOverview.vue'
import BrandDesignOverview from '@/views/services/brands/BrandDesignOverview.vue'
import BrandOptionOverview from '@/views/services/brands/BrandOptionOverview.vue'
import BrandAuthOverview from '@/views/services/brands/BrandAuthOverview.vue'
import { defaultItemInfo } from '@/views/services/brands/useStore'
import CreateForm from '@/layouts/utils/CreateForm.vue'
import type { Tab } from '@/views/types'
import corp from '@corp'
import { getUserLevel } from '@axios'

const {path, item } = defaultItemInfo()
const tabs = <Tab[]>([
    { icon: 'ph-buildings', title: '운영사정보' },
    { icon: 'tabler-color-filter', title: '테마디자인' },
    { icon: 'tabler-table-options', title: '추가옵션' },
])
if(corp.id === parseInt(process.env.MAIN_BRAND_ID) && getUserLevel() == 50) {
    tabs.push({ icon: 'carbon:two-factor-authentication', title: '권한옵션' })
}
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
                    <BrandOptionOverview :item="item.pv_options" :brand="item" />
                </VWindowItem>
                <VWindowItem>
                    <BrandAuthOverview :item="item.pv_options" :brand="item" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
