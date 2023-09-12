<script lang="ts" setup>
import type { FinanceVan } from '@/views/types'
import FinanceVanCard from '@/views/services/pay-gateways/FinanceVanCard.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'

const { finance_vans } = useStore()
const new_finance_vans = reactive<FinanceVan[]>([])
const addNewFinanceVan = () => {
    new_finance_vans.push({
        id: 0,
        finance_company_num: null,
        fin_type: null,
        dev_fee: 0.1,
        api_key: '',
        sms_key: '',
        sms_id: '',
        sms_sender_phone: '',
        sms_receive_phone: '',
        min_balance_limit: 0,
        corp_code: '',
        corp_name: '',
        nick_name: '금융 VAN 명칭을 적어주세요.😀',
        withdraw_acct_num: '',
    })
}

</script>
<template>
    <FinanceVanCard v-for="finance_van in finance_vans" :key="finance_van.id" style="margin-top: 1em;" :item="finance_van"/>
    <FinanceVanCard v-for="(new_finance_van, index) in new_finance_vans" :key="index" style="margin-top: 1em;" :item="new_finance_van"/>
    <!-- 👉 submit -->
    <VCard style="margin-top: 1em;">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewFinanceVan">
                금융VAN 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
</template>
