<script setup lang="ts">
import { useSearchStore } from '@/views/merchandises/noti-send-histories/useStore'
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
const getComplainTypeColor = (http_code: number) => {
    return http_code >= 200 && http_code <= 299 ? 'success' : 'error'
}
</script>
<template>
    <BaseIndexView placeholder="발송 URL 검색" :metas="metas" :add="false" add_name="가맹점">
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
                    <span v-else-if="key == `trans_id`" class="edit-link">
                        #{{ user[key] }}
                    </span>
                    <span v-else-if="key == `http_code`">
                        <VChip :color="getComplainTypeColor(Number(user[key]))">
                            {{ user[key]  }}
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
