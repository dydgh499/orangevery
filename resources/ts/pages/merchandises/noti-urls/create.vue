
<script setup lang="ts">
import NotiCard from '@/views/merchandises/noti-urls/NotiCard.vue'
import CreateForm from '@/layouts/utils/CreateForm.vue'

import { defaultItemInfo } from '@/views/merchandises/noti-urls/useStore'
import { useMchtFilterStore } from '@/views/merchandises/useStore'
import type { Tab } from '@/views/types'

const {path, item } = defaultItemInfo()
const { merchandises, getAllMerchandises } = useMchtFilterStore()
const tabs = <Tab[]>([
    { icon: 'streamline:interface-time-alarm-notification-alert-bell-wake-clock-alarm', title: '노티정보' },
])
const id = ref<number>(0)
getAllMerchandises()
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <NotiCard :item="item" :able_mcht_chanage="true" :merchandises="merchandises"/>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
