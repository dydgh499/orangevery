<script lang="ts" setup>

const visible = ref(false)
const dangers = ref(<any[]>([]))

let resolveCallback: (isAgreed: boolean) => void;
const show = (datas: any[]): Promise<boolean> => {
    visible.value = true
    dangers.value = datas

    return new Promise<boolean>((resolve) => {
        resolveCallback = resolve;
    });
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" persistent max-width="900">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = !visible" />
        <!-- Dialog Content -->
        <VCard title="모아서출금 이상 가맹점 발견">
            <VCardText>
            </VCardText>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>가맹점 상호</th>
                            <th class='list-square'>총 매출액</th>
                            <th class='list-square'>총 정산액</th>
                            <th class='list-square'>총 취소수기입금</th>
                            <th class='list-square'>총 출금가능액</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(danger, key) in dangers" :key="key">
                            <td class='list-square'>{{ danger.mcht_name }}</td>
                            <td class='list-square'>{{ danger.total_amount.toLocaleString() }}</td>
                            <td class='list-square'>{{ danger.settle_amount.toLocaleString() }}</td>
                            <td class='list-square'>{{ danger.cancel_deposit.toLocaleString() }}</td>
                            <td class='list-square text-error'>{{ danger.withdraw_able_amount.toLocaleString() }}</td>
                        </tr>
                    </tbody>
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
