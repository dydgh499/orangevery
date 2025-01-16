<script setup lang="ts">
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { issuers } from '@/views/complaints/useStore'
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore'
import { useRequestStore } from '@/views/request'
import { selectFunctionCollect } from '@/views/selected'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { getDifferenceSettlemenMchtCode, getDifferenceSettlementResultCode, getDifferenceSettleMenual, mcht_settle_types, status_codes, useSearchStore } from '@/views/transactions/settle-histories/useDifferenceStore'
import { settlementFunctionCollect } from '@/views/transactions/settle/Settle'
import type { DifferentSettlementInfo } from '@/views/types'
import { axios, getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'
import corp from '@corp'

const { store, head, exporter, metas } = useSearchStore()
const { pgs, pss, settle_types, terminals, cus_filters } = useStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { isSalesCol } = settlementFunctionCollect(store)

const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))

const different_settle_infos = ref(<DifferentSettlementInfo[]>([]))
const { post } = useRequestStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

store.params.level = 10
store.params.issuer = '전체'
store.params.use_realtime_deposit = Number(corp.pv_options.paid.use_realtime_deposit)

const batchRetry = async() => {
    if(selected.value.length) {
        if (await alert.value.show("정말 일괄 재업로드 요청을 하시겠습니까?<br><b class='text-error'>성공 건은 재요청되지 않습니다.</b>")) {
            const r = await post(`/api/v1/manager/transactions/settle-histories/difference/retry`, {
                selected: selected.value
            })
            if (r.status == 201)
                snackbar.value.show('성공하였습니다.', 'success')
            else
                snackbar.value.show(r.data.message, 'error')            
        }
    }
    else
        snackbar.value.show('1개이상 선택해주세요.', 'error')
}

onMounted(async() => {
    watchEffect(async () => {
        if (store.getChartProcess() === false) {
            const r = await store.getChartData()            
            if(r.status === 200) {
                metas[0]['stats'] = r.data.amount.toLocaleString() + ' ￦'
                metas[1]['stats'] = r.data.vat_amount.toLocaleString() + ' ￦'
                metas[2]['stats'] = r.data.supply_amount.toLocaleString() + ' ￦'
                metas[3]['stats'] = r.data.settle_amount.toLocaleString() + ' ￦'
                metas[0]['percentage'] = r.data.amount ? 100 : 0
                metas[1]['percentage'] = store.getPercentage(r.data.vat_amount, r.data.amount)
                metas[2]['percentage'] = store.getPercentage(r.data.supply_amount, r.data.amount)
                metas[3]['percentage'] = store.getPercentage(r.data.settle_amount, r.data.amount)
            }
        }
    })
    const r = await axios.get('/api/v1/manager/services/brands/different-settlement-infos')
    different_settle_infos.value = r.data
})
</script>
<template>
    <div>
        <BaseIndexView placeholder="MID, TID, 승인번호, 거래번호, 주민번호, 사업자번호 검색" :metas="metas" :add="false" add_name=""
            :date_filter_type="DateFilters.DATE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #sales_extra_field>
                        <VCol cols="6" sm="3">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.status_code"
                                :items="status_codes" :label="`결과 필터`" item-title="title" item-value="id"
                                @update:modelValue="store.updateQueryString({ level: store.params.status_code })" />
                        </VCol>
                    </template>
                    <template #pg_extra_field>
                        <VCol cols="6" sm="3" v-if="getUserLevel() >= 35">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.mcht_settle_type"
                                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산타입 필터" item-title="name"
                                item-value="id"  @update:modelValue="[store.updateQueryString({mcht_settle_type: store.params.mcht_settle_type})]"/>
                        </VCol>
                        <VCol cols="6" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.issuer"
                                :items="['전체'].concat(issuers)" label="발급사 필터"
                                @update:modelValue="store.updateQueryString({ issuer: store.params.issuer })" />
                        </VCol>
                        <VCol cols="6" sm="3">
                            <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.module_type"
                                :items="[{ id: null, title: '전체' }].concat(module_types)" label="모듈타입 필터"
                                item-title="title" item-value="id"
                                @update:modelValue="[store.updateQueryString({ module_type: store.params.module_type })]" />
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="ic:outline-help" @click="alert.show(getDifferenceSettleMenual(different_settle_infos), 'v-dialog-lg')" size="small">
                    차액정산 메뉴얼
                </VBtn>
                <VBtn color="warning" prepend-icon="gridicons:reply" @click="batchRetry()" size="small" v-if="getUserLevel() >= 35">
                    재업로드 요청
                </VBtn>
                <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.only_appr"
                        label="승인 매출만 조회" color="success"
                        @update:modelValue="store.updateQueryString({ only_appr: store.params.only_appr })" />

            </template>
            <template #headers>
                <tr>
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                        <div class='check-label-container' v-if="key == 'id'">
                            <VCheckbox v-model="all_selected" class="check-label" />
                            <span>선택/취소</span>
                        </div>
                        <span v-else>
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
                                    <div
                                        class='check-label-container'>
                                        <VCheckbox v-model="selected" :value="item[_key]" class="check-label" />
                                        <span>#{{ item[_key] }}</span>
                                    </div>
                                </span>
                            <span v-else-if="_key === 'settle_result_msg'">
                                <VChip :color="getDifferenceSettlementResultCode(item['settle_result_code'])">
                                    {{ item[_key] }}
                                </VChip>                                
                            </span>
                            <span v-else-if="_key === 'mcht_section_code'">
                                <VChip :color="getDifferenceSettlemenMchtCode(item['mcht_section_code'])">
                                    {{ mcht_settle_types.find(obj => obj.id === item[_key])?.title }}
                                </VChip>                                
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
                            <b v-else-if="isSalesCol(_key as string)">
                                {{ Number(item[_key]).toLocaleString() }}
                            </b>
                            <span v-else-if="_key.toString().includes('_fee') && _key != 'mcht_settle_fee'">
                                <VChip v-if="item[_key]">
                                    {{ (item[_key] * 100).toFixed(3) }} %
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'custom_id'">
                                {{ cus_filters.find(cus => cus.id === item[_key])?.name }}
                            </span>
                            <span v-else-if="_key == 'updated_at'"
                                :class="item[_key] !== item['created_at'] ? 'text-primary' : ''">
                                {{ item[_key] }}
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
<style>
.different-settle-menual {
  block-size: auto !important;
}
  </style>
  