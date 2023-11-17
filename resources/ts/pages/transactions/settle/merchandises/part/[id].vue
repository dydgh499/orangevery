<script setup lang="ts">
import { module_types, installments } from '@/views/merchandises/pay-modules/useStore'
import { useSearchStore } from '@/views/transactions/settle/part/useMerchandiseStore'
import { useRequestStore } from '@/views/request'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { selectFunctionCollect } from '@/views/selected'
import { settlementFunctionCollect } from '@/views/transactions/settle/Settle'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { getUserLevel } from '@axios'
import { cloneDeep } from 'lodash'
import { DateFilters } from '@core/enums'
import corp from '@corp'

const route = useRoute()
const { store, head, exporter, metas } = useSearchStore()
const { selected, all_selected, dialog } = selectFunctionCollect(store)
const { isAbleMchtDepositCollect } = settlementFunctionCollect(store)

const { get, post } = useRequestStore()
const { pgs, pss, settle_types, terminals, cus_filters } = useStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)
const snackbar = <any>(inject('snackbar'))

const user = ref(<any>({}))
const settle = ref({
    'total_amount': 0,
    'cxl_amount': 0,
    'appr_amount': 0,
    'settle_amount': 0,
    'trx_amount': 0,
    'settle_fee': 0,
    'deduct_amount': 0,
    'comm_settle_amount':0,
    'under_sales_amount':0,
})


store.params.dev_use = corp.pv_options.auth.levels.dev_use
store.params.id = route.params.id
store.params.s_dt = route.query.s_dt
store.params.e_dt = route.query.e_dt
store.params.level = 10

const isSalesCol = (key: string) => {
    const sales_cols = ['amount', 'trx_amount', 'mcht_settle_fee', 'hold_amount', 'total_trx_amount', 'profit']
    for (let i = 0; i < sales_cols.length; i++) {
        if (sales_cols[i] === key)
            return true
    }
    return false
}
const getPartSettleFormat = () => {
    const params = Object.assign(cloneDeep(store.params), settle.value)
    params.selected = selected.value
    params.acct_name = user.value.acct_name
    params.acct_num = user.value.acct_num
    params.acct_bank_name = user.value.acct_bank_name
    params.acct_bank_code = user.value.acct_bank_code
    return params
}

const partSettle = async () => {
    const count = selected.value.length
    if(await dialog('정말 '+count+'개의 매출을 부분정산하시겠습니까?')) {
        const r = await post('/api/v1/manager/transactions/settle-histories/merchandises/part', getPartSettleFormat(), true)
        store.setChartProcess()
        store.setTable()
    }
}

const settleCollect = async () => {
    if(await dialog('정말 정산금을 이체한 후 정산 하시겠습니까?')) {
        const r = await post('/api/v1/manager/transactions/settle-histories/merchandises/settle-collect', getPartSettleFormat(), true)
        if(r.data.result_cd == "0000") {
            store.setChartProcess()
            store.setTable()    
        }
        else
            snackbar.value.show(r.data.result_msg, 'error')
    }
}
const getUser = async () => {
    const path = store.params.level == 10 ? 'merchandises' : 'salesforce'
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
    snackbar.value.show('정산일은 검색 종료일('+store.params.e_dt+') 기준으로 진행됩니다.', 'success')
})
watchEffect(() => {
    const _settle = {
        'appr_amount'   : 0,
        'cxl_amount'    : 0,
        'total_amount'  : 0,
        'settle_amount' : 0,
        'trx_amount'    : 0,
        'settle_fee'    : 0,
        'deduct_amount' : 0,
        'comm_settle_amount':0,
        'under_sales_amount':0,
    }
    for (let i = 0; i < selected.value.length; i++) {
        const trans:any = store.getItems.find(item => item['id'] == selected.value[i])
        if(trans) {
            if(trans['is_cancel'])
                _settle.cxl_amount += trans['amount']
            else
                _settle.appr_amount += trans['amount']

            _settle.total_amount += trans['amount']
            _settle.settle_amount += trans['profit']
            _settle.trx_amount += trans['trx_amount']
            _settle.settle_fee += trans['mcht_settle_fee']
        }

    }
    settle.value = _settle
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
                        <VCol cols="12" sm="3" v-if="getUserLevel() >= 35">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.mcht_settle_type"
                                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산타입 필터" item-title="name"
                                item-value="id" @update:modelValue="store.updateQueryString({mcht_settle_type: store.params.mcht_settle_type})" />
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="tabler-calculator" @click="partSettle()" v-if="getUserLevel() >= 35" size="small">
                    부분정산
                </VBtn>
                <!--
                <VBtn prepend-icon="fa6-solid:money-bill-transfer" @click="settleCollect()" v-if="isAbleMchtDepositCollect(parseInt(route.query.use_collect_withdraw as string))" size="small">
                    정산 및 이체
                </VBtn>
                -->
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
                            <th>입금 수수료</th>
                            <td><span>{{ settle.settle_fee.toLocaleString() }}</span> &#8361;</td>
                        </tr>
                    </table>
                </div>
            </template>
            <template #headers>
                <tr>
                    <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
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
                        <template v-else-if="key == 'id'">
                            <div class='check-label-container'>
                                <VCheckbox v-model="all_selected" class="check-label"/>
                                <span>선택/취소</span>
                            </div>
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
                <tr v-for="(item, index) in store.getItems" :key="index">
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
                            <span v-else-if="_key == 'pay_cond_price'">
                                {{ item['settle_fee'] }}
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
