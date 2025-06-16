<script setup lang="ts">
import { inputFormater } from '@/@core/utils/formatters'
import PasswordAuthDialog from '@/layouts/dialogs/users/PasswordAuthDialog.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { user_info } from '@/plugins/axios'
import { useRequestStore } from '@/views/request'
import { useHeadOfficeAccountStore } from '@/views/services/cms-transactions/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { FinanceVan, HeadOffceAccount } from '@/views/types'
import { requiredValidatorV2 } from '@validators'

const { head_office_accounts } = useHeadOfficeAccountStore()
const { post } = useRequestStore()
const { finance_vans, updateFinanceVan } = useStore()

const passwordAuthDialog = ref()
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))

const visible = ref(false)
const token = ref('')
const fin_id = ref(null)
const note = ref('')
const head_office_acct_id = ref(null)
const {
    amount_format,
    amount,
    formatAmount,
} = inputFormater()

const show = () => {
    token.value = ''
    visible.value = true
}

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
        const phone_num = user_info.value.phone_num.replaceAll(' ', '').replaceAll('-', '')
        token.value = await passwordAuthDialog.value.show(phone_num)

        if(token.value !== '') {
            if(await alert.value.show('정말 '+amount.value+'원을 이체하시겠습니까?')) {
                const params = {
                    fin_id: fin_id.value,
                    head_office_acct_id: head_office_acct_id.value,
                    withdraw_amount: amount.value,
                    note: note.value,
                    token: token.value
                }
                const r = await post('/api/v1/manager/services/cms-transactions/withdraw', params, true)
                if(r.status_code === 201) {
                    updateFinanceVan(fin_id.value)
                }
            }
        }
    }
    else
        snackbar.value.show('출금 금액을 입력해주세요.', 'warning')
}

defineExpose({
    show
});
</script>
<template>
    <section>
        <VDialog v-model="visible" persistent max-width="600">
            <!-- Dialog Content -->
            <DialogCloseBtn @click="visible = false" />
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
                                    variant="underlined"
                                    label="출금 이체모듈 선택" item-title="nick_name" item-value="id" 
                                    persistent-hint single-line  :hint="withdrawAcctHint()"/>
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>
                                <BaseQuestionTooltip :location="'top'" :text="'입금계좌 선택'" :content="'가상계좌로부터 입금받을 계좌입니다.<br>입금계좌 등록 및 수정작업은 개발사에 문의해주세요.'">
                                </BaseQuestionTooltip>
                            </template>
                            <template #input>
                                <VTextField 
                                    v-model="head_office_acct_id" 
                                    @input="formatAmount"
                                    variant="underlined"
                                    placeholder="입금 계좌를 입력해주세요"
                                    :rules="[requiredValidatorV2(amount, '입금계좌')]" 
                                />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>출금금액 입력</template>
                            <template #input>
                                <VTextField 
                                    v-model="amount_format" suffix="원" 
                                    variant="underlined"
                                    placeholder="출금금액을 입력해주세요"
                                    :rules="[requiredValidatorV2(amount, '출금금액')]" 
                                />
                            </template>
                        </CreateHalfVCol>
                        <CreateHalfVCol :mdl="6" :mdr="6">
                            <template #name>출금사유</template>
                            <template #input>
                                <VTextField v-model="note" label="출금사유"
                                    variant="underlined"
                                    prepend-inner-icon="twemoji-spiral-notepad" maxlength="50" auto-grow :rules="[requiredValidatorV2(note, '출금사유')]"/>
                            </template>
                        </CreateHalfVCol>
                    </VRow>
                    <VRow>
                        <VCol>
                            <div style="display: flex;" v-if="fin_id">
                                <b style="min-width: 6.5em;">차감후 잔액</b>
                                <b class="text-primary">{{ (finance_vans.find(obj => obj.id == fin_id)?.balance - amount).toLocaleString() }}원</b>
                            </div>
                        </VCol>
                        <VCol class="d-flex gap-4">
                            <VBtn type="button" style="margin-left: auto;" @click="deposit()">
                                지정계좌로 이체
                                <VIcon end icon="fa6-solid:money-bill-transfer" />
                            </VBtn>
                        </VCol>
                    </VRow>
                    <br>
                </VCardItem>
            </VCard>
        </VDialog>
        <PasswordAuthDialog ref="passwordAuthDialog"/>
    </section>
</template>
