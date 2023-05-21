<script setup lang="ts">

import { useSearchStore } from '@/views/pay-modules/usePayModStore'
import BaseIndexOverview from '@/views/utils/BaseIndexOverview.vue';

// 👉 Store
const {store, pagenation, setHeaders} = useSearchStore()
provide('store', store)
provide('pagenation', pagenation)
provide('setHeaders', setHeaders)

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
    <BaseIndexOverview :placeholder="`MID, TID 검색`" :metas="metas">
        <template #name>결제모듈</template>
    </BaseIndexOverview>
</template>
