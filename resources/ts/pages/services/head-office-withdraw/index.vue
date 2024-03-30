
<script setup lang="ts">
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import HeadOfficeAccountCard from '@/views/services/head-office-withdraw/HeadOfficeAccountCard.vue'
import { useHeadOfficeAccountStore } from '@/views/services/head-office-withdraw/useStore'
import { useRequestStore } from '@/views/request'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { HeadOffceAccount, FinanceVan } from '@/views/types'
import { requiredValidatorV2 } from '@validators'

const { head_office_accounts } = useHeadOfficeAccountStore()
const { post } = useRequestStore()
const { finance_vans } = useStore()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))

const fin_id = ref(null)
const head_office_acct_id = ref(null)
const amount = ref(0)

const withdrawAcctBalance = () => {
    const finance_van = <FinanceVan>(finance_vans.find(obj => obj.id == fin_id.value))
    if(finance_van)
        return `출금 가능잔액: ${finance_van.balance?.toLocaleString()}원`
    else
        return ``
}

const withdrawAcctHint = () => {
    const finance_van = <FinanceVan>(finance_vans.find(obj => obj.id == fin_id.value))
    if(finance_van)
        return `은행코드: ${finance_van.bank_code}, 계좌번호: ${finance_van.withdraw_acct_num}`
    else
        return ``
}

const depositAcctHint = () => {
    const head_office_account = <HeadOffceAccount>(head_office_accounts.find(obj => obj.id == head_office_acct_id.value))
    if(head_office_account)
        return `예금주: ${head_office_account.acct_name}, 은행명: ${head_office_account.acct_bank_name}`
    else
        return ``
}

const deposit = async () => {
    if(amount.value) {
        if(await alert.value.show('정말 '+amount.value+'원을 이체하시겠습니까?')) {
            const params = {
                fin_id: fin_id.value,
                head_office_acct_id: head_office_acct_id.value,
                withdraw_amount: amount.value
            }
            const r = await post('/api/v1/manager/transactions/realtime-histories/head-office-transfer', params)
        }
    }
    else
        snackbar.value.show('출금 금액을 입력해주세요.', 'warning')
}
</script>
<template>
    <section>
        <VRow class="match-height">
            <!-- 👉 운영정보 -->
            <VCol cols="12" md="4">
                <VCard>
                    <VCardItem>
                        <VCardTitle style="margin-bottom: 1em;">본사 지정계좌 이체</VCardTitle>
                        <VDivider style="margin: 1em 0;" />
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>출금 이체모듈 선택<br>
                                    <h4>
                                        {{ withdrawAcctBalance() }}
                                    </h4>
                                </template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="fin_id" :items="finance_vans"
                                        label="출금 이체모듈 선택" item-title="nick_name" item-value="id" 
                                        persistent-hint single-line  :hint="withdrawAcctHint()"/>
                                </template>
                            </CreateHalfVCol>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>지정계좌 선택</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="head_office_acct_id"
                                        :items="head_office_accounts" label="입금 계좌 선택" item-title="acct_num" item-value="id"
                                        persistent-hint single-line  :hint="depositAcctHint()" />
                                </template>
                            </CreateHalfVCol>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>출금금액 입력</template>
                                <template #input>
                                    <VTextField v-model="amount" type="number" suffix="￦" placeholder="출금금액 입력"
                                        prepend-inner-icon="ic:outline-price-change" :rules="[requiredValidatorV2(amount, '출금금액')]" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                        <VCol class="d-flex gap-4">
                            <VBtn type="button" style="margin-left: auto;" @click="deposit()">
                                지정계좌로 이체
                                <VIcon end icon="fa6-solid:money-bill-transfer" />
                            </VBtn>
                        </VCol>
                    </VRow>
                    </VCardItem>
                </VCard>
            </VCol>
            <VCol cols="12" md="8">
                <VCard>
                    <VCardItem>
                        <VCol cols="12">
                            <VRow>
                                <HeadOfficeAccountCard />
                            </VRow>
                        </VCol>
                    </VCardItem>
                </VCard>
            </VCol>
        </VRow>
    </section>
</template>
