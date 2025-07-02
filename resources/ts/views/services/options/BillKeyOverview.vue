<script lang="ts" setup>
import { axios, user_info } from '@/plugins/axios';
import { useRequestStore } from '@/views/request';
import BillKeyCard from '@/views/services/options/BillKeyCard.vue';
import { BillKeyCreate } from '@/views/types';

const { setNullRemove } = useRequestStore()
const bill_keys = ref(<BillKeyCreate[]>([]))

const getAllItem = async () => {
    const res = await axios.get(`/api/v1/manager/pays/bill-keys`, {
        params: {
            page_size : 10,
            page : 1,
        }
    })
    bill_keys.value = res.data
}

const addNewItem = () => {
    bill_keys.value.push({
        id: 0,
        buyer_name: user_info.value.nick_name,
        buyer_phone: user_info.value.phone_num,
        yymm: '',
        auth_num: '',
        card_pw: '',
        pmod_id: null,
        issuer: '',
        nick_name: '',
        card_num: ''
    })
}
onMounted(async () => {
    await getAllItem()
})
watchEffect(() => {
    setNullRemove(bill_keys.value)
})
</script>
<template>
    <VCard>
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewItem">
                빌키 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
    <VRow style="margin-top: 1em;">
        <BillKeyCard v-for="bill_key in bill_keys" :key="bill_key.id" :item="bill_key"/>
    </VRow>
</template>
