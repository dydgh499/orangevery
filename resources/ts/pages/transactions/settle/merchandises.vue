<script setup lang="ts">
import { useSalesHierarchicalStore } from '@/views/salesforces/useStore'
import { useSearchStore } from '@/views/transactions/settle-history/useMerchandiseStore'
import BaseIndexOverview from '@/views/utils/BaseIndexOverview.vue';

const { store, setHeaders } = useSearchStore()
const { flattened } = useSalesHierarchicalStore()
provide('store', store)
provide('setHeaders', setHeaders)

const salesforce = ref({trx_fee:0, user_name:'영업자 선택'})
const metas = []
</script>
<template>
    <BaseIndexOverview :placeholder="`ID, 상호, 대표자명 검색`" :metas="metas" :add="false" :update="false">
        <template #options>
            <VCol cols="12" sm="2">
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="salesforce" :items="flattened"
                        prepend-inner-icon="tabler-man" label="영업자 선택"
                        :hint="`수수료율: ${(salesforce.trx_fee*100).toFixed(3)}%`" item-title="user_name" item-value="id"
                        persistent-hint single-line 
                />
            </VCol>
        </template>
        <template #name></template>
    </BaseIndexOverview>
</template>
