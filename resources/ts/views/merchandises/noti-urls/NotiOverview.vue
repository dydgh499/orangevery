<script lang="ts" setup>
import type { NotiUrl, Merchandise } from '@/views/types'
import { getAllNotiUrls } from '@/views/merchandises/noti-urls/useStore'
import NotiCard from '@/views/merchandises/noti-urls/NotiCard.vue'
import { useRequestStore } from '@/views/request'

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>()
const { setNullRemove } = useRequestStore()
const noti_urls = reactive<NotiUrl[]>([])

const addNewNotiUrl = () => {
    noti_urls.push(<NotiUrl>{
        id: 0,
        send_url: '',
        noti_status: 1,
        mcht_id: props.item.id,
        note: '비고',
    })
}
if(props.item.id)
    Object.assign(noti_urls, await getAllNotiUrls(props.item.id))

watchEffect(() => {
    setNullRemove(noti_urls)
})
</script>
<template>
    <VCard style="margin-top: 1em;">
        <VCol class="d-flex gap-4" v-if="getUserLevel() >= 35">
            <VBtn type="button" style="margin-left: auto;" @click="addNewNotiUrl">
                노티 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
    <VRow>
        <NotiCard v-for="(item, index) in noti_urls" :key="index" style="margin-top: 1em;" :item="item" :able_mcht_chanage="false" :pay_modules="pay_modules"/>
    </VRow>
</template>
