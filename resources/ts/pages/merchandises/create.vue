
<script setup lang="ts">
import UserOverview from '@/views/users/UserOverview.vue'
import MchtOverview from '@/views/merchandises/MchtOverview.vue'
import PayModuleOverview from '@/views/merchandises/pay-modules/PayModuleOverview.vue'

import { useUpdateStore } from '@/views/merchandises/useStore'
import CreateForm from '@/layouts/utils/CreateForm.vue'
import type { Tab } from '@/views/types'

const {path, item } = useUpdateStore()
const tabs = <Tab[]>([
    { icon: 'tabler-user-check', title: '개인정보' },
    { icon: 'ph-buildings', title: '가맹점정보' },
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈정보(가맹점 추가 후 가능)' },
])
const id = ref<number>(0)
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <UserOverview :item="item" :id="id" />
                </VWindowItem>
                <VWindowItem>
                    <MchtOverview :item="item"/>
                </VWindowItem>
                <VWindowItem>
                    <PayModuleOverview :item="item" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
