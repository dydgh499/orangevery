<script setup lang="ts">
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore'
import { useRequestStore } from '@/views/request'
import { selectFunctionCollect } from '@/views/selected'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { useSearchStore } from '@/views/transactions/settle/part/useSalesforceStore'
import { settlementFunctionCollect } from '@/views/transactions/settle/Settle'
import { getProfitColName } from '@/views/transactions/useStore'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'
import corp from '@corp'
import { cloneDeep } from 'lodash'

const route = useRoute()
const { store, head, exporter, metas } = useSearchStore()
const { selected, all_selected, dialog } = selectFunctionCollect(store)
const { get, post } = useRequestStore()
const { pgs, pss, settle_types, terminals, cus_filters } = useStore()
const { isSalesCol } = settlementFunctionCollect(store)

const user = ref(<any>({}))
const settle = ref({
    'total_amount': 0,
    'cxl_amount': 0,
    'cxl_count': 0,
    'appr_amount': 0,
    'appr_count': 0,
    'settle_amount': 0,
    'trx_amount': 0,
    'settle_fee': 0,
})

const snackbar = <any>(inject('snackbar'))

provide('store', store)
provide('head', head)
provide('exporter', exporter)

store.params.dev_use = corp.pv_options.auth.levels.dev_use
store.params.id = route.params.id
store.params.s_dt = route.query.s_dt
store.params.e_dt = route.query.e_dt
store.params.level = route.query.level
store.params.is_base_trx = 1

const partSettle = async () => {
    const count = selected.value.length
    const params = Object.assign(cloneDeep(store.params), settle.value)

    params.acct_name = user.value.acct_name
    params.acct_num = user.value.acct_num
    params.acct_bank_name = user.value.acct_bank_name
    params.acct_bank_code = user.value.acct_bank_code
    params.settle_transaction_idxs = selected.value
    params.deduct_amount = 0
    params.comm_settle_amount = 0
    params.under_sales_amount = 0
    params.cancel_deposit_amount = 0
    params.settle_pay_module_idxs = []
    params.cancel_deposit_idxs    = []

    if (await dialog('정말 '+count+'개의 매출을 부분정산하시겠습니까?')) {
        const r = await post('/api/v1/manager/transactions/settle-histories/salesforces', params)
        if (r.status == 201) {
            snackbar.value.show('성공하였습니다.', 'success')
            store.setChartProcess()
            store.setTable()
        }
        else
            snackbar.value.show(r.data.message, 'error')
    }
}

const getUser = async () => {
    const path = store.params.level == 10 ? 'merchandises' : 'salesforces'
    const res = await get('/api/v1/manager/'+path+'/'+store.params.id)
    user.value = res.data
}

onMounted(() => {
    getUser()
    watchEffect(async () => {
        if (store.getChartProcess() === false) {
            const r = await store.getChartData()
            metas[0]['stats'] = r.data.appr.amount.toLocaleString() + ' ￦'
            metas[1]['stats'] = r.data.cxl.amount.toLocaleString() + ' ￦'
            metas[2]['stats'] = r.data.amount.toLocaleString() + ' ￦'
            metas[3]['stats'] = r.data.profit.toLocaleString() + ' ￦'
            metas[0]['subtitle'] = r.data.appr.count.toLocaleString() + '건'
            metas[1]['subtitle'] = r.data.cxl.count.toLocaleString() + '건'
            metas[2]['subtitle'] = r.data.count.toLocaleString() + '건'
            metas[3]['subtitle'] = r.data.count.toLocaleString() + '건'
            metas[0]['percentage'] = r.data.appr.amount ? 100 : 0
            metas[1]['percentage'] = store.getPercentage(r.data.cxl.amount, r.data.appr.amount)
            metas[2]['percentage'] = store.getPercentage(r.data.amount, r.data.appr.amount)
            metas[3]['percentage'] = store.getPercentage(r.data.profit, r.data.appr.amount)
        }
    })    
    watchEffect(() => {
        const _settle = {
            'appr_amount'   : 0,
            'appr_count': 0,
            'cxl_amount'    : 0,
            'cxl_count': 0,
            'total_amount'  : 0,
            'settle_amount' : 0,
            'trx_amount'    : 0,
            'settle_fee'    : 0,
        }
        for (let i = 0; i < selected.value.length; i++) {
            const trans:any = store.getItems.find(item => item['id'] == selected.value[i])
            if(trans) {
                if(trans['is_cancel']) {
                    _settle.cxl_amount += trans['amount']
                    _settle.cxl_count++
                }
                else {
                    _settle.appr_amount += trans['amount']
                    _settle.appr_count++
                }

                _settle.total_amount += trans['amount']
                _settle.settle_amount += trans['profit']
                _settle.trx_amount += trans['trx_amount']
            }
        }
        settle.value = _settle
    })
    snackbar.value.show('정산일은 검색 종료일('+store.params.e_dt+') 기준으로 진행됩니다.', 'success')
})
</script>
<template>
    <div>
        <BaseIndexView placeholder="MID, TID, 승인번호, 거래번호 검색" :metas="metas" :add="false" add_name=""
            :date_filter_type="DateFilters.SETTLE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #pg_extra_field>
                        <VCol cols="6" sm="3" v-if="getUserLevel() >= 35">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.mcht_settle_type"
                                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산타입 필터" item-title="name"
                                item-value="id" @update:modelValue="[store.updateQueryString({mcht_settle_type: store.params.mcht_settle_type})]"/>
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="tabler-calculator" @click="partSettle()" size="small">
                    부분정산
                </VBtn>
                <div>
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.is_base_trx" label="매출일 기준 조회" color="primary" @update:modelValue="[store.updateQueryString({is_base_trx: store.params.is_base_trx})]"/>                
                    <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.only_cancel" label="취소 매출 조회"
                            color="error"
                            @update:modelValue="store.updateQueryString({ only_cancel: store.params.only_cancel })" />
                </div>
                <div style="display: flex;">
                    <table>
                        <tr>
                            <th>매출액 합계</th>
                            <td><span>{{ settle.total_amount.toLocaleString() }}</span> &#8361;</td>
                        </tr> 
                        <tr>
                            <th>승인액 합계</th>
                            <td><span>{{ settle.appr_amount.toLocaleString() }}</span> &#8361;</td>
                        </tr>            
                        <tr>
                            <th>취소액 합계</th>
                            <td><span>{{ settle.cxl_amount.toLocaleString() }}</span> &#8361;</td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <th>정산액 합계</th>
                            <td><span>{{ settle.settle_amount.toLocaleString() }}</span> &#8361;</td>
                        </tr>
                        <tr>
                            <th>거래 수수료</th>
                            <td><span>{{ settle.trx_amount.toLocaleString() }}</span> &#8361;</td>
                        </tr>
                        <tr>
                            <th><br></th>
                            <td><span></span></td>
                        </tr>
                    </table>
                </div>
            </template>
            <template #headers>
                <tr>
                    <template v-for="(sub_header, index) in head.getSubHeaderComputed" :key="index">
                        <th :colspan="head.getSubHeaderComputed.length - 1 == index ? sub_header.width + 1 : sub_header.width"
                            class='list-square sub-headers' v-show="sub_header.width">
                            <span>{{ sub_header.ko }}</span>
                        </th>
                    </template>
                </tr>
                <tr>
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                        <span v-if="key == 'total_trx_amount'">
                            <BaseQuestionTooltip :location="'top'" :text="store.params.level === 10 ? (header.ko as string) : '총 지급액'"
                                :content="'총 거래 수수료 = 금액 - (거래 수수료 + 유보금 + 이체 수수료)'"/>
                        </span>
                        <span v-else-if="key == 'mcht_settle_fee' && store.params.level == 10">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'입금 수수료는 가맹점만 적용됩니다.'"/>
                        </span>
                        <span v-else-if="key == 'trx_amount'">
                            <span>{{ store.params.level === 10 ? header.ko : '지급액' }}</span>
                        </span>
                        <span v-else-if="key == 'profit'">
                            <span>{{ getProfitColName(store.params.level) }}</span>
                        </span>
                        <span v-else-if="key == 'hold_amount' && store.params.level == 10">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'유보금은 가맹점만 적용됩니다.'"/>
                        </span>
                        <span v-else>
                            <div class='check-label-container' v-if="key == 'id' && getUserLevel() >= 35">
                                <VCheckbox v-model="all_selected" class="check-label" />
                                <span>선택/취소</span>
                            </div>
                            <span v-else>
                                {{ header.ko }}
                            </span>
                        </span>
                    </th>
                </tr>
            </template>
            <template #body>
                <tr v-for="(item, index) in store.getItems" :key="index">
                    <VTooltip activator="parent" location="end" open-delay="250">
                        <span>{{ `${item['mcht_name']} (${item['is_cancel'] ? "취소" : "승인"})` }}</span>
                        <br>
                        <span>{{ `${item['amount'].toLocaleString()}원` }}</span>
                    </VTooltip>
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <td v-show="_header.visible" :style="item['is_cancel'] ? 'color:red;' : ''" class='list-square'>
                            <span v-if="_key == 'id'">
                                <div class='check-label-container'>
                                    <VCheckbox v-model="selected" :value="item[_key]" class="check-label"/>
                                    <span>#{{ item[_key] }}</span>
                                </div>
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
