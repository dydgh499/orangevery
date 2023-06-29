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
                <VTable>
                    <thead>
                        <tr>
                            <td>은행명</td>
                            <td>은행코드</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(bank, key) in banks" :key="key">
                            <td>{{ bank.title }}</td>
                            <td>{{ bank.code }}</td>
                        </tr>
                    </tbody>
                </VTable>
            </VCardText>
        </VCard>
    </VDialog>
</template>
