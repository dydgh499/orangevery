
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import { isFixplus } from '@/plugins/fixplus'
import FixplusOverview from '@/views/salesforces/FixplusOverview.vue'
import SalesforceOverview from '@/views/salesforces/SalesforceOverview.vue'
import { defaultItemInfo, isAbleThemeDesign } from '@/views/salesforces/useStore'
import BrandDesignOverview from '@/views/services/brands/BrandDesignOverview.vue'
import type { Tab } from '@/views/types'
import UserOverview from '@/views/users/UserOverview.vue'

const {path, item } = defaultItemInfo()

const IsAbleThemeDesign = computed(() => {
    return isAbleThemeDesign(item)
})

const getTab = computed(() => {
    const tabs = <Tab[]>([])
    if(isFixplus()) {
        tabs.push({ icon: 'tabler-user-check', title: '영업점정보' })
    }
    else {
        tabs.push(...[
            { icon: 'tabler-user-check', title: '개인정보' },
            { icon: 'ph-buildings', title: '영업점정보' },
        ])
        if(isAbleThemeDesign(item))
            tabs.push({ icon: 'tabler-color-filter', title: '테마디자인' })
    }
    return tabs
})
const id = ref<number>(0)
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="getTab" :item="item">
            <template #view>
                <template v-if="isFixplus()">
                    <VWindowItem>
                        <FixplusOverview :item="item"/>
                    </VWindowItem>
                </template>
                <template v-else>
                    <VWindowItem>
                        <UserOverview :item="item" :key="id" :is_mcht="false" />
                    </VWindowItem>
                    <VWindowItem>
                        <SalesforceOverview :item="item" />
                    </VWindowItem>
                    <VWindowItem v-if="IsAbleThemeDesign">
                        <BrandDesignOverview :item="item" :key="id"/>
                    </VWindowItem>
                </template>
            </template>
        </CreateForm>
    </section>
</template>
