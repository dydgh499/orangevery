
<script setup lang="ts">
import UserOverview from '@/views/users/UserOverview.vue'
import SalesforceOverview from '@/views/salesforces/SalesforceOverview.vue'
import CreateForm from '@/layouts/utils/CreateForm.vue'
import { useUpdateStore } from '@/views/salesforces/useStore'

const {path, item } = useUpdateStore()
const tabs = [
{ icon: 'tabler-user-check', title: '개인정보' },
    { icon: 'ph-buildings', title: '영업점정보' },
]
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
                    <SalesforceOverview :item="item" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
