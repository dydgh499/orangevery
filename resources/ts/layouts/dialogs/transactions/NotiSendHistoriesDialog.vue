<script setup lang="ts">
import { notiSendHistoryInterface } from '@/views/transactions/transactions'
import { Transaction } from '@/views/types'

const visible = ref(false)
const transaction = ref(<Transaction>({}))

const { notiSendDetailClass } = notiSendHistoryInterface()

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
        <VCard title="노티발송 이력">
            <VCardText>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>발송 URL</th>
                            <th class='list-square'>응답코드</th>
                            <th class='list-square'>응답내용</th>
                            <th class='list-square'>재시도 회수</th>
                            <th class='list-square'>발송시간</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(history, key) in transaction.noti_send_histories" :key="key" :class="notiSendDetailClass(history)">
                            <td class='list-square'>{{ history.send_url }}</td>
                            <td class='list-square'>{{ history.http_code }}</td>
                            <td class='list-square'>{{ history.message }}</td>
                            <td class='list-square'>{{ history.retry_count }}</td>
                            <td class='list-square'>{{ history.created_at }}</td>
                        </tr>
                    </tbody>
                    <tfoot v-if="!Boolean(transaction.noti_send_histories?.length)">
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
