
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import NotiCard from '@/views/merchandises/noti-urls/NotiCard.vue'
import { defaultItemInfo } from '@/views/merchandises/noti-urls/useStore'
import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import type { PayModule, Tab } from '@/views/types'

const {path, item } = defaultItemInfo()
const pay_modules = reactive<PayModule[]>([])
const tabs = <Tab[]>([
    { icon: 'emojione:envelope', title: '노티정보' },
])
const route = useRoute()
const id = Number(route.params.id) || 0
Object.assign(pay_modules, await getAllPayModules())
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <NotiCard :item="item" :able_mcht_chanage="true" :pay_modules="pay_modules" :key="pay_modules.length"/>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
