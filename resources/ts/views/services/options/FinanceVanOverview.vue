<script lang="ts" setup>
import { axios } from '@/plugins/axios';
import { useRequestStore } from '@/views/request';
import FinanceVanCard from '@/views/services/options/FinanceVanCard.vue';
import type { FinanceVan } from '@/views/types';

const { setNullRemove } = useRequestStore()
const _finance_vans = ref(<FinanceVan[]>([]))

const getAllFinanceVan = async () => {
    const res = await axios.get(`/api/v1/manager/services/finance-vans`, {
        params: {
            page_size : 10,
            page : 1,
        }
    })
    _finance_vans.value = res.data
}

const addNewFinanceVan = () => {
    _finance_vans.value.push(<FinanceVan>({
        id: 0,
        finance_company_num: null,
        bank_code: '000',
        api_key: '',
        sub_key: '',
        enc_key: '',
        iv: '',
        corp_code: '',
        corp_name: '',
        nick_name: '별칭입력',
        withdraw_acct_num: '',
    }))
}
onMounted(async () => {
    await getAllFinanceVan()
})
watchEffect(() => {
    setNullRemove(_finance_vans.value)
})
</script>
<template>
    <VCard>
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewFinanceVan">
                이체모듈 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
    <VRow style="margin-top: 1em;">
        <FinanceVanCard v-for="finance_van in _finance_vans" :key="finance_van.id" :item="finance_van"/>
    </VRow>
</template>
