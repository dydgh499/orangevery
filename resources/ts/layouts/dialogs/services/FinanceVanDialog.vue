<script lang="ts" setup>
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { FinanceVan } from '@/views/types'

const fin_id  = ref(null)
const visible = ref(false)
const settle_amount = ref(0)
const { finance_vans } = useStore()
const snackbar = <any>(inject('snackbar'))

let resolveCallback: (idx: number) => void;

const show = (_settle_amount: number): Promise<number> => {
    visible.value = true
    settle_amount.value = _settle_amount

    return new Promise<number>((resolve) => {
        resolveCallback = resolve;
    });
}

const selected = () => {
    visible.value = false
    if(fin_id.value)
        resolveCallback(fin_id.value)
    else
        snackbar.value.show('금융 VAN을 선택해주세요.', 'warning')
}

const getFinanceVan = () => {
    return <FinanceVan>(finance_vans.find(obj => obj.id == fin_id.value))
}

const withdrawAcctBalance = () => {
    const finance_van = getFinanceVan()
    if(finance_van)
        return `${finance_van.balance?.toLocaleString()}`
    else
        return ``
}

const withdrawAcctHint = () => {
    const finance_van = getFinanceVan()
    if(finance_van)
        return `은행코드: ${finance_van.bank_code}, 계좌번호: ${finance_van.withdraw_acct_num}`
    else
        return ``
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent class="v-dialog-lg">
        <!-- Dialog Content -->
        <DialogCloseBtn @click="visible = false; resolveCallback(0)" />
        <VCard title="이체에 사용될 금융 VAN을 선택해주세요.">
            <VCardText>
                <VRow class="pt-3">
                    <CreateHalfVCol :mdl="6" :mdr="6">
                        <template #name>출금 이체모듈 선택<br>
                            <div style="display: flex;">
                                <table>
                                    <tr v-if="fin_id">
                                        <th style="min-width: 10em;text-align: start;">출금 가능잔액</th>
                                        <td><h4>{{ withdrawAcctBalance() }} &#8361;</h4></td>
                                    </tr>
                                    <tr>
                                        <th style="min-width: 10em;text-align: start;">정산할 금액(-)</th>
                                        <td><span>{{ settle_amount.toLocaleString() }} &#8361;</span></td>
                                    </tr>
                                </table>
                            </div>
                        </template>
                        <template #input>
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="fin_id" :items="finance_vans"
                                label="출금 이체모듈 선택" item-title="nick_name" item-value="id" 
                                persistent-hint single-line  :hint="withdrawAcctHint()"/>
                        </template>
                    </CreateHalfVCol>
                    <CreateHalfVCol :mdl="6" :mdr="6">
                        <template #name>
                            <div style="display: flex;" v-if="fin_id">
                                <table style="border-top: 1px dashed grey;">
                                    <tr>
                                        <th style="min-width: 10em;text-align: start;" class="text-primary">정산금 차감액</th>
                                        <td><h4 class="text-primary">{{ (getFinanceVan().balance - settle_amount).toLocaleString() }} &#8361;</h4></td>
                                    </tr>
                                </table>
                            </div>
                        </template>
                        <template #input>
                        </template>
                    </CreateHalfVCol>
                    <VCardText class="d-flex justify-end gap-3 flex-wrap">
                        <VBtn @click="selected()">
                            <span style="font-weight: bold;">이체하기</span>
                        </VBtn>
                        <VBtn @click="visible = false; resolveCallback(-1)" color="warning">
                            <span style="font-weight: bold;">입금상태만 변경</span>
                        </VBtn>
                    </VCardText>
                </VRow>
            </VCardText>
        </VCard>
    </VDialog>
</template>
