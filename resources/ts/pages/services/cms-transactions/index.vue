
<script setup lang="ts">
import HeadOfficeWithdrawDialog from '@/layouts/dialogs/services/HeadOfficeWithdrawDialog.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { getUserLevel, pay_token, user_info } from '@/plugins/axios'
import { useSearchStore } from '@/views/services/cms-transactions/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { realtimeMessage, realtimeResult } from '@/views/transactions/settle-histories/useCollectWithdrawHistoryStore'
import { DateFilters } from '@core/enums'

const { store, head, exporter, metas } = useSearchStore()
const { finance_vans } = useStore()
const headOfficeWithdrawDialog = ref()
const total_amount = ref(<any>{
    deposit_amount: 0,
    withdraw_amount: 0,
})
provide('store', store)
provide('head', head)
provide('exporter', exporter)
const snackbar = <any>(inject('snackbar'))

if(getUserLevel() < 35) {
    pay_token.value = ''
    user_info.value = {}
    location.href = '/'
}

onMounted(() => {
    watchEffect(async () => {
        if (store.getChartProcess() === false) {
            const r = await store.getChartData()
            total_amount.value.deposit_amount = Number(r.data.deposit_amount)
            total_amount.value.withdraw_amount = Number(r.data.withdraw_amount)

            metas[0]['stats'] = Number(r.data.deposit_amount).toLocaleString() + ' ￦'
            metas[1]['stats'] = Number(r.data.withdraw_amount).toLocaleString() + ' ￦'
            metas[2]['stats'] = (Number(r.data.deposit_amount) + Number(r.data.withdraw_amount)).toLocaleString() + ' ￦'
            metas[0]['subtitle'] = Number(r.data.total_deposit_count).toLocaleString() + '건'
            metas[1]['subtitle'] = Number(r.data.total_withdraw_count).toLocaleString() + '건'
            metas[2]['subtitle'] = (Number(r.data.total_deposit_count) + Number(r.data.total_withdraw_count)).toLocaleString() + '건'
        }
    })
    watchEffect(() => {
        let finance_vans_balances = ''
        finance_vans.forEach(finance_van => {
            finance_vans_balances += `${finance_van.nick_name}: ${finance_van.balance ? finance_van.balance.toLocaleString() : 0 } ￦<br>`
        })
        metas[3]['stats'] = finance_vans_balances
    })
    snackbar.value.show('거래모듈 및 입금정보는 2024-07-17부터 업데이트됩니다.', 'success')
})

</script>
<template>
    <section>
        <div>
            <BaseIndexView placeholder="계좌번호, 메모사항 검색" :metas="metas" :add="false" add_name="입금계좌" :date_filter_type="DateFilters.SETTLE_RANGE">
                <template #filter>
                </template>
                <template #index_extra_field>
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.is_withdraw" :items="[{ id: null, title: '전체' }, {id: 0, title:'입금'}, {id: 1, title:'출금'}]"
                        density="compact" variant="outlined" item-title="title" item-value="id" label="입출금 타입"
                            @update:modelValue="store.updateQueryString({is_withdraw: store.params.is_withdraw})"
                    />
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.fin_id" :items="[{ id: null, nick_name: '전체' }].concat(finance_vans)"
                        density="compact" variant="outlined" item-title="nick_name" item-value="id" label="거래모듈"
                            @update:modelValue="store.updateQueryString({fin_id: store.params.fin_id})"
                    />
                    <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.page_size" density="compact" variant="outlined"
                        :items="[10, 20, 30, 50, 100, 200]" label="표시 개수" id="page-size-filter" eager  @update:modelValue="store.updateQueryString({page_size: store.params.page_size})"/>

                    <VBtn prepend-icon="carbon:batch-job" @click="headOfficeWithdrawDialog.show()" v-if="getUserLevel() >= 35" color="primary" size="small">
                        지정계좌 이체
                    </VBtn>
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
                            <span>
                                {{ header.ko }}
                            </span>
                        </th>
                    </tr>
                </template>
                <template #body>
                    <template v-for="(item, index) in store.getItems" :key="index">
                        <tr>
                            <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                                <td v-show="_header.visible" :class="_key == 'title' ? 'list-square title' : 'list-square'">
                                    <span v-if="_key == 'id'">
                                        #{{ item[_key] }}
                                    </span>
                                    <b v-else-if="_key === 'amount'" class="text-primary">
                                        {{ item[_key].toLocaleString() }}
                                    </b>
                                    <span v-else-if="_key === 'fin_id'">
                                        {{ (finance_vans.find(obj => obj.id == item[_key]))?.nick_name }}
                                    </span>
                                    <span v-else-if="_key === 'is_withdraw'">
                                        <VChip :color="item[_key] ? 'primary' : 'success'">
                                            {{ item[_key] ? '출금' : '입금' }}
                                        </VChip>
                                    </span>
                                    <span v-else-if="_key === 'result_code'">
                                        <VChip :color="store.getSelectIdColor(realtimeResult(item[_key]))">
                                            {{ realtimeMessage(item) }}
                                        </VChip>
                                    </span>
                                    <span v-else-if="_key === 'note'" v-html="item[_key]" style="line-height: 2em;"></span>
                                    <span v-else>
                                        {{ item[_key] }}
                                    </span>
                                </td>
                            </template>
                        </tr>
                    </template>
                </template>
            </BaseIndexView>
            <HeadOfficeWithdrawDialog ref="headOfficeWithdrawDialog"/>
        </div>
    </section>
</template>
