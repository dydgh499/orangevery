<script lang="ts" setup>
import { axios } from '@/plugins/axios';
import { useRequestStore } from '@/views/request';
import PayGatewayCard from '@/views/services/options/PayGatewayCard.vue';
import { PayGateway } from '@/views/types';
import { useStore } from './useStore';

const { setNullRemove } = useRequestStore()
const items = ref(<PayGateway[]>([]))

const getAllItem = async () => {
    const res = await axios.get(`/api/v1/manager/services/pay-gateways`, {
        params: {
            page_size : 10,
            page : 1,
        }
    })
    items.value = res.data
}

const addNewItem = () => {
    items.value.push({
        id: 0,
        pg_type: null,
        pg_name: '',
        rep_name: '',
        company_name: '',
        business_num: '',
        phone_num: '',
        addr: ''
    })
}
onMounted(async () => {
    await getAllItem()
})
watchEffect(() => {
    setNullRemove(items.value)
})
</script>
<template>
    <VCard>
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewItem">
                결제대행사 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
    <PayGatewayCard v-for="item in items" :key="item.id" :item="item" style="margin-top: 1em;"/>
</template>
