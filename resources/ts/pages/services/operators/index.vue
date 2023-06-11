<script setup lang="ts">
import { useSearchStore, operator_levels } from '@/views/services/operators/useStore'
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
const getLevelTypeColor = (level: number) => {
    const id = operator_levels.find(item => item.id === level)?.id
    if(id == 30)
        return "default"
    else if(id == 35)
        return "primary"
    else if(id == 40)
        return "success"
    else if(id == 50)
        return "info"
    else
        return "error"
}
</script>
<template>
    <BaseIndexView placeholder="ID 및 성명 검색" :metas="metas" :add="true" add_name="운영자">
    <template #filter>
    </template>
    <template #header>
        <th v-for="(header, index) in store.headers" :key="index" v-show="!header.hidden" class='list-square'>  {{ header.ko }} </th>
    </template>
    <template #body>
        <tr v-for="(user, index) in store.items" :key="index" style="height: 3.75rem;">
            <td v-for="(header, key, index) in store.headers" :key="index" v-show="!header.hidden" class='list-square'>  
                <span v-if="key == `id`" class="edit-link" @click="store.edit(user.id)">
                    #{{ user[key] }}
                </span>
                <span v-else-if="key == `user_name`" class="edit-link" @click="store.edit(user.id)">
                    {{ user[key] }}
                </span>
                <span v-else-if="key == `level`">
                    <VChip :color="getLevelTypeColor(user[key])">
                        {{ operator_levels.find(item => item.id === user[key])?.name }}
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
