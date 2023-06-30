
<script setup lang="ts">
import PayModuleCard from '@/views/merchandises/pay-modules/PayModuleCard.vue'
import CreateForm from '@/layouts/utils/CreateForm.vue'
import { useUpdateStore } from '@/views/merchandises/pay-modules/useStore'

const { path, item } = useUpdateStore()
const tabs = [
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈정보' },
]
const id = ref<number>(0)

</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <PayModuleCard style="margin-top: 1em;" :item="item" :able_mcht_chanage="true"/>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
