
<script setup lang="ts">
import CreateForm from '@/layouts/utils/CreateForm.vue'
import PopupOverview from '@/views/popups/PopupOverview.vue'
import { defaultItemInfo } from '@/views/popups/useStore'
import type { Tab } from '@/views/types'

const { path, item } = defaultItemInfo()
const tabs = <Tab[]>([
    { icon: 'tabler-user-check', title: '팝업정보' },
])
const id = ref<number>(0)
</script>
<template>
    <section>
        <CreateForm :id="id" :path="path" :tabs="tabs" :item="item">
            <template #view>
                <VWindowItem>
                    <PopupOverview :item="item" :id="id" />
                </VWindowItem>
            </template>
        </CreateForm>
    </section>
</template>
