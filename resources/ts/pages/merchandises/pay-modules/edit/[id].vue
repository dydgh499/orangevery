
<script setup lang="ts">
import PayModuleCard from '@/views/merchandises/pay-modules/PayModuleCard.vue'
import { useUpdateStore } from '@/views/merchandises/pay-modules/useStore'
import { useMchtFilterStore } from '@/views/merchandises/useStore'
import CreateForm from '@/layouts/utils/CreateForm.vue'

const { path, item } = useUpdateStore()
const { merchandises, getAllMerchandises } = useMchtFilterStore()
const tabs = [
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈정보' },
]
const route = useRoute()
const id = Number(route.params.id) || 0

getAllMerchandises()
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <PayModuleCard style="margin-top: 1em;" :item="item" :able_mcht_chanage="false" :merchandises="merchandises"/>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
