<script setup lang="ts">
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { installments } from '@/views/merchandises/pay-modules/useStore'
import { useSearchStore } from '@/views/transactions/settle/cancel-deposits/useStore'
import { DateFilters } from '@core/enums'

const { 
    store, 
    head, 
    exporter, 
    metas
} = useSearchStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

const getDateFormat = (_settle_dt: number) => {
    const settle_dt = _settle_dt.toString()
    return settle_dt.substr(0, 4) + '-' + settle_dt.substr(4, 2) + '-' + settle_dt.substr(6, 2)
}

</script>
<template>
    <BaseIndexView placeholder="가맹점 상호 검색" :metas="metas" :add="false" add_name="" :date_filter_type="DateFilters.SETTLE_RANGE">
        <template #index_extra_field>
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                :items="[10, 20, 30, 50, 100, 200]" label="조회 개수" id="page-size-filter" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})" />
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
            <tr v-for="(item, index) in store.getItems" :key="index">
                <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                    <td v-show="_header.visible" class='list-square'>
                        <span v-if="_key == 'id'">
                            #{{ item[_key] }}
                        </span>
                        <span v-else-if="_key == 'deposit_amount'">
                            {{ item.deposit_amount.toLocaleString() }}
                        </span>
                        <span v-else-if="_key == 'total_trx_amount'">
                            {{ (item.amount - item.profit).toLocaleString() }}
                        </span>
                        <span v-else-if="_key == 'settle_dt'">
                            {{ getDateFormat(item[_key]) }}
                        </span>
                        <span v-else-if="_key == 'mcht_settle_id'">
                            <VChip :color="Number(item[_key]) === 0 ? 'default' : 'success'">
                                {{ Number(item[_key]) === 0 ? '정산안함' : "#"+item[_key]}}
                            </VChip>
                        </span>
                        <span v-else-if="_key == 'amount'" style="color: red !important;">
                            {{ (item.amount).toLocaleString() }}
                        </span>
                        <span v-else-if="_key == 'profit'">
                            {{ (item.profit).toLocaleString() }}
                        </span>
                        <span v-else-if="_key == 'cxl_dttm'" style="color: red !important;">
                            {{ item[_key] }}
                        </span>
                        <span v-else-if="_key == 'trx_dttm'" class="text-primary">
                            {{ item[_key] }}
                        </span>                            
                        <span v-else-if="_key == 'installment'">
                            {{ installments.find(inst => inst['id'] === item[_key])?.title }}
                        </span>
                        <span v-else>{{ item[_key] }}</span>
                    </td>
                </template>
            </tr>
        </template>
    </BaseIndexView>
</template>
