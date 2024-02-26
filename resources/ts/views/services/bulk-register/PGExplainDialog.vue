<script setup lang="ts">
import { useStore } from '@/views/services/pay-gateways/useStore'

const visible = ref(false)
const { pgs, pss } = useStore()


const filterPgs = (pg_id: number) => {
    return pss.filter(item => { return item.pg_id == pg_id })
}
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
        <VCard title="PG사/구간명 정보">
            <VCardText>
                <template v-for="(pg, key) in pgs" :key="key">
                    <span style="font-weight: bold;">PG사명: {{ pg.pg_name }}</span>
                    <br>
                    <b>입력 값: <span style="color: red;">{{ pg.id }}</span></b>
                    <VTable class="text-no-wrap" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class='list-square'>{{ pg.pg_name }} 구간명</th>
                                <th class='list-square'>입력 값</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(ps, key) in filterPgs(pg.id ?? 0)" :key="key">
                                <td class='list-square'>{{ ps.name }}</td>
                                <td class='list-square'>{{ ps.id }}</td>
                            </tr>
                        </tbody>
                    </VTable>
                    <br>
                </template>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>