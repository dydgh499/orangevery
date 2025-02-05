
<script setup lang="ts">
import FixplusOverview from '@/views/merchandises/FixplusOverview.vue'
import MchtOverview from '@/views/merchandises/MchtOverview.vue'
import UserOverview from '@/views/users/UserOverview.vue'

import CreateForm from '@/layouts/utils/CreateForm.vue'
import { isFixplus } from '@/plugins/fixplus'
import { defaultItemInfo } from '@/views/merchandises/useStore'
import type { Tab } from '@/views/types'

const {path, item } = defaultItemInfo()
const tabs = <Tab[]>([])

if(isFixplus()) {
    tabs.push({ icon: 'tabler-user-check', title: '가맹점정보' })
}
else {
    tabs.push({ icon: 'tabler-user-check', title: '개인정보' })
    tabs.push({ icon: 'tabler-building-store', title: '가맹점정보' })
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
                        <UserOverview :item="item" :key="id" :is_mcht="true"/>
                    </VWindowItem>
                    <VWindowItem>
                        <MchtOverview :item="item"/>
                    </VWindowItem>
                </template>
            </template>
        </CreateForm>
    </section>
</template>
