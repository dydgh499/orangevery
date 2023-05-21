
<script setup lang="ts">
import PayModuleCard from '@/views/pay-modules/PayModuleCard.vue';
import CreateForm from '@/views/utils/CreateForm.vue'
import { useUpdateStore } from '@/views/pay-modules/usePayModStore'
import { useSalesHierarchicalStore } from '@/views/salesforces/useSalesStore'

const { flattenUp } = useSalesHierarchicalStore()
const { path, item } = useUpdateStore()
const tabs = [
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈정보' },
]
const id = ref<number>(0)
const route = useRoute()
const ancestors = ref<object[]>([]);
watchEffect(() => {
    id.value = Number(route.params.id) || 0
})
watchEffect(async () => {
    if (item.group_id != 0)
        ancestors.value = await flattenUp(item.group_id)
    console.log(ancestors.value)
})
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <PayModuleCard style="margin-top: 1em;" :item="item"
                        :ancestors="ancestors" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
