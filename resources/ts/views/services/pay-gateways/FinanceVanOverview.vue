<script lang="ts" setup>
import { useRequestStore } from '@/views/request'
import FinanceVanCard from '@/views/services/pay-gateways/FinanceVanCard.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { FinanceVan } from '@/views/types'

const { finance_vans } = useStore()
const { setNullRemove } = useRequestStore()

const addNewFinanceVan = () => {
    finance_vans.push(<FinanceVan>({
        id: 0,
        finance_company_num: null,
        is_agency_van: 0,
        dev_fee: 0,
        bank_code: '000',
        api_key: '',
        sub_key: '',
        enc_key: '',
        iv: '',
        min_balance_limit: 0,
        corp_code: '',
        corp_name: '',
        nick_name: '별칭입력',
        withdraw_acct_num: '',
        use_kakao_auth: 0,
        use_account_auth: 0,
    }))
}
watchEffect(() => {
    setNullRemove(finance_vans)
})
</script>
<template>
    <VCard>
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewFinanceVan">
                금융VAN 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VCard>
    <VRow style="margin-top: 1em;">
        <FinanceVanCard v-for="finance_van in finance_vans" :key="finance_van.id" :item="finance_van"/>
    </VRow>
</template>
