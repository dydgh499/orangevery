<script lang="ts" setup>
import type { FinanceVan } from '@/views/types'
import FinanceVanCard from '@/views/services/pay-gateways/FinanceVanCard.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useRequestStore } from '@/views/request'

const { finance_vans } = useStore()
const { setNullRemove } = useRequestStore()

const addNewFinanceVan = () => {
    finance_vans.push(<FinanceVan>({
        id: 0,
        finance_company_num: null,
        fin_type: null,
        dev_fee: 10,
        bank_code: '000',
        api_key: '',
        sub_key: '',
        enc_key: '',
        iv: '',
        min_balance_limit: 0,
        corp_code: '',
        corp_name: '',
        nick_name: '금융 VAN 명칭을 적어주세요.😀',
        withdraw_acct_num: '',
    }))
}
watchEffect(() => {
    setNullRemove(finance_vans)
})
</script>
<template>
    <FinanceVanCard v-for="finance_van in finance_vans" :key="finance_van.id" style="margin-top: 1em;" :item="finance_van"/>
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
