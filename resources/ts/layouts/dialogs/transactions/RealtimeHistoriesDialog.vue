<script setup lang="ts">
import { Transaction } from '@/views/types'
import { realtimeDetailClass } from '@/views/transactions/useStore'

const visible = ref(false)
const transaction = ref(<Transaction>({}))
const show = (_transaction: Transaction) => {
    transaction.value = _transaction
    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="1200">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="실시간이체 상세이력">
            <VCardText>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>거래번호</th>
                            <th class='list-square'>전송번호</th>
                            <th class='list-square'>결과코드</th>
                            <th class='list-square'>전송타입</th>
                            <th class='list-square'>응답메세지</th>
                            <th class='list-square'>거래금액</th>
                            <th class='list-square'>계좌번호</th>
                            <th class='list-square'>은행명</th>
                            <th class='list-square'>은행코드</th>
                            <th class='list-square'>생성시간</th>
                            <th class='list-square'>업데이트시간</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(history, key) in transaction.realtimes" :key="key" :class="realtimeDetailClass(history)">
                            <td class='list-square'>#{{ history.trans_id }}</td>
                            <td class='list-square'>{{ history.trans_seq_num }}</td>
                            <td class='list-square'>{{ history.result_code }}</td>
                            <td class='list-square'>{{ history.request_type }}</td>
                            <td class='list-square'>{{ history.message }}</td>
                            <td class='list-square'>{{ history.amount }}</td>
                            <td class='list-square'>{{ history.acct_num }}</td>
                            <td class='list-square'>{{ history.acct_bank_name }}</td>
                            <td class='list-square'>{{ history.acct_bank_code }}</td>
                            <td class='list-square'>{{ history.created_at }}</td>
                            <td class='list-square'>{{ history.updated_at }}</td>
                        </tr>
                    </tbody>
                    <!-- 👉 table footer  -->
                    <tfoot v-if="!Boolean(transaction.realtimes?.length)">
                        <tr>
                            <td colspan="12" class='list-square' style="border: 0;">
                                상세이력이 존재하지 않습니다.
                            </td>
                        </tr>
                    </tfoot>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
