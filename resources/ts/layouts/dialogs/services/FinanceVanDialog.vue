<script lang="ts" setup>
import CreateHalfVCol from '@/layouts/utils/CreateHalfVCol.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import type { FinanceVan } from '@/views/types'

const fin_id  = ref(null)
const visible = ref(false)
const { finance_vans } = useStore()
const snackbar = <any>(inject('snackbar'))

let resolveCallback: (idx: number) => void;

const show = (): Promise<number> => {
    visible.value = true

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
                    <VBtn @click="selected()">
                        <span style="font-weight: bold;">이체하기</span>
                    </VBtn>
                    <VBtn @click="visible = false; resolveCallback(-1)" color="warning">
                        <span style="font-weight: bold;">입금상태만 변경</span>
                    </VBtn>
                </VRow>
            </VCardText>
        </VCard>
    </VDialog>
</template>
