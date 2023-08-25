<script setup lang="ts">
import { module_types, installments } from '@/views/merchandises/pay-modules/useStore'
import { useSearchStore } from '@/views/transactions/settle/part/useMerchandiseStore'
import { useRequestStore } from '@/views/request'
import { useStore } from '@/views/services/pay-gateways/useStore'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { getUserLevel } from '@axios'
import { cloneDeep } from 'lodash'
import corp from '@corp'

const route = useRoute()
const { store, head, exporter } = useSearchStore()
const { get, post } = useRequestStore()
const { pgs, pss, settle_types, terminals, cus_filters } = useStore()

provide('store', store)
provide('head', head)
provide('exporter', exporter)
const alert = <any>(inject('alert'))
const snackbar = <any>(inject('snackbar'))

const mcht_settle_type = ref({ id: null, name: '전체' })
const selected = ref<number[]>([])
const all_selected = ref()
const user = ref(<any>({}))
const settle = ref({
    'total_amount': 0,
    'cxl_amount': 0,
    'appr_amount': 0,
    'settle_amount': 0,
    'trx_amount': 0,
    'settle_fee': 0,
    'deduct_amount': 0,
})
const metas = ref([
    {
        icon: 'ic-outline-payments',
        color: 'primary',
        title: '승인액 합계',
        stats: '0',
        percentage: 0,
        subtitle: '0건',
    },
    {
        icon: 'ic-outline-payments',
        color: 'error',
        title: '취소액 합계',
        stats: '0',
        percentage: 0,
        subtitle: '0건',
    },
    {
        icon: 'ic-outline-payments',
        color: 'success',
        title: '매출액 합계',
        stats: '0',
        percentage: 0,
        subtitle: '0건',
    },
    {
        icon: 'ic-outline-payments',
        color: 'warning',
        title: '정산액 합계',
        stats: '0',
        percentage: 0,
        subtitle: '0건',
    },
])

store.params.dev_use = corp.pv_options.auth.levels.dev_use
store.params.id = route.params.id
store.params.dt = route.params.dt
store.params.level = 10

const isSalesCol = (key: string) => {
    const sales_cols = ['amount', 'trx_amount', 'mcht_settle_fee', 'hold_amount', 'total_trx_amount', 'profit']
    for (let i = 0; i < sales_cols.length; i++) {
        if (sales_cols[i] === key)
            return true
    }
    return false
}
const partSettle = async () => {
    const count = selected.value.length
    const str_selected = selected.value.join(',')
    const params = Object.assign(cloneDeep(store.params), settle.value);
    const path = params.level == 10 ? 'merchandises' : 'salesforce'

    params.selected = selected.value
    params.acct_name = user.value.acct_name
    params.acct_num = user.value.acct_num
    params.acct_bank_name = user.value.acct_bank_name
    params.acct_bank_code = user.value.acct_bank_code
    if(count)
    {
        if (await alert.value.show('정말 '+count+'개의 매출을 부분정산하시겠습니까?<br><br>NO. ['+str_selected+']')) {
            const res = await post('/api/v1/manager/transactions/settle-histories/'+path+'/part', params)
            snackbar.value.show('성공하였습니다', 'success')
            store.setChartProcess()
            store.setTable()
        }
    }
    else
        snackbar.value.show('부분정산할 매출을 선택해주세요.', 'warning')
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
            metas.value[0]['stats'] = r.data.appr.amount.toLocaleString() + ' ￦'
            metas.value[1]['stats'] = r.data.cxl.amount.toLocaleString() + ' ￦'
            metas.value[2]['stats'] = r.data.amount.toLocaleString() + ' ￦'
            metas.value[3]['stats'] = r.data.profit.toLocaleString() + ' ￦'
            metas.value[0]['subtitle'] = r.data.appr.count.toLocaleString() + '건'
            metas.value[1]['subtitle'] = r.data.cxl.count.toLocaleString() + '건'
            metas.value[2]['subtitle'] = r.data.count.toLocaleString() + '건'
            metas.value[3]['subtitle'] = r.data.count.toLocaleString() + '건'
            metas.value[0]['percentage'] = r.data.appr.amount ? 100 : 0
            metas.value[1]['percentage'] = store.getPercentage(r.data.cxl.amount, r.data.appr.amount)
            metas.value[2]['percentage'] = store.getPercentage(r.data.amount, r.data.appr.amount)
            metas.value[3]['percentage'] = store.getPercentage(r.data.profit, r.data.appr.amount)
        }
    })
})
watchEffect(() => {
    store.setChartProcess()
    store.params.level = store.params.level
    store.params.mcht_settle_type = mcht_settle_type.value.id
})
watchEffect(() => {
    selected.value = all_selected.value ? store.getItems.map(item => item['id']) : []
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
            _settle.settle_fee += trans['trx_amount']
        }

    }
    settle.value = _settle
})
</script>
<template>
    <div>
        <BaseIndexView placeholder="MID, TID, 승인번호, 거래번호 검색" :metas="metas" :add="false" add_name=""
            :is_range_date="false">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #pg_extra_field>
                        <VCol cols="12" sm="3" v-if="getUserLevel() >= 35">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="mcht_settle_type"
                                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산타입 필터" item-title="name"
                                item-value="id" return-object />
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="tabler-calculator" @click="partSettle()">
                    부분정산
                </VBtn>
                <div style="display: flex;">
                    <table>
                        <tr>
                            <th>승인액 합계</th>
                            <td><span>{{ settle.appr_amount.toLocaleString() }}</span> &#8361;</td>
                        </tr>            
                        <tr>
                            <th>취소액 합계</th>
                            <td><span>{{ settle.cxl_amount.toLocaleString() }}</span> &#8361;</td>
                        </tr>
                        <tr>
                            <th>거래 수수료</th>
                            <td><span>{{ settle.trx_amount.toLocaleString() }}</span> &#8361;</td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <th>매출액 합계</th>
                            <td><span>{{ settle.total_amount.toLocaleString() }}</span> &#8361;</td>
                        </tr>            
                        <tr>
                            <th>정산액 합계</th>
                            <td><span>{{ settle.settle_amount.toLocaleString() }}</span> &#8361;</td>
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
                            <VCheckbox v-model="all_selected" label="선택/취소" class="check-label"/>
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
                                <VCheckbox v-model="selected" :value="item[_key]" :label="`#${item[_key]}`" class="check-label"/>
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
