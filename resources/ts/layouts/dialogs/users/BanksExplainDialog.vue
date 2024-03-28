<script setup lang="ts">
import { banks } from '@/views/users/useStore'

const visible = ref(false)

const show = () => {
    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="600">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="입금은행명/은행코드 테이블">
            <VCardText>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>은행명</th>
                            <th class='list-square'>은행코드</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(bank, key) in banks" :key="key">
                            <td class='list-square'>{{ bank.title }}</td>
                            <td class='list-square'>{{ bank.code }}</td>
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
