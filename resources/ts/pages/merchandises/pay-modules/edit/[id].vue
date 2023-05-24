
<script setup lang="ts">
import PayModuleCard from '@/views/merchandises/pay-modules/PayModuleCard.vue';
import { useUpdateStore } from '@/views/merchandises/pay-modules/useStore'
import CreateForm from '@/views/utils/CreateForm.vue'
import { useSalesHierarchicalStore } from '@/views/salesforces/useStore'

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
