
<script setup lang="ts">
import MchtOverview from '@/views/merchandises/MchtOverview.vue'
import NotiOverview from '@/views/merchandises/noti-urls/NotiOverview.vue'
import PayModuleOldOverview from '@/views/merchandises/pay-modules/PayModuleOldOverview.vue'
import PayModuleOverview from '@/views/merchandises/pay-modules/PayModuleOverview.vue'
import UserOverview from '@/views/users/UserOverview.vue'

import CreateForm from '@/layouts/utils/CreateForm.vue'
import { defaultItemInfo } from '@/views/merchandises/useStore'
import corp from '@corp'

const {path, item } = defaultItemInfo()
const tabs = [
    { icon: 'tabler-user-check', title: '개인정보' },
    { icon: 'tabler-building-store', title: '가맹점정보' },
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈정보' },
]
if(corp.pv_options.paid.use_noti) {
    tabs.push({ icon: 'emojione:envelope', title: '노티정보' })
}
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
                    <UserOverview :item="item" :id="id" :is_mcht="true" />
                </VWindowItem>
                <VWindowItem>
                    <MchtOverview :item="item"/>
                </VWindowItem>
                <VWindowItem>
                    <Suspense>
                        <PayModuleOverview :item="item" v-if="corp.pv_options.free.pay_module_detail_view"/>
                        <PayModuleOldOverview :item="item" v-else/>               
                    </Suspense>
                </VWindowItem>
                <VWindowItem>
                    <Suspense>
                        <NotiOverview :item="item" />
                    </Suspense>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
