<script setup lang="ts">
import type { Transaction } from '@/views/types'
import { installments } from '@/views/merchandises/pay-modules/useStore'
import { useRequestStore } from '@/views/request'

const store = <any>(inject('store'))
const alert = <any>(inject('alert'))
const { post } = useRequestStore()

const visible = ref(false)
const trans = ref<Transaction>({})

const vForm = ref()
const deposit_amount = ref()
const deposit_history = ref()

const show = (item: Transaction) => {
    trans.value = item
    visible.value = true
}

const submit = async () => {
    const is_valid = await vForm.value.validate();
    if (is_valid.valid && await alert.value.show('정말 해당 취소건의 입금내역을 등록 하시겠습니까?')) {
        const params = {
            trans_id: trans.value.id,
            deposit_amount: deposit_amount.value,
            deposit_history: deposit_history.value,
        }
        const r = await post('/api/v1/manager/transactions/cancel-deposits', params)
        store.setTable()
        visible.value = false
    }
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="800">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="입금내역 등록">
            <VCardText>
                <VTable class="text-no-wrap" style="width: 100%; margin: 1em 0;">
                    <thead>
                        <tr>
                            <th class='list-square' style="width: 25%;">가맹점명</th>
                            <th class='list-square' style="width: 10%;">입금일자</th>
                            <th class='list-square' style="width: 25%;">입금금액</th>
                            <th class='list-square' style="width: 40%;">입금내역</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class='list-square'>{{ trans?.mcht_name }}</td>
                            <td class='list-square'>{{ trans.trx_dt }}</td>
                            <td class='list-square'>
                                <VForm ref="vForm">
                                    <VTextField v-model="deposit_amount" type="number" />
                                </VForm>
                            </td>
                            <td class='list-square'>
                                <VForm ref="vForm">
                                    <VTextField v-model="deposit_history" type="string" />
                                </VForm>
                            </td>
                        </tr>
                    </tbody>
                </VTable>
                <VTable class="text-no-wrap table-content" style="width: 100%;">
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
                            <th class='list-square table-td'>{{ trans?.acquirer + " / " + installments.find(inst => inst['id'] === trans?.installment)?.title }}</th>
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
                            <th class='list-square table-td text-primary'>{{ trans?.profit.toLocaleString() }}</th>
                        </tr>
                    </thead>
                </VTable>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn color="secondary" variant="tonal" @click="visible = false">
                    취소
                </VBtn>
                <VBtn @click="submit()">
                    생성
                </VBtn>
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
</style>
