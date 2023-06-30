
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import ComplaintOverview from '@/views/complaints/ComplaintOverview.vue'
import { useUpdateStore } from '@/views/complaints/useStore'
import type { Tab } from '@/views/types'
const {path, item } = useUpdateStore()
const tabs = <Tab[]>([
    { icon: 'tabler-user-check', title: '민원 정보' },
])
const id = ref<number>(0)
const route = useRoute()
watchEffect(() => {
    id.value = Number(route.params.id) || 0
    item.pg_id =  Number(route.query.pg_id) || null
    item.appr_num =  route.query.appr_num as string
    item.appr_dt =  new Date(route.query.appr_dt as string)
    item.cust_name =  route.query.cust_name as string
    item.mcht_id =  Number(route.query.mcht_id) || null
    item.phone_num =  route.query.phone_num as string
    item.tid =  route.query.tid as string
})
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <ComplaintOverview :item="item" :id="id" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
