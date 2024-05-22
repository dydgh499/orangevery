<script setup lang="ts">
import { installments } from '@/views/merchandises/pay-modules/useStore'
import { useRequestStore } from '@/views/request'
import type { CancelDeposit, Transaction } from '@/views/types'
import { cloneDeep } from 'lodash'
import { VForm } from 'vuetify/components'

const formatDate = <any>(inject('$formatDate'))
const { update, remove } = useRequestStore()

const visible = ref(false)
const trans = ref<Transaction>()
const vForm = ref<VForm>()
const cancel_deposit = ref(<CancelDeposit>({}))

const show = (item: Transaction) => {
    trans.value = item
    visible.value = true
    init(trans.value?.id)

}

const init = (trans_id: number) => {
    cancel_deposit.value.id = 0
    cancel_deposit.value.trans_id = trans_id
    cancel_deposit.value.deposit_amount = 0
    cancel_deposit.value.deposit_history = ''
    cancel_deposit.value.deposit_dt = formatDate(new Date())
}

const closeDialog = () => {
    visible.value = false
    Object.assign(trans.value?.cancel_deposits as CancelDeposit[], trans.value?.cancel_deposits?.filter(item => item.id !== 0))
}

const cancelDepositUpdate = async(_cancel_deposit: CancelDeposit) => {
    const res = await update('/transactions/settle/merchandises/cancel-deposits', _cancel_deposit, vForm.value, false)
    if(res.status == 201) {
        trans.value?.cancel_deposits?.unshift(cloneDeep(cancel_deposit.value))
        init(cancel_deposit.value.trans_id)
    }
}

const cancelDepositRemove = async(_cancel_deposit: CancelDeposit) => {
    const res = await remove('/transactions/settle/merchandises/cancel-deposits', _cancel_deposit, false)
    if(res.status == 201) {
        const idx = trans.value?.cancel_deposits?.findIndex(obj => obj.id === _cancel_deposit.id)
        if(idx !== undefined)
            trans.value?.cancel_deposits?.splice(idx, 1)
    }
}

const totalSettleAmount = computed(() => {
    const total_cancel_deposit = trans.value?.cancel_deposits?.reduce((sum, item) => {
        return parseInt(sum) + (item.deposit_amount ? parseInt(item.deposit_amount) : 0)
    }, 0) 
    return parseInt(trans.value?.profit) + parseInt(total_cancel_deposit)
})
defineExpose({
    show
});
onMounted(() => {
})
</script>
<template>
    <VDialog v-model="visible" max-width="1000">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="closeDialog()" />
        <!-- Dialog Content -->
        <VCard title="가맹점 입금내역 등록">
            <VCardText>
                <VForm ref="vForm">
                </VForm>
                <VTable class="text-no-wrap" style="width: 100%; margin: 1em 0;">
                    <thead>
                        <tr>
                            <th class='list-square' style="width: 25%;">가맹점명</th>
                            <th class='list-square' style="width: 25%;">입금일자</th>
                            <th class='list-square' style="width: 25%;">입금금액</th>
                            <th class='list-square' style="width: 25%;">입금내역</th>
                            <th class='list-square' style="width: 25%;">수정/삭제</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class='list-square'>{{ trans?.mcht_name }}</td>
                            <td class='list-square'>
                                <VTextField v-model="cancel_deposit.deposit_dt" type="date" />
                            </td>
                            <td class='list-square'>
                                <VTextField v-model="cancel_deposit.deposit_amount" type="number" />
                            </td>
                            <td class='list-square'>
                                <VTextField v-model="cancel_deposit.deposit_history" type="string" />
                            </td>
                            <td class="text-center">
                                <VCol class="d-flex gap-4">
                                    <VBtn type="button" color="default" variant="text"
                                        @click="cancelDepositUpdate(cancel_deposit)">
                                        {{ cancel_deposit.id == 0 ? "추가" : "수정" }}
                                        <VIcon end icon="tabler-pencil" />
                                    </VBtn>
                                    <VBtn type="button" color="default" variant="text" v-if="cancel_deposit.id"
                                        @click="cancelDepositRemove(cancel_deposit)">
                                        삭제
                                        <VIcon end icon="tabler-trash" />
                                    </VBtn>
                                </VCol>
                            </td>
                        </tr>
                        <tr v-for="(_cancel_deposit, key) in trans?.cancel_deposits" :key="key">
                            <td class='list-square'>{{ trans?.mcht_name }}</td>
                            <td class='list-square'>
                                <VTextField v-model="_cancel_deposit.deposit_dt" type="date" />
                            </td>
                            <td class='list-square'>
                                <VTextField v-model="_cancel_deposit.deposit_amount" type="number" />
                            </td>
                            <td class='list-square'>
                                <VTextField v-model="_cancel_deposit.deposit_history" type="string" />
                            </td>
                            <td class="text-center">
                                <VCol class="d-flex gap-4">
                                    <VBtn type="button" color="default" variant="text"
                                        @click="cancelDepositUpdate(_cancel_deposit)">
                                        {{ _cancel_deposit.id == 0 ? "추가" : "수정" }}
                                        <VIcon end icon="tabler-pencil" />
                                    </VBtn>
                                    <VBtn type="button" color="default" variant="text" v-if="_cancel_deposit.id"
                                        @click="cancelDepositRemove(_cancel_deposit)">
                                        삭제
                                        <VIcon end icon="tabler-trash" />
                                    </VBtn>
                                </VCol>
                            </td>
                        </tr>
                    </tbody>
                </VTable>
                <VTable class="text-no-wrap table-content" style="width: 100%; margin: 1em 0;">
                    <thead>
                        <tr>
                            <th class='list-square table-th'>취소일자</th>
                            <th class='list-square table-td important-text'>{{ trans?.cxl_dttm }}</th>
                            <th class='list-square table-th'>원거래일자</th>
                            <th class='list-square table-td'>{{ trans?.trx_dttm }}</th>
                        </tr>
                        <tr>
                            <th class='list-square table-th'>MID</th>
                            <th class='list-square table-td'>{{ trans?.mid }}</th>
                            <th class='list-square table-th'>TID</th>
                            <th class='list-square table-td'>{{ trans?.tid }}</th>
                        </tr>
                        <tr>
                            <th class='list-square table-th'>승인번호</th>
                            <th class='list-square table-td'>{{ trans?.appr_num }}</th>
                            <th class='list-square table-th'>매입사/할부</th>
                            <th class='list-square table-td'>{{ trans?.acquirer + " / " + installments.find(inst =>
                                inst['id'] === trans?.installment)?.title }}</th>
                        </tr>
                        <tr>
                            <th class='list-square table-th'>취소금액</th>
                            <th class='list-square table-td important-text'>{{ trans?.amount.toLocaleString() }}</th>
                            <th class='list-square table-th'>정산금</th>
                            <th class='list-square table-td important-text'>{{ trans?.profit.toLocaleString() }}</th>
                        </tr>
                        <tr>
                            <th class='list-square table-th'>수수료</th>
                            <th class='list-square table-td'>{{ trans?.trx_amount.toLocaleString() }}</th>
                            <th class='list-square table-th'>미차감잔액</th>
                            <th class='list-square table-td text-primary'>{{ totalSettleAmount.toLocaleString() }}</th>
                        </tr>
                    </thead>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
.table-content {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.table-th,
.table-td {
  border-inline-end: thin solid rgba(var(--v-border-color), var(--v-border-opacity)) !important;
}

.important-text {
  color: red !important;
}

:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
