<script setup lang="ts">
import { VirtualAccountWithdraw } from '@/views/types'
import { withdrawInterface } from '@/views/virtual-accounts/histories/useStore'

const visible = ref(false)
const histories = ref(<VirtualAccountWithdraw[]>([]))

const { withdrawStatusColors } = withdrawInterface()

const show = (_histories: VirtualAccountWithdraw[]) => {
    histories.value = _histories
    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="1200">
        <DialogCloseBtn @click="visible = false" />
        <VCard title="출금시도 상세이력">
            <VCardText>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>거래 ID</th>
                            <th class='list-square'>전송번호</th>
                            <th class='list-square'>결과코드</th>
                            <th class='list-square'>전송타입</th>
                            <th class='list-square'>응답메세지</th>
                            <th class='list-square'>출금시도금액</th>
                            <th class='list-square'>계좌명</th>
                            <th class='list-square'>계좌번호</th>
                            <th class='list-square'>은행명</th>
                            <th class='list-square'>은행코드</th>
                            <th class='list-square'>생성시간</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(history, key) in histories" :key="key" :class="withdrawStatusColors(history)">
                            <td class='list-square'>#{{ history.id }}</td>
                            <td class='list-square'>{{ history.trans_seq_num }}</td>
                            <td class='list-square'>{{ history.result_code }}</td>
                            <td class='list-square'>{{ history.request_type }}</td>
                            <td class='list-square'>{{ history.note }}</td>
                            <td class='list-square'>{{ history.trans_amount.toLocaleString() }}</td>
                            <td class='list-square'>{{ history.acct_name }}</td>
                            <td class='list-square'>{{ history.acct_num }}</td>
                            <td class='list-square'>{{ history.acct_bank_name }}</td>
                            <td class='list-square'>{{ history.acct_bank_code }}</td>
                            <td class='list-square'>{{ history.created_at }}</td>
                        </tr>
                    </tbody>
                    <!-- 👉 table footer  -->
                    <tfoot v-if="!Boolean(histories.length)">
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
