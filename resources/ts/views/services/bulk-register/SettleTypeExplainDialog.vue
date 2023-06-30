<script setup lang="ts">
import { useStore } from '@/views/services/pay-gateways/useStore'

const visible = ref(false)

const show = () => {
    visible.value = true
}
const { settle_types } = useStore()

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="600">
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="visible = false" />
        <!-- Dialog Content -->
        <VCard title="가맹점 정산타입 정보">
            <VCardText>
                <VTable class="text-no-wrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class='list-square'>정산타입</th>
                            <th class='list-square'>입력값</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(bank, key) in settle_types" :key="key">
                            <td class='list-square'>{{ bank.name }}</td>
                            <td class='list-square'>{{ bank.id }}</td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
