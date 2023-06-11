<script setup lang="ts">
import { useSearchStore } from '@/views/merchandises/fee-change-histories/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';

const { store } = useSearchStore()
provide('store', store)

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
    <BaseIndexView placeholder="가맹점명 검색" :metas="metas" :add="false" add_name="가맹점">
        <template #filter>
        </template>
        <template #header>
            <th v-for="(header, index) in store.headers" :key="index" v-show="!header.hidden" class='list-square'>  {{ header.ko }} </th>
        </template>
        <template #body>
            <tr v-for="(user, index) in store.items" :key="index" style="height: 3.75rem;">
                <td v-for="(header, key, index) in store.headers" :key="index" v-show="!header.hidden" class='list-square'>  
                    <span v-if="key == `id`" class="edit-link">
                        #{{ user[key] }}
                    </span>
                    <span v-else-if="key == `change_status`">
                        <VChip :color="store.booleanTypeColor(user[key])" >
                            {{ user[key] ? '변경예약' : '변경완료' }}
                        </VChip>
                    </span>
                    <span v-else-if="key.includes('_fee')"> 
                        <VChip>
                            {{ user[key] + "%" }}
                        </VChip>
                    </span>
                    <span v-else> 
                        {{ user[key] }} 
                    </span>
                </td>
            </tr>
        </template>
    </BaseIndexView>
</template>
