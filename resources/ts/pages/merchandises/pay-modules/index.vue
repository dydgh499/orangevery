<script setup lang="ts">
import { useSearchStore } from '@/views/merchandises/pay-modules/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { classes } from '@/views/salesforces/useStore';
import { module_types, installments } from '@/views/merchandises/pay-modules/useStore';
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue';
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';

const { pgs, pss, pay_conds, ternimals } = useStore()

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
const getMouduleTypeColor = (id: number) => {
    const module_id = module_types.find(item => item.id === id)?.id
    if(module_id == 0)
        return "default"
    else if(module_id == 1)
        return "primary"
    else if(module_id == 2)
        return "success"
    else if(module_id == 3)
        return "info"
    else if(module_id == 4)
        return "warning"
    else
        return "error"
}
</script>
<template>
    <BaseIndexView placeholder="MID, TID 검색" :metas="metas" :add="true" add_name="결제모듈">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :pay_cond="true" :terminal="true" :cus_filter="true" />
        </template>
        <template #header>
            <th v-for="(header, index) in store.headers" :key="index" v-show="!header.hidden"> {{ header.ko }} </th>
        </template>
        <template #body>
            <tr v-for="(user, index) in store.items" :key="index" style="height: 3.75rem;">
                <td v-for="(header, key, index) in store.headers" :key="index" v-show="!header.hidden"> 

                    <span v-if="key == 'id'" class="edit-link" @click="store.edit(user.id)">
                        #{{ user[key] }}
                    </span>
                    <span v-else-if="key == 'note'" class="edit-link" @click="store.edit(user.id)">
                        {{ user[key] }}
                    </span>
                    <span v-else-if="key == 'module_type'"> 
                        <VChip :color="getMouduleTypeColor(user[key])">
                            {{ module_types.find(item => item.id === user[key])?.title }}
                        </VChip>
                    </span>
                    <span v-else-if="key == 'installment'"> 
                        {{ installments.find(item => item.id === user[key])?.title }}
                    </span>
                    <span v-else-if="key == 'pg_id'"> 
                        {{ pgs.find(item => item.id === user[key])?.pg_nm }}
                    </span>
                    <span v-else-if="key == 'ps_id'"> 
                        {{ pss.find(item => item.id === user[key])?.name }}
                    </span>
                    <span v-else-if="key == 'pay_cond_id'"> 
                        {{ pay_conds.find(item => item.id === user[key])?.name }}
                    </span>
                    <span v-else-if="key == 'terminal_id'"> 
                        {{ ternimals.find(item => item.id === user[key])?.name }}
                    </span>
                    <span v-else-if="key == 'comm_pr'"> 
                        {{ user[key].toLocaleString() }}
                    </span>    
                    <span v-else-if="key == 'comm_calc_class'"> 
                        {{ classes.find(item => item.id === user[key])?.title }}
                    </span>    
                    <span v-else> 
                        {{ user[key] }} 
                    </span>

                </td>
            </tr>
        </template>
    </BaseIndexView>
</template>
