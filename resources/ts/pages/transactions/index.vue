<script setup lang="ts">
import { module_types, installments } from '@/views/merchandises/pay-modules/useStore'
import { useSearchStore } from '@/views/transactions/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import ExtraMenu from '@/views/transactions/ExtraMenu.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import SalesSlipDialog from '@/layouts/dialogs/SalesSlipDialog.vue'
import CancelTransDialog from '@/layouts/dialogs/CancelTransDialog.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { allLevels } from '@/views/salesforces/useStore'
import { user_info } from '@axios'
import corp from '@corp'

const { store, head, exporter } = useSearchStore()

const { pgs, pss, settle_types, terminals, cus_filters } = useStore()
const all_levels = allLevels()
const salesslip = ref()
const cancelTran = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)
provide('salesslip', salesslip)
provide('cancelTran', cancelTran)

store.params.level = 10
store.params.dev_use = corp.pv_options.auth.levels.dev_use

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
const isSalesCol = (key: string) => {
    const sales_cols = ['amount', 'trx_amount', 'mcht_settle_fee', 'hold_amount', 'total_trx_amount', 'profit']
    for (let i = 0; i < sales_cols.length; i++) {
        if (sales_cols[i] === key)
            return true
    }
    return false
}
</script>
<template>
    <div>
        <BaseIndexView placeholder="MID, TID, 승인번호, 거래번호 검색" :metas="metas" :add="user_info.level >= 35" add_name="매출" :is_range_date="true">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :pay_cond="true" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #extra_left>
                        <VCol cols="12" sm="3">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.level" :items="all_levels"
                                :label="`등급 선택`" item-title="title" item-value="id" create />
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
                        <template v-if="key == 'total_trx_amount'">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'총 거래 수수료 = 금액 - (거래 수수료 + 유보금 + 입금 수수료)'">
                            </BaseQuestionTooltip>
                        </template>
                        <template v-else-if="key == 'mcht_settle_fee'">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'입금 수수료는 가맹점만 적용됩니다.'">
                            </BaseQuestionTooltip>
                        </template>
                        <template v-else-if="key == 'hold_amount'">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'유보금은 가맹점만 적용됩니다.'">
                            </BaseQuestionTooltip>
                        </template>
                        <template v-else>
                            <span>
                                {{ header.ko }}
                            </span>
                        </template>
                    </th>
                </tr>
            </template>
            <template #body>
                <tr v-for="(item, index) in store.items" :key="index" style="height: 3.75rem;">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <template v-if="head.getDepth(_header, 0) != 1">
                            <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="!__header.hidden"
                                class='list-square'>
                                <span>
                                    {{ item[_key][__key] }}
                                </span>
                            </td>
                        </template>
                        <template v-else>
                            <td v-show="!_header.hidden" :style="item['is_cancel'] ? 'color:red;' : ''" class='list-square'>
                                <span v-if="_key == 'id'" class="edit-link" @click="store.edit(item['id'])">
                                    #{{ item[_key] }}
                                </span>
                                <span v-else-if="_key == 'module_type'">
                                    <VChip
                                        :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                        {{ module_types.find(obj => obj.id === item[_key])?.title }}
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
                                <span v-else-if="isSalesCol(_key as string)">
                                    {{ Number(item[_key]).toLocaleString() }}
                                </span>
                                <span v-else-if="_key.toString().includes('_fee') && _key != 'mcht_settle_fee'">
                                    <VChip>
                                        {{ (item[_key] * 100).toFixed(3) }} %
                                    </VChip>
                                </span>
                                <span v-else-if="_key == 'pay_cond_price'">
                                    {{ item['settle_fee'] }}
                                </span>
                                <span v-else-if="_key == 'custom_id'">
                                    {{ cus_filters.find(cus => cus.id === item[_key])?.name }}
                                </span>
                                <span v-else-if="_key == 'extra_col'">
                                    <ExtraMenu :item="item"></ExtraMenu>
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
        <SalesSlipDialog ref="salesslip" />
        <CancelTransDialog ref="cancelTran" />
    </div>
</template>
