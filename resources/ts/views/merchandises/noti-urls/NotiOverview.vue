<script lang="ts" setup>
import type { NotiUrl, Merchandise } from '@/views/types'
import NotiCard from '@/views/merchandises/noti-urls/NotiCard.vue'

import { getAllMerchandises } from '@/views/merchandises/useStore'
import { getAllNotiUrls } from '@/views/merchandises/noti-urls/useStore'

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>();
const new_noti_urls = reactive<NotiUrl[]>([])
const noti_urls = reactive<NotiUrl[]>([])
const merchandises = reactive<Merchandise[]>([])

const addNewNotiUrl = () => {
    new_noti_urls.push(<NotiUrl>{
        id: 0,
        send_url: '',
        noti_status: true,
        mcht_id: props.item.id,
        note: '비고',
    })
}
watchEffect(async() => {
    if(props.item.id)
        Object.assign(noti_urls, await getAllNotiUrls(props.item.id))
    else
        Object.assign(merchandises, await getAllMerchandises())
})
</script>
<template>
    <NotiCard v-for="(item, index) in noti_urls" :key="index" style="margin-top: 1em;" :item="item" :able_mcht_chanage="false" :merchandises="merchandises"/>
    <NotiCard v-for="(item, index) in new_noti_urls" :key="index" style="margin-top: 1em;" :item="item" :able_mcht_chanage="false" :merchandises="merchandises"/>
    <!-- 👉 submit -->
    <VCard style="margin-top: 1em;">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewNotiUrl">
                노티 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
</template>
