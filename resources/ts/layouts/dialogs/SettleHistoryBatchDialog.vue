

<script lang="ts" setup>
import FinanceVanDialog from '@/layouts/dialogs/services/FinanceVanDialog.vue';
import { settlementHistoryFunctionCollect } from '@/views/transactions/settle-histories/SettleHistory';
import corp from '@corp';

interface Props {
    selected_idxs: number[],
    store: any
    is_mcht: boolean,
}

const props = defineProps<Props>()

const financeDialog = ref()
const visible = ref(false)
const snackbar = <any>(inject('snackbar'))

const { batchDeposit, batchCancel, batchLinkAccount, batchOffsetProcessing } = settlementHistoryFunctionCollect(props.store)

const getTotalSettleAmount = () => {
    return props.selected_idxs.reduce((total, id) => {
        const item = props.store.items.find(obj => obj.id === id && obj.deposit_status === 0)
        return item ? total + item.settle_amount : total
    }, 0)
}

const getBatchDepositParams = async () => {
    if (props.selected_idxs.length) {
        const params: any = {
            use_finance_van_deposit: Number(corp.pv_options.paid.use_finance_van_deposit),
        }
        if (params['use_finance_van_deposit']) {
            params['fin_id'] = await financeDialog.value.show(getTotalSettleAmount())
            // 선택안함
            if (params['fin_id'] == 0)
                return 0
            else if(params['fin_id'] == -1)
                params.use_finance_van_deposit = 0
        }
        batchDeposit(props.selected_idxs, props.is_mcht, params)
    }
    else
        snackbar.value.show('입금/미입금처리할 정산 이력을 선택해주세요.', 'error')
}

const offsetProcess = () => {
    if (props.selected_idxs.length)
        batchOffsetProcessing(props.selected_idxs, props.is_mcht)
    else
        snackbar.value.show('상계처리할 정산 이력을 선택해주세요.', 'error')
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
        <VDialog v-model="visible" persistent style="max-width: 700px;">
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
                            <VBtn prepend-icon="carbon:ibm-event-processing" @click="offsetProcess()" 
                                size="small" color="info">
                                상계처리
                            </VBtn>
                            <VBtn prepend-icon="ri-bank-card-fill" @click="batchLinkAccount(props.selected_idxs, props.is_mcht)"
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
