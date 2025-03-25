<script setup lang="ts">
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { settlementHistoryFunctionCollect } from '@/views/transactions/settle-histories/SettleHistory'
import { SettlesHistory } from '@/views/types'
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

const { deposit, download, addDeduct, linkAccount } = settlementHistoryFunctionCollect(store)

const getDepositParams = async () => {
    const params:any = {
        brand_id: corp.id,
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
