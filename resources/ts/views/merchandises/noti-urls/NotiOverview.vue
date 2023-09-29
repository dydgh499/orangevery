<script lang="ts" setup>
import type { NotiUrl, Merchandise } from '@/views/types'
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import { getAllNotiUrls } from '@/views/merchandises/noti-urls/useStore'
import NotiCard from '@/views/merchandises/noti-urls/NotiCard.vue'

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>();
const new_noti_urls = reactive<NotiUrl[]>([])
const noti_urls = reactive<NotiUrl[]>([])

const { mchts } = useSalesFilterStore()
const addNewNotiUrl = () => {
    new_noti_urls.push(<NotiUrl>{
        id: 0,
        send_url: '',
        noti_status: true,
        mcht_id: props.item.id,
        note: '비고',
    })
}
if(props.item.id)
    Object.assign(noti_urls, await getAllNotiUrls(props.item.id))

</script>
<template>
    <NotiCard v-for="(item, index) in noti_urls" :key="index" style="margin-top: 1em;" :item="item" :able_mcht_chanage="false" :merchandises="mchts"/>
    <NotiCard v-for="(item, index) in new_noti_urls" :key="index" style="margin-top: 1em;" :item="item" :able_mcht_chanage="false" :merchandises="mchts"/>
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
