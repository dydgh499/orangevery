<script setup lang="ts">
import { useSearchStore } from '@/views/merchandises/useStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue';
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
    <BaseIndexView placeholder="가맹점 상호 검색" :metas="metas" :add="true" add_name="가맹점">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :pay_cond="true" :terminal="true" :cus_filter="true"  :sales="true"/>
        </template>
        <template #header>
            <th v-for="(header, index) in store.headers" :key="index" v-show="!header.hidden"> {{ header.ko }} </th>
        </template>
        <template #body>
            <tr v-for="(user, index) in store.items" :key="index" style="height: 3.75rem;">
                <td v-for="(header, key, index) in store.headers" :key="index" v-show="!header.hidden"> 
                    <span v-if="key == `id`" class="edit-link" @click="store.edit(user.id)">
                        #{{ user[key] }}
                    </span>
                    <span v-else-if="key == `user_name`" class="edit-link" @click="store.edit(user.id)">
                        {{ user[key] }}
                    </span>
                    <span v-else-if="key.includes('_fee')"> 
                        <VChip>
                            {{ user[key] }} %
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
