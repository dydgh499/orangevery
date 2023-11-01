
<script setup lang="ts">
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import HeadOfficeAccountCard from '@/views/services/head-office-withdraw/HeadOfficeAccountCard.vue'
import { useHeadOfficeAccountStore } from '@/views/services/head-office-withdraw/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { HeadOffceAccount, FinanceVan } from '@/views/types'
import { requiredValidator } from '@validators'

const { head_office_accounts } = useHeadOfficeAccountStore()
const { finance_vans } = useStore()

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))

const withdraw_acct = ref()
const deposit_acct = ref()
const amount = ref(0)

const withdrawAcctHint = () => {
    const finance_van = <FinanceVan>(finance_vans.find(obj => obj.id == withdraw_acct.value))
    if(finance_van)
        return `계좌번호: ${finance_van.withdraw_acct_num}, 은행코드: ${finance_van.bank_code}`
    else
        return ``
}
const depositAcctHint = () => {
    const head_office_account = <HeadOffceAccount>(head_office_accounts.value.find(obj => obj.id == deposit_acct.value))
    if(head_office_account)
        return `예금주: ${head_office_account.acct_name}, 은행명: ${head_office_account.acct_bank_name}`
    else
        return ``
}
const deposit = async () => {
    if(amount.value) {
        if(await alert.value.show('정말 '+amount.value+'원을 이체하시겠습니까?')) {

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
                        <VCardTitle style="margin-bottom: 1em;">본사 지정계좌 출금</VCardTitle>
                        <VDivider style="margin: 1em 0;" />
                        <VRow class="pt-3">
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>출금 이체 모듈 선택</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="withdraw_acct" :items="finance_vans"
                                        label="출금 이체모듈 선택" item-title="nick_name" item-value="id" 
                                        persistent-hint single-line  :hint="withdrawAcctHint()"/>
                                </template>
                            </CreateHalfVCol>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>지정계좌 선택</template>
                                <template #input>
                                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="deposit_acct"
                                        :items="head_office_accounts" label="입금 계좌 선택" item-title="acct_num" item-value="id"
                                        persistent-hint single-line  :hint="depositAcctHint()" />
                                </template>
                            </CreateHalfVCol>
                            <CreateHalfVCol :mdl="6" :mdr="6">
                                <template #name>출금 금액 입력</template>
                                <template #input>
                                    <VTextField v-model="amount" type="number" suffix="￦" placeholder="출금금액 입력"
                                        prepend-inner-icon="ic:outline-price-change" :rules="[requiredValidator]" />
                                </template>
                            </CreateHalfVCol>
                        </VRow>
                        <VRow>
                        <VCol class="d-flex gap-4">
                            <VBtn type="button" style="margin-left: auto;" @click="deposit()">
                                입금계좌로 이체
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
