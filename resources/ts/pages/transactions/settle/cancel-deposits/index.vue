<script setup lang="ts">
import { installments } from '@/views/merchandises/pay-modules/useStore'
import { useSearchStore } from '@/views/transactions/settle/cancel-deposits/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
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

</script>
<template>
    <BaseIndexView placeholder="검색어" :metas="metas" :add="false" add_name="" :date_filter_type="DateFilters.SETTLE_RANGE">
        <template #index_extra_field>
            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})" />
        </template>
        <template #headers>
            <tr>
                <th v-for="(colspan, index) in head.getColspansComputed" :colspan="colspan" :key="index" class='list-square'
                    style="border-bottom: 0;">
                    <span>
                        {{ head.main_headers[index] }}
                    </span>
                </th>
            </tr>
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
                    <template v-if="head.getDepth(_header, 0) != 1">
                        <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible"
                            class='list-square'>
                        </td>
                    </template>
                    <template v-else>
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key == 'id'" class="edit-link">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="_key == 'deposit_amount'">
                                {{ item.deposit_amount.toLocaleString() }}
                            </span>
                            <span v-else-if="_key == 'total_trx_amount'">
                                {{ (item.amount - item.profit).toLocaleString() }}
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
                </template>
            </tr>
        </template>
    </BaseIndexView>
</template>
