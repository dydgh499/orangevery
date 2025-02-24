<script lang="ts" setup>
import NotiCard from '@/views/merchandises/noti-urls/NotiCard.vue'
import { getAllNotiUrls, notiViewable } from '@/views/merchandises/noti-urls/useStore'
import { getAllPayModules } from '@/views/merchandises/pay-modules/useStore'
import { useRequestStore } from '@/views/request'
import type { Merchandise, NotiUrl, PayModule } from '@/views/types'

interface Props {
    item: Merchandise,
}
const props = defineProps<Props>()
const { setNullRemove } = useRequestStore()
const noti_urls = reactive<NotiUrl[]>([])
const pay_modules = reactive<PayModule[]>([])

const addNewNotiUrl = () => {
    noti_urls.push(<NotiUrl>{
        id: 0,
        send_url: '',
        noti_status: 1,
        mcht_id: props.item.id,
        pmod_id: -1,
        note: '비고',
    })
}
if(props.item.id) {
    Object.assign(noti_urls, await getAllNotiUrls(props.item.id))
    Object.assign(pay_modules, await getAllPayModules(props.item.id))
}

watchEffect(() => {
    setNullRemove(noti_urls)
})
</script>
<template>
    <VCard style="margin-top: 1em;" v-if="notiViewable(0)">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewNotiUrl">
                노티 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
    <VCard style="margin-top: 1em;" v-if="noti_urls.length === 0">
        <VCol class="d-flex gap-4">
            등록된 노티정보가 없습니다.
        </VCol>
    </VCard>

    <VRow style="margin-top: 1em;">
        <NotiCard v-for="(item, index) in noti_urls" :key="index" :item="item" :able_mcht_chanage="false" :pay_modules="pay_modules"/>
    </VRow>
</template>
