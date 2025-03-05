
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import { isFixplus } from '@/plugins/fixplus'
import FixplusOverview from '@/views/salesforces/FixplusOverview.vue'
import SalesforceOverview from '@/views/salesforces/SalesforceOverview.vue'
import { defaultItemInfo } from '@/views/salesforces/useStore'
import type { Tab } from '@/views/types'
import UserOverview from '@/views/users/UserOverview.vue'

const {path, item } = defaultItemInfo()
const tabs = <Tab[]>([])

if(isFixplus()) {
    tabs.push({ icon: 'tabler-user-check', title: '영업점정보' })
}
else {
    tabs.push(...[
        { icon: 'tabler-user-check', title: '개인정보' },
        { icon: 'ph-buildings', title: '영업점정보' },
    ])
}
const id = ref<number>(0)
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
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
                </template>
            </template>
        </CreateForm>
    </section>
</template>
