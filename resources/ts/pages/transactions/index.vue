<script setup lang="ts">
import { useSalesFilterStore } from '@/views/salesforces/useStore'
import BaseIndexOverview from '@/layouts/lists/BaseIndexOverview.vue';
import { useSearchStore } from '@/views/transactions/useStore';

const {store, setHeaders} = useSearchStore()
const { flattened } = useSalesFilterStore()
provide('store', store)
provide('setHeaders', setHeaders)

const salesforce = ref({trx_fee:0, user_name:'영업점 선택'})
const metas = [
    {
        icon: 'tabler-user',
        color: 'primary',
        title: '금월 추가된 가맹점',
        stats: '21,459',
        percentage: +29,
        subtitle: 'Total Users',
    },
    {
        icon: 'tabler-user-plus',
        color: 'error',
        title: '금주 추가된 가맹점',
        stats: '4,567',
        percentage: +18,
        subtitle: 'Last week analytics',
    },
    {
        icon: 'tabler-user-check',
        color: 'success',
        title: '금월 감소한 가맹점',
        stats: '19,860',
        percentage: -14,
        subtitle: 'Last week analytics',
    },
    {
        icon: 'tabler-user-exclamation',
        color: 'warning',
        title: '금주 감소한 가맹점',
        stats: '237',
        percentage: +42,
        subtitle: 'Last week analytics',
    },
]
</script>
<template>
    <BaseIndexOverview :placeholder="`MID, TID, 거래번호 검색`" :metas="metas" :add="false" :update="true">
        <template #options>
            <VCol cols="12" sm="2">
                <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="salesforce" :items="flattened"
                        prepend-inner-icon="tabler-man" label="영업점 선택"
                        :hint="`수수료율: ${(salesforce.trx_fee*100).toFixed(3)}%`" item-title="user_name" item-value="id"
                        persistent-hint single-line 
                />
            </VCol>
        </template>
        <template #name></template>
    </BaseIndexOverview>
</template>

