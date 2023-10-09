<script setup lang="ts">
import { module_types, installments } from '@/views/merchandises/pay-modules/useStore'
import { useSearchStore, memo } from '@/views/transactions/settle-histories/useDifferenceStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import { getUserLevel } from '@axios'
import corp from '@corp'

const { store, head, exporter } = useSearchStore()
const { pgs, pss, settle_types, terminals, cus_filters } = useStore()

const alert = <any>(inject('alert'))

provide('store', store)
provide('head', head)
provide('exporter', exporter)

store.params.level = 10
store.params.dev_use = corp.pv_options.auth.levels.dev_use
store.params.is_use_realtime_deposit = Number(corp.pv_options.paid.use_realtime_deposit)

const metas = ref([
    {
        icon: 'ic-outline-payments',
        color: 'primary',
        title: '거래액 합계',
        stats: '0',
    },
    {
        icon: 'ic-outline-payments',
        color: 'error',
        title: '공급가액 합계',
        stats: '0',
    },
    {
        icon: 'ic-outline-payments',
        color: 'success',
        title: '부가세 합계',
        stats: '0',
    },
    {
        icon: 'ic-outline-payments',
        color: 'warning',
        title: '차액정산금 합계',
        stats: '0',
    },
])
const isSalesCol = (key: string) => {
    const sales_cols = ['amount', 'supply_amount', 'vat_amount', 'settle_amount']
    for (let i = 0; i < sales_cols.length; i++) {
        if (sales_cols[i] === key)
            return true
    }
    return false
}
onMounted(() => {
    watchEffect(async () => {
        if (store.getChartProcess() === false) {
            const r = await store.getChartData()
            metas.value[0]['stats'] = r.data.amount.toLocaleString() + ' ￦'
            metas.value[1]['stats'] = r.data.supply_amount.toLocaleString() + ' ￦'
            metas.value[2]['stats'] = r.data.vat_amount.toLocaleString() + ' ￦'
            metas.value[3]['stats'] = r.data.settle_amount.toLocaleString() + ' ￦'
        }
    })
})
watchEffect(() => {
    store.setChartProcess()
    store.params.level = store.params.level
    store.params.mcht_settle_type = store.params.mcht_settle_type
})
</script>
<template>
    <div>
        <BaseIndexView placeholder="MID, TID, 승인번호, 거래번호, 주민번호, 사업자번호 검색" :metas="metas" :add="false" add_name=""
            :is_range_date="true">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #sales_extra_field>
                    </template>
                    <template #pg_extra_field>
                        <VCol cols="12" sm="3" v-if="getUserLevel() >= 35">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.mcht_settle_type"
                                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산타입 필터" item-title="name"
                                item-value="id" />
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                
                <VBtn prepend-icon="ic:outline-help" @click="alert.show(memo)" >
                    차액정산 설명서
                </VBtn>
            </template>
            <template #headers>
                <tr>
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                        <span>
                            {{ header.ko }}
                        </span>
                    </th>
                </tr>
            </template>
            <template #body>
                <tr v-for="(item, index) in store.getItems" :key="item['id']">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_key">
                        <td v-if="_header.visible" :style="item['is_cancel'] ? 'color:red;' : ''" class='list-square'>
                            <span v-if="_key == 'id'">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == 'module_type'">
                                <VChip :color="store.getSelectIdColor(module_types.find(obj => obj.id === item[_key])?.id)">
                                    {{ module_types.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'installment'">
                                {{ installments.find(inst => inst['id'] === item[_key])?.title }}
                            </span>
                            <span v-else-if="_key == 'pg_id'">
                                {{ pgs.find(pg => pg['id'] === item[_key])?.pg_name }}
                            </span>
                            <span v-else-if="_key == 'ps_id'">
                                {{ pss.find(ps => ps['id'] === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'mcht_settle_type'">
                                {{ settle_types.find(settle_type => settle_type['id'] === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'terminal_id'">
                                {{ terminals.find(terminal => terminal['id'] === item[_key])?.name }}
                            </span>
                            <span v-else-if="isSalesCol(_key as string)">
                                {{ Number(item[_key]).toLocaleString() }}
                            </span>
                            <span v-else-if="_key.toString().includes('_fee') && _key != 'mcht_settle_fee'">
                                <VChip v-if="item[_key]">
                                    {{ (item[_key] * 100).toFixed(3) }} %
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'custom_id'">
                                {{ cus_filters.find(cus => cus.id === item[_key])?.name }}
                            </span>
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
    </div>
</template>
