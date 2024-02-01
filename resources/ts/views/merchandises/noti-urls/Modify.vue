
<script setup lang="ts">
import NotiCard from '@/views/merchandises/noti-urls/NotiCard.vue'
import CreateForm from '@/layouts/utils/CreateForm.vue'
import { defaultItemInfo } from '@/views/merchandises/noti-urls/useStore'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import type { Tab } from '@/views/types'

const {path, item } = defaultItemInfo()
const { mchts } = useSalesFilterStore()
const tabs = <Tab[]>([
    { icon: 'emojione:envelope', title: '노티정보' },
])
const route = useRoute()
const id = Number(route.params.id) || 0
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <NotiCard :item="item" :able_mcht_chanage="false" :merchandises="mchts"/>
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
