<script setup lang="ts">
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { issuers } from '@/views/complaints/useStore'
import { module_types } from '@/views/merchandises/pay-modules/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useSearchStore } from '@/views/transactions/summary/useStore'

import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import type { Options } from '@/views/types'
import { getUserLevel, salesLevels } from '@axios'
import { DateFilters } from '@core/enums'
import corp from '@corp'

const { store, head, exporter, metas, mchtGroup, dataToChart } = useSearchStore()
const { settle_types } = useStore()


provide('store', store)
provide('head', head)
provide('exporter', exporter)

store.params.level = 10
store.params.issuer = '전체'
// 실시간이체 사용여부
if (Number(corp.pv_options.paid.use_realtime_deposit))
    store.params.use_realtime_deposit = 1

const getLevels = () => {
    const sales = salesLevels()
    if (getUserLevel() >= 10)
        sales.unshift(<Options>({ id: 10, title: '가맹점' }))
    return sales
}

onMounted(() => {
    watchEffect(async () => {
        await dataToChart()
    })
})
</script>
<template>
    <div>
        <BaseIndexView placeholder="상호, MID, TID, 승인번호, 거래번호, 결제모듈 별칭, 주민번호, 사업자번호, 발급사, 매입사, 휴대폰 번호 검색" :metas="metas"
            :add="false" add_name="매출" :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #sales_extra_field>
                        <VCol cols="6" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.level" :items="getLevels()"
                                :label="`등급 선택`" item-title="title" item-value="id"
                                @update:modelValue="store.updateQueryString({ level: store.params.level })" />
                        </VCol>
                    </template>
                    <template #pg_extra_field>
                        <VCol cols="6" sm="3" v-if="getUserLevel() >= 35">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.mcht_settle_type"
                                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산타입 필터" item-title="name"
                                item-value="id"
                                @update:modelValue="store.updateQueryString({ mcht_settle_type: store.params.mcht_settle_type })" />                                
                        </VCol>
                        <VCol cols="6" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.issuer"
                                :items="['전체'].concat(issuers)" label="발급사 필터"
                                @update:modelValue="store.updateQueryString({ issuer: store.params.issuer })" />
                        </VCol>
                        <VCol cols="6" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.module_type"
                                    :items="[{ id: null, title: '전체' }].concat(module_types)" label="모듈타입 필터" item-title="title"
                                    item-value="id"
                                    @update:modelValue="[store.updateQueryString({ module_type: store.params.module_type })]" />
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="tabler-calculator" @click="mchtGroup()" size="small"
                    :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''">
                    가맹점 매출집계
                </VBtn>
                <div>
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.only_cancel" label="취소 매출 조회" color="error"
                        @update:modelValue="store.updateQueryString({ only_cancel: store.params.only_cancel })" />
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.no_settlement" label="미정산 매출 조회" color="warning"
                        @update:modelValue="store.updateQueryString({ no_settlement: store.params.no_settlement })" />
                </div>
            </template>
            <template #headers>
                <tr>
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                        <template v-if="key == 'total_trx_amount'">
                            <span>{{ store.params.level === 10 ? header.ko : '총 지급액' }}</span>
                        </template>
                        <template v-else-if="key == 'total_profit'">
                            <span>{{ store.params.level === 10 ? header.ko : '총 수익금' }}</span>
                        </template>
                        <template v-else>
                            <span>{{ header.ko }}</span>
                        </template>
                    </th>
                </tr>
            </template>
            <template #body>
                <tr v-for="(item, index) in store.getItems" :key="item['id']">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_key">
                        <td v-if="_header.visible" class='list-square'>                            
                            <span v-if="_key === 'total_count'">
                                {{ (Number(item['total_appr_count']) +  Number(item['total_cxl_count'])).toLocaleString() }}
                            </span>
                            <span v-else-if="_key === 'total_amount'">
                                {{ (Number(item['total_appr_amount']) +  Number(item['total_cxl_amount'])).toLocaleString() }}
                            </span>
                            <span v-else-if="_key === 'total_trx_amount'">
                                {{ ((Number(item['total_appr_amount']) +  Number(item['total_cxl_amount'])) - Number(item['total_profit'])).toLocaleString() }}
                            </span>
                            <span v-else-if="_key !== 'user_name'">
                                {{ Number(item[_key]).toLocaleString() }}
                            </span>
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </tr>
            </template>
        </BaseIndexView>
</div></template>
