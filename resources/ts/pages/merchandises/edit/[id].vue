
<script setup lang="ts">
import UserOverview from '@/views/users/UserOverview.vue';
import MchtOverview from '@/views/merchandises/MchtOverview.vue';
import PayModuleOverview from '@/views/pay-modules/PayModuleOverview.vue';
import CreateForm from '@/views/utils/CreateForm.vue'
import { useUpdateStore } from '@/views/merchandises/useMchtStore'
const {path, item } = useUpdateStore()
const tabs = [
    { icon: 'tabler-user-check', title: '개인정보' },
    { icon: 'ph-buildings', title: '가맹점정보' },
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈정보' },
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
                    <MchtOverview :item="item" />
                </VWindowItem>
                <VWindowItem>
                    <PayModuleOverview :item="item" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
