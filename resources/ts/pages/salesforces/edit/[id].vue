
<script setup lang="ts">
import UserOverview from '@/views/users/UserOverview.vue';
import SalesforceOverview from '@/views/salesforces/SalesforceOverview.vue';
import CreateForm from '@/layouts/utils/CreateForm.vue'
import { useUpdateStore } from '@/views/salesforces/useStore'

const {path, item } = useUpdateStore()
const tabs = [
    { icon: 'tabler-man', title: '영업자정보' },
]
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
                    <UserOverview :item="item" :id="id" />
                    <SalesforceOverview :item="item" />
                </VWindowItem>
                <VWindowItem>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
