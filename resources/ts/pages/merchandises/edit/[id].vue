
<script setup lang="ts">
import { isFixplus } from '@/plugins/fixplus'
import FixplusOverview from '@/views/merchandises/FixplusOverview.vue'
import MchtOverview from '@/views/merchandises/MchtOverview.vue'
import NotiOverview from '@/views/merchandises/noti-urls/NotiOverview.vue'
import PayModuleOldOverview from '@/views/merchandises/pay-modules/PayModuleOldOverview.vue'
import PayModuleOverview from '@/views/merchandises/pay-modules/PayModuleOverview.vue'
import UserOverview from '@/views/users/UserOverview.vue'

import CreateForm from '@/layouts/utils/CreateForm.vue'
import corp from '@/plugins/corp'
import { notiViewable } from '@/views/merchandises/noti-urls/useStore'
import { defaultItemInfo } from '@/views/merchandises/useStore'
import type { Tab } from '@/views/types'

const id    = ref<number>(0)
const route = useRoute()
const {path, item } = defaultItemInfo()
const tabs = <Tab[]>([])

if(isFixplus()) {
    tabs.push({ icon: 'tabler-user-check', title: '가맹점정보' })
}
else {
    tabs.push({ icon: 'tabler-user-check', title: '개인정보' })
    tabs.push({ icon: 'tabler-building-store', title: '가맹점정보' })
    
    if(corp.pv_options.paid.brand_mode === 0) {
        tabs.push({ icon: 'ic-outline-send-to-mobile', title: '결제모듈정보' })
        if(notiViewable(id.value)) 
            tabs.push({ icon: 'emojione:envelope', title: '노티정보' })
    }
}

watchEffect(() => {
    id.value = Number(route.params.id) || 0
})
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <template v-if="isFixplus()">
                    <VWindowItem>
                        <FixplusOverview :item="item" :key="id"/>
                    </VWindowItem>
                </template>
                <template v-else>
                    <VWindowItem>
                        <UserOverview :item="item" :key="id" :is_mcht="true"/>
                    </VWindowItem>
                    <VWindowItem>
                        <MchtOverview :item="item"/>
                    </VWindowItem>
                    <template v-if="corp.pv_options.paid.fee_input_mode === false">
                        <VWindowItem>
                            <Suspense>
                                <PayModuleOverview :item="item" v-if="corp.pv_options.free.pay_module_detail_view"/>
                                <PayModuleOldOverview :item="item" v-else/>
                            </Suspense>
                        </VWindowItem>
                        <VWindowItem v-if="notiViewable(id)">
                            <Suspense>
                                <NotiOverview :item="item" />
                            </Suspense>
                        </VWindowItem>
                    </template>
                </template>
            </template>
        </CreateForm>
    </section>
</template>
