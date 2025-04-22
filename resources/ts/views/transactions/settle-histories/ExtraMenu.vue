<script setup lang="ts">
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { settlementHistoryFunctionCollect } from '@/views/transactions/settle-histories/SettleHistory'
import { SettlesHistory, VirtualAccountWithdraw } from '@/views/types'
import { getUserLevel } from '@axios'
import corp from '@corp'

interface Props {
    name: string,
    item: SettlesHistory,
    is_mcht: boolean
}

const props = defineProps<Props>()
const store = <any>(inject('store'))
const financeDialog = <any>(inject('financeDialog'))
const addDeductDialog = <any>(inject('addDeductDialog'))
const withdrawStatusmentDialog = <any>(inject('withdrawStatusmentDialog'))
const withdrawHistoriesDialog = <any>(inject('withdrawHistoriesDialog'))

const { deposit, download, addDeduct, linkAccount } = settlementHistoryFunctionCollect(store)
const { finance_vans } = useStore()

const getDepositParams = async () => {
    const params:any = {
        use_finance_van_deposit: Number(corp.pv_options.paid.use_finance_van_deposit),
    }
    if(params['use_finance_van_deposit']) {
        params['fin_id'] = await financeDialog.value.show()
        // 선택안함
        if(params['fin_id'] == 0)
            return 0        
        else if(params['fin_id'] == -1)
            params.use_finance_van_deposit = 0
    }
    deposit(props.item, props.is_mcht, params)
}

const getSuccessResultId = () => {
    const realtime = props.item.deposits?.find(obj => obj.result_code === '0000' && obj.request_type === 6170)
    return realtime ? realtime.id : 0
}

const getWithdrawStatement = () => {
    const realtime = props.item.deposits?.find(obj => obj.result_code === '0000' && obj.request_type === 6170)
    
    if(realtime) {
        const finance = finance_vans.find(obj => obj.id == realtime.fin_id)
        return {
            user_name: props.name,
            acct_num: props.item.acct_num,
            acct_bank_name: props.item.acct_bank_name,
            trans_seq_num: realtime.trans_seq_num,
            trans_amount: props.item.deposit_amount,
            withdraw_bank_code: finance?.bank_code,
            withdraw_corp_name: finance?.corp_name,
            withdraw_acct_num: finance?.withdraw_acct_num,
            created_at: realtime.created_at,
        }
    }
    else
        return {}
}

const getWithdrawHistories = () => {
    const histories = []
    if(props.item.deposits)
    {
        for(var i=0; i<props.item.deposits.length; i++)
        {            
            histories.push(<VirtualAccountWithdraw>{
                id: props.item.deposits[i].id,
                trans_amount: props.item.deposit_amount,
                result_code: props.item.deposits[i].result_code,
                request_type: props.item.deposits[i].request_type,
                note: props.item.deposits[i].message,
                trans_seq_num: props.item.deposits[i].trans_seq_num,
                acct_num: props.item.acct_num,
                acct_name: props.item.acct_name,
                acct_bank_name: props.item.acct_bank_name,
                acct_bank_code: props.item.acct_bank_code,
                created_at: props.item.deposits[i].created_at,
            })
        }
    }
    return histories
}

</script>
<template>
    <VBtn icon size="x-small" color="default" variant="text">
        <VIcon size="22" icon="tabler-dots-vertical" />
        <VMenu activator="parent" width="250">
            <VList>
                <VListItem value="deposit" @click="getDepositParams()" v-if="getUserLevel() >= 35">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:report-money" />
                    </template>
                    <VListItemTitle>{{ props.item.deposit_status ? '입금취소처리' : '입금처리' }}</VListItemTitle>
                </VListItem>
                <VListItem value="deduct" @click="addDeduct(addDeductDialog, props.item, props.is_mcht)"  v-if="getUserLevel() >= 35">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="ic:twotone-plus-minus-alt" />
                    </template>
                    <VListItemTitle>추가차감</VListItemTitle>
                </VListItem>
                <VListItem value="account-linking" @click="linkAccount(props.item, props.is_mcht)"  v-if="getUserLevel() >= 35">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="ri-bank-card-fill" />
                    </template>
                    <VListItemTitle>계좌정보 동기화</VListItemTitle>
                </VListItem>
                <VListItem 
                    value="withdraw-status"
                    @click="withdrawStatusmentDialog.settle(getWithdrawStatement())"
                    v-if="getSuccessResultId() !== 0">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:receipt-2" />
                    </template>
                    <VListItemTitle>이체내역서</VListItemTitle>
                </VListItem>
                <VListItem 
                    value="withdraw-histories" 
                    @click="withdrawHistoriesDialog.show(getWithdrawHistories())"
                    v-if="props.item.deposits && props.item.deposits?.length"
                    >
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="tabler:history" />
                    </template>
                    <VListItemTitle>출금시도이력</VListItemTitle>
                </VListItem>
                <VListItem value="download" @click="download(props.item, props.is_mcht)" style="width: fit-content;">
                    <template #prepend>
                        <VIcon size="24" class="me-3" icon="vscode-icons:file-type-excel" />
                    </template>
                    <VListItemTitle style="width: fit-content;">
                        <BaseQuestionTooltip :location="'bottom'" :text="'정산매출 다운로드'" :content="'본정산건에 사용되었던 매출건들이 다운로드 됩니다.<br>(결제모듈 정산정보는 추출되지 않습니다.)'"/>
                    </VListItemTitle>
                </VListItem>
            </VList>
        </VMenu>
    </VBtn>
</template>
