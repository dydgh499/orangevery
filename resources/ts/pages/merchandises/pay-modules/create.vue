
<script setup lang="ts">
import PayModuleCard from '@/views/merchandises/pay-modules/PayModuleCard.vue'
import CreateForm from '@/layouts/utils/CreateForm.vue'
import { defaultItemInfo } from '@/views/merchandises/pay-modules/useStore'
import { getAllMerchandises } from '@/views/merchandises/useStore'
import type { Merchandise } from '@/views/types'

const { path, item } = defaultItemInfo()
const merchandises = reactive<Merchandise[]>([])
const tabs = [
    { icon: 'ic-outline-send-to-mobile', title: '결제모듈정보' },
]

const id = ref<number>(0)
watchEffect(async() => {
    Object.assign(merchandises, await getAllMerchandises())
})
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <PayModuleCard :item="item" :able_mcht_chanage="true" :merchandises="merchandises"/>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
