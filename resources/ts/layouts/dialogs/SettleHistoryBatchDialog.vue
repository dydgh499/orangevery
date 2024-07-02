

<script lang="ts" setup>
import FinanceVanDialog from '@/layouts/dialogs/services/FinanceVanDialog.vue'
import { settlementHistoryFunctionCollect } from '@/views/transactions/settle-histories/SettleHistory'
import corp from '@corp'

interface Props {
    selected_idxs: number[],
    store: any
    is_mcht: boolean,
}

const props = defineProps<Props>()

const financeDialog = ref()
const visible = ref(false)

const { batchDeposit, batchCancel, batchLinkAccount } = settlementHistoryFunctionCollect(props.store)

const getBatchDepositParams = async () => {
    if (props.selected_idxs) {
        const params: any = {
            brand_id: corp.id,
            use_finance_van_deposit: Number(corp.pv_options.paid.use_finance_van_deposit),
        }
        if (params['use_finance_van_deposit']) {
            params['fin_id'] = await financeDialog.value.show()
            // 선택안함
            if (params['fin_id'] == 0)
                return 0
            else if(params['fin_id'] == -1)
                params.use_finance_van_deposit = 0
        }
        batchDeposit(props.selected_idxs, props.is_mcht, params)
    }
}

const show = () => {
    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <div>
        <VDialog v-model="visible" persistent style="max-width: 600px;">
            <DialogCloseBtn @click="visible = !visible" />
            <VCard title="정산이력 일괄작업">
                <VCardText>
                    <b>선택된 정산이력 : {{ props.selected_idxs.length.toLocaleString() }}개</b>
                    <VDivider style="margin: 1em 0;" />
                    <VRow>
                        <VCol cols="12" style="display: flex; justify-content: space-evenly;">
                            <VBtn prepend-icon="tabler:report-money" @click="getBatchDepositParams()" 
                                size="small">
                                입금/미입금처리
                            </VBtn>
                            <VBtn prepend-icon="tabler:device-tablet-cancel" @click="batchLinkAccount(props.selected_idxs, props.is_mcht)"
                                color="warning" size="small">
                                계좌정보 동기화
                            </VBtn>
                            <VBtn prepend-icon="tabler:device-tablet-cancel" @click="batchCancel(props.selected_idxs, props.is_mcht)"
                                color="error" size="small">
                                정산취소
                            </VBtn>
                        </VCol>
                    </VRow>
                </VCardText>
            </VCard>
        </VDialog>
        <FinanceVanDialog ref="financeDialog" />
    </div>
</template>
