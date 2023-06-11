<script setup lang="ts">
import { module_types, installments } from '@/views/merchandises/pay-modules/useStore';
import { useSearchStore } from '@/views/transactions/useStore';
import { useStore } from '@/views/services/pay-gateways/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue';
import { allLevels } from '@/views/salesforces/useStore';
import corp from '@corp'

const { store } = useSearchStore()
const { pgs, pss, pay_conds, ternimals, cus_filters } = useStore()
const all_levels = allLevels()
provide('store', store)

store.params.level = 10
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
    if (module_id == 0)
        return "default"
    else if (module_id == 1)
        return "primary"
    else if (module_id == 2)
        return "success"
    else if (module_id == 3)
        return "info"
    else if (module_id == 4)
        return "warning"
    else
        return "error"
}
store.params.dev_use = corp.pv_options.auth.levels.dev_use
</script>
<template>
    <BaseIndexView placeholder="가맹점 상호 검색" :metas="metas" :add="true" add_name="가맹점">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :pay_cond="true" :terminal="true" :cus_filter="true" :sales="true">
                <template #extra_left>
                    <VCol cols="12" sm="3">
                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.level" :items="all_levels"
                            :label="`등급 선택`" item-title="title" item-value="id" />
                    </VCol>
                </template>
            </BaseIndexFilterCard>
        </template>
        <template #header>
            <th v-for="(header, index) in store.headers" :key="index" v-show="!header.hidden" class='list-square'>  {{ header.ko }} </th>
        </template>
        <template #body>
            <tr v-for="(user, index) in  store.items " :key="index" style="height: 3.75rem;">
                <td v-for="(header, key, index) in  store.headers " :key="index" v-show="!header.hidden" 
                    :style="user.is_cancel ? 'color:red;' : ''">
                    <span v-if="key == `id`" class="edit-link">
                        #{{ user[key] }}
                    </span>
                    <span v-else-if="key.includes('_fee')">
                        <VChip>
                            {{ (user[key] * 100).toFixed(4) }} %
                        </VChip>
                    </span>
                    <span v-else-if="key == 'module_type'">
                        <VChip :color="getMouduleTypeColor(user[key])">
                            {{ module_types.find(item => item.id === user[key])?.title }}
                        </VChip>
                    </span>
                    <span v-else-if="key == 'installment'">
                        {{ installments.find(item => item.id === user[key])?.title }}
                    </span>
                    <span v-else-if="key == 'pay_cond_price'">
                        {{ user['pay_cond_fee'] }}
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
                    <span v-else-if="key == 'custom_id'">
                        {{ cus_filters.find(item => item.id === user[key])?.name }}
                    </span>
                    <span v-else-if="key == 'amount'">
                        {{ user[key].toLocaleString() }}
                    </span>
                    <span v-else-if="key == 'trx_amount'">
                        {{ user[key].toLocaleString() }}
                    </span>
                    <span v-else-if="key == 'profit'">
                        {{ user[key].toLocaleString() }}
                    </span>
                    <span v-else-if="key == 'extra_col'">
                        <VBtn icon size="x-small" color="default" variant="text">
                            <VIcon size="22" icon="tabler-dots-vertical" />

                            <VMenu activator="parent">
                                <VList>
                                    <VListItem value="saleslip" @click="">
                                        <template #prepend>
                                            <VIcon size="24" class="me-3" icon="tabler:receipt" />
                                        </template>
                                        <VListItemTitle>매출전표</VListItemTitle>
                                    </VListItem>

                                    <VListItem value="complaint" @click="">
                                        <template #prepend>
                                            <VIcon size="24" class="me-3" icon="ic-round-sentiment-dissatisfied" />
                                        </template>
                                        <VListItemTitle>민원처리</VListItemTitle>
                                    </VListItem>
                                    <VListItem value="modify" @click="">
                                        <template #prepend>
                                            <VIcon size="24" class="me-3" icon="tabler-pencil" />
                                        </template>
                                        <VListItemTitle>수정</VListItemTitle>
                                    </VListItem>
                                    <VListItem value="delete" @click="">
                                        <template #prepend>
                                            <VIcon size="24" class="me-3" icon="tabler-trash" />
                                        </template>
                                        <VListItemTitle>삭제</VListItemTitle>
                                    </VListItem>
                                </VList>
                            </VMenu>
                        </VBtn>
                    </span>
                    <span v-else>
                        {{ user[key] }}
                    </span>
                </td>
            </tr>
        </template>
    </BaseIndexView>
</template>
