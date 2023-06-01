<script lang="ts" setup>
import type { Classification } from '@/views/types'
import ClassificationTr from '@/views/services/pay-gateways/ClassificationTr.vue';
import { useStore } from '@/views/services/pay-gateways/useStore'

const { ternimals, pay_conds, cus_filters } = useStore()


const new_terminals = reactive<Classification[]>([]);
const new_pay_conds = reactive<Classification[]>([]);
const new_cus_filters = reactive<Classification[]>([]);
const addNewClassification = (items: Classification[], type: number) => {
    items.push(<Classification>(
        {
            id: 0,
            trx_fee:0,
            type: type,
        }
    ))
}

</script>
<template>
    <VRow class="match-height">
        <VCol cols="12" md="6">
            <VCard>
                <VCardItem>
                    <VCardTitle style="margin-bottom: 1em;">결제 조건</VCardTitle>
                    <VTable style="text-align: center;">
                        <thead>
                            <tr>
                                <th scope="col" style="text-align: center;">No.</th>
                                <th scope="col" style="text-align: center;">결제 조건명</th>
                                <th scope="col" style="text-align: center;">사용 수수료</th>
                                <th scope="col" style="text-align: center;">추가/수정</th>
                            </tr>
                        </thead>
                        <tbody>
                            <ClassificationTr v-for="(item, index) in pay_conds" :key="index" :item="item" :index="index">
                            </ClassificationTr>
                            <ClassificationTr v-for="(item, index) in new_pay_conds" :key="index" :item="item"
                                :index="index">
                            </ClassificationTr>
                        </tbody>
                    </VTable>
                    <VRow>
                        <VCol class="d-flex gap-4 pt-10">
                            <VBtn type="button" style="margin-left: auto;" @click="addNewClassification(new_pay_conds, 1)">
                                결제 조건 추가
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
                    <VCardTitle style="margin-bottom: 1em;">단말기 종류</VCardTitle>
                    <VTable style="text-align: center;">
                        <thead>
                            <tr>
                                <th scope="col" style="text-align: center;">No.</th>
                                <th scope="col" style="text-align: center;">단말기명</th>
                                <th scope="col" style="text-align: center;">사용 수수료</th>
                                <th scope="col" style="text-align: center;">추가/수정</th>
                            </tr>
                        </thead>
                        <tbody>
                            <ClassificationTr v-for="(item, index) in ternimals" :key="index" :item="item" :index="index">
                            </ClassificationTr>
                            <ClassificationTr v-for="(item, index) in new_terminals" :key="index" :item="item"
                                :index="index">
                            </ClassificationTr>
                        </tbody>
                    </VTable>
                    <VRow>
                        <VCol class="d-flex gap-4 pt-10">
                            <VBtn type="button" style="margin-left: auto;" @click="addNewClassification(new_terminals, 0)">
                                단말기 종류
                                <VIcon end icon="tabler-plus" />
                            </VBtn>
                        </VCol>
                    </VRow>
                </VCardItem>
                <VCardItem>
                    <VCardTitle style="margin-bottom: 1em;">커스텀 필터</VCardTitle>
                    <VTable style="text-align: center;">
                        <thead>
                            <tr>
                                <th scope="col" style="text-align: center;">No.</th>
                                <th scope="col" style="text-align: center;">커스텀 필터명</th>
                                <th scope="col" style="text-align: center;">사용 수수료</th>
                                <th scope="col" style="text-align: center;">추가/수정</th>
                            </tr>
                        </thead>
                        <tbody>
                            <ClassificationTr v-for="(item, index) in cus_filters" :key="index" :item="item" :index="index">
                            </ClassificationTr>
                            <ClassificationTr v-for="(item, index) in new_cus_filters" :key="index" :item="item"
                                :index="index">
                            </ClassificationTr>
                        </tbody>
                    </VTable>
                    <VRow>
                        <VCol class="d-flex gap-4 pt-10">
                            <VBtn type="button" style="margin-left: auto;"
                                @click="addNewClassification(new_cus_filters, 2)">
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
