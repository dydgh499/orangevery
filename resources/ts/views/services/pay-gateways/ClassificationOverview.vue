<script lang="ts" setup>
import type { Classification } from '@/views/types'
import ClassificationTr from '@/views/services/pay-gateways/ClassificationTr.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useRequestStore } from '@/views/request'

const { terminals, cus_filters } = useStore()
const { setNullRemove } = useRequestStore()

const addNewClassification = (items: Classification[], type: number) => {
    items.push(<Classification>({
        id: 0,
        type: type,
    }))
}

watchEffect(() => {
    setNullRemove(terminals)
    setNullRemove(cus_filters)
})
</script>
<template>
    <VRow class="match-height">
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle style="margin-bottom: 1em;">장비 종류</VCardTitle>
                    <VTable style="text-align: center;">
                        <thead>
                            <tr>
                                <th scope="col" style="text-align: center;">No.</th>
                                <th scope="col" style="text-align: center;">장비명</th>
                                <th scope="col" style="text-align: center;">추가/수정</th>
                            </tr>
                        </thead>
                        <tbody>
                            <ClassificationTr v-for="(item, index) in terminals" :key="index" :item="item" :index="index"
                                :base_count="0">
                            </ClassificationTr>
                        </tbody>
                    </VTable>
                    <VRow>
                        <VCol class="d-flex gap-4 pt-10">
                            <VBtn type="button" style="margin-left: auto;" @click="addNewClassification(terminals, 0)">
                                장비 종류
                                <VIcon end icon="tabler-plus" />
                            </VBtn>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle style="margin-bottom: 1em;">커스텀 필터</VCardTitle>
                    <VTable style="text-align: center;">
                        <thead>
                            <tr>
                                <th scope="col" style="text-align: center;">No.</th>
                                <th scope="col" style="text-align: center;">커스텀 필터명</th>
                                <th scope="col" style="text-align: center;">추가/수정</th>
                            </tr>
                        </thead>
                        <tbody>
                            <ClassificationTr v-for="(item, index) in cus_filters" :key="index" :item="item" :index="index"
                                :base_count="0">
                            </ClassificationTr>
                        </tbody>
                    </VTable>
                    <VRow>
                        <VCol class="d-flex gap-4 pt-10">
                            <VBtn type="button" style="margin-left: auto;"
                                @click="addNewClassification(cus_filters, 1)">
                                커스텀 필터 추가
                                <VIcon end icon="tabler-plus" />
                            </VBtn>
                        </VCol>
                    </VRow>
                </VCardItem>
            </VCard>
        </VCol>
    </VRow>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
  </style>
