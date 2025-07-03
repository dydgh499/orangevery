<script lang="ts" setup>
import { axios } from '@/plugins/axios';
import { useRequestStore } from '@/views/request';
import PayModuleCard from '@/views/services/options/PayModuleCard.vue';
import { PayModule } from '@/views/types';
import { getDeliveryModeInfo } from './useStore';

const { setNullRemove } = useRequestStore()
const pay_modules = ref(<PayModule[]>([]))

const getAllItem = async () => {
    const res = await axios.get(`/api/v1/manager/pays/pay-modules`, {
        params: {
            page_size : 10,
            page : 1,
        }
    })
    pay_modules.value = res.data
}

const addNewItem = () => {
    pay_modules.value.push({
        id: 0,
        api_key: '',
        sub_key: '',
        module_type: 4,
        note: '빌키결제',
        mid: '',
        tid: '',
        pg_id: getDeliveryModeInfo().pg_id,
        ps_id: null,
        is_old_auth: 0
    })
}
onMounted(async () => {
    await getAllItem()
})
watchEffect(() => {
    setNullRemove(pay_modules.value)
})
</script>
<template>
    <VCard>
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewItem">
                결제모듈 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
    <VRow style="margin-top: 1em;">
        <PayModuleCard v-for="pay_module in pay_modules" :key="pay_module.id" :item="pay_module"/>
    </VRow>
</template>
