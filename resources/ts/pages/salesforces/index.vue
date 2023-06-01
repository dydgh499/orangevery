<script setup lang="ts">
import { useSearchStore } from '@/views/salesforces/useStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue';
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import { classes } from '@/views/salesforces/useStore';

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

const getSalesTypeColor = (_class: number) => {
    const id = classes.find(item => item.id === _class)?.id
    if(id == 0)
        return "default"
    else if(id == 1)
        return "primary"
    else if(id == 2)
        return "success"
    else if(id == 3)
        return "info"
    else if(id == 4)
        return "warning"
    else if(id == 5)
        return "error"
    else
        return 'default';
}
</script>
<template>
    <BaseIndexView placeholder="ID, 대표자명 검색" :metas="metas" :add="true" add_name="영업점">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :pay_cond="true" :terminal="true" :cus_filter="true" />
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
                    <span v-else-if="key == 'user_name'" class="edit-link" @click="store.edit(user.id)">
                        {{ user[key] }}
                    </span>
                    <span v-else-if="key == 'class'"> 
                        <VChip :color="getSalesTypeColor(user[key])">
                            {{ classes.find(item => item.id === user[key])?.title }}
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
