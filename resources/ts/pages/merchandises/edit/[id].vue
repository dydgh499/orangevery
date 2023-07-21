
<script setup lang="ts">
import UserOverview from '@/views/users/UserOverview.vue'
import MchtOverview from '@/views/merchandises/MchtOverview.vue'
import NotiOverview from '@/views/merchandises/NotiOverview.vue'
import PayModuleOverview from '@/views/merchandises/pay-modules/PayModuleOverview.vue'
import CreateForm from '@/layouts/utils/CreateForm.vue'
import { defaultItemInfo } from '@/views/merchandises/useStore'

const {path, item } = defaultItemInfo()
const tabs = [
    { icon: 'tabler-user-check', title: '개인정보' },
    { icon: 'tabler-building-store', title: '가맹점정보' },
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈정보' },
    { icon: 'streamline:interface-time-alarm-notification-alert-bell-wake-clock-alarm', title: '노티정보' },
]
const id    = ref<number>(0)
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
                    <UserOverview :item="item" :id="id" />
                </VWindowItem>
                <VWindowItem>
                    <MchtOverview :item="item"/>
                </VWindowItem>
                <VWindowItem>
                    <PayModuleOverview :item="item" />
                </VWindowItem>
                <VWindowItem>
                    <NotiOverview :item="item" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
