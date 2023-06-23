<script setup lang="ts">
import { module_types, installments } from '@/views/merchandises/pay-modules/useStore';
import { useSearchStore } from '@/views/transactions/useStore';
import { useStore } from '@/views/services/pay-gateways/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue';
import { allLevels } from '@/views/salesforces/useStore';
import corp from '@corp'

const { store, head, exporter } = useSearchStore()

const { pgs, pss, settle_types, terminals, cus_filters } = useStore()
const all_levels = allLevels()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

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
    <BaseIndexView placeholder="가맹점 상호 검색" :metas="metas" :add="true" add_name="가맹점" :is_range_date="true">
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
        <template #headers>
            <tr>
                <th v-for="(colspan, index) in head.getColspansComputed" :colspan="colspan" :key="index"
                    class='list-square'>
                    <span>
                        {{ head.main_headers[index] }}
                    </span>
                </th>
            </tr>
            <tr>
                <th v-for="(header, key) in head.flat_headers" :key="key" v-show="!header.hidden" class='list-square'>
                    <span>
                        {{ header.ko }}
                    </span>
                </th>
            </tr>
        </template>
        <template #body>
            <tr v-for="(item, index) in store.items" :key="index" style="height: 3.75rem;">
                <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                    <template v-if="head.getDepth(_header, 0) != 1">
                        <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="!__header.hidden" class='list-square'>
                            <span>
                                {{ item[_key][__key] }}
                            </span>
                        </td>
                    </template>
                    <template v-else>
                        <td v-show="!_header.hidden" :style="item['is_cancel'] ? 'color:red;' : ''" class='list-square'>
                            <span v-if="_key == 'id'" class="edit-link">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == 'module_type'">
                                <VChip :color="getMouduleTypeColor(item[_key])">
                                    {{ module_types.find(module_type => module_type['id'] === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'installment'">
                                {{ installments.find(inst => inst['id'] === item[_key])?.title }}
                            </span>
                            <span v-else-if="_key == 'pg_id'">
                                {{ pgs.find(pg => pg['id'] === item[_key])?.pg_nm }}
                            </span>
                            <span v-else-if="_key == 'ps_id'">
                                {{ pss.find(ps => ps['id'] === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'settle_type'">
                                {{ settle_types.find(settle_type => settle_type['id'] === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'terminal_id'">
                                {{ terminals.find(terminal => terminal['id'] === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'amount'">
                                {{ Number(item[_key]).toLocaleString() }}
                            </span>
                            <span v-else-if="_key == 'trx_amount'">
                                {{ Number(item[_key]).toLocaleString() }}
                            </span>
                            <span v-else-if="_key == 'profit'">
                                {{ Number(item[_key]).toLocaleString() }}
                            </span>
                            <span v-else-if="_key.includes('_fee')">
                                <VChip>
                                    {{ (item[_key] * 100).toFixed(4) }} %
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'pay_cond_price'">
                                {{ item['settle_fee'] }}
                            </span>                            
                            <span v-else-if="_key == 'custom_id'">
                                {{ cus_filters.find(cus => cus.id === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'extra_col'">
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
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </template>
            </tr>
        </template>
    </BaseIndexView>
</template>
