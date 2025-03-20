<script setup lang="ts">
import { useRequestStore } from '@/views/request';
import SalesRecommenderCodeItem from '@/views/salesforces/sales-recommender-codes/SalesRecommenderCodeItem.vue';
import type { Salesforce, SalesRecommenderCode } from '@/views/types';

const { setNullRemove } = useRequestStore()
const visible = ref(false)
const salesforce = ref(<Salesforce><unknown>({ id: 0, sales_recommender_codes: [], level: 13}))

const addNewSalesRecommenderCode = () => {
    salesforce.value.sales_recommender_codes?.push(<SalesRecommenderCode>({
        id: 0,
        sales_id: salesforce.value.id,
        mcht_fee: 0,
        sales_fee: 0,
        recommend_code: '',
    }))
}
watchEffect(() => {
    if(salesforce.value.sales_recommender_codes && salesforce.value.sales_recommender_codes?.length)
        setNullRemove(salesforce.value.sales_recommender_codes)
})

const show = (_salesforce: Salesforce) => {
    salesforce.value = _salesforce
    visible.value = true
}

defineExpose({
    show
});
</script>
<template>
    <VDialog v-model="visible" max-width="450">
        <DialogCloseBtn @click="visible = false" />
        <VCard title="추천인코드 관리">
            <VCardText>
                <SalesRecommenderCodeItem 
                v-for="(sales_recommender_code, index) in salesforce.sales_recommender_codes"
                        :key="sales_recommender_code.id" 
                        :item="sales_recommender_code" 
                        :level="salesforce.level"
                        :parent_total_fee="salesforce?.parent_total_fee ?? 0"
                        :p2p_pay_fee="salesforce?.p2p_pay_fee ?? 0"
                        />
                <VRow v-show="Boolean(salesforce.id != 0)">
                    <VCol class="d-flex gap-4">
                        <VBtn type="button" 
                            size="small"
                            style="margin-left: auto;" 
                            @click="addNewSalesRecommenderCode()">
                            추가하기
                        </VBtn>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
    </VDialog>
</template>
<style scoped>
:deep(.v-row) {
  align-items: center;
}

:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
