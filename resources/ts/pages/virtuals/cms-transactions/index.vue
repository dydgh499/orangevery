
<script setup lang="ts">
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { getUserLevel, pay_token, user_info } from '@/plugins/axios'
import { realtimeMessage, realtimeResult, useSearchStore } from '@/views/virtuals/cms-transactions/useStore'
import { useStore } from '@/views/services/options/useStore'
import { DateFilters } from '@core/enums'

const { store, head, exporter, metas } = useSearchStore()
const { finance_vans } = useStore()
const total = ref(<any>{
    deposit_amount: 0,
    withdraw_amount: 0,
    total_realtime_withdraw_amount: 0,
    total_collect_withdraw_amount: 0,
    total_payment_agency_withdraw_amount: 0,
    total_withdraw_amount: 0,
    total_difference: 0,
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
            total.value.deposit_amount = Number(r.data.deposit_amount)
            total.value.withdraw_amount = Number(r.data.withdraw_amount)
            total.value.total_realtime_withdraw_amount = Number(r.data.total_realtime_withdraw_amount)
            total.value.total_collect_withdraw_amount = Number(r.data.total_collect_withdraw_amount)
            total.value.total_payment_agency_withdraw_amount = Number(r.data.total_payment_agency_withdraw_amount)
            total.value.total_withdraw_amount = total.value.withdraw_amount + total.value.total_realtime_withdraw_amount + total.value.total_collect_withdraw_amount + total.value.total_payment_agency_withdraw_amount
            total.value.total_difference = total.value.deposit_amount + total.value.total_withdraw_amount
        }
    })
    snackbar.value.show('거래모듈 및 입금정보는 2024-07-17부터 업데이트됩니다.', 'success')
})

</script>
<template>
    <section>
        <div>
            <BaseIndexView placeholder="계좌번호, 메모사항 검색" :metas="metas" :add="false" add_name="" :date_filter_type="DateFilters.SETTLE_RANGE">
                <template #filter>
                    <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="false"
                        :sales="false" :page="true">
                        <template #sales_extra_field>
                            <VCol cols="12" sm="4">
                                <table class="total-table">
                                    <tr>
                                        <th>입금액 합계</th>
                                        <td class="text-primary"><b>{{ total.deposit_amount.toLocaleString() }}</b> &#8361;</td>
                                    </tr>
                                    <tr>
                                        <th>출금액 합계</th>
                                        <td class="text-error"><b>{{ total.total_withdraw_amount.toLocaleString() }}</b> &#8361;</td>
                                    </tr>
                                    <tr>
                                        <th>입출금 차액</th>
                                        <td><b>{{ total.total_difference.toLocaleString() }}</b> &#8361;</td>
                                    </tr>
                                </table>
                            </VCol>
                            <VCol cols="12" sm="4">
                                <table class="total-table">
                                    <tr>
                                        <th>수기출금</th>
                                        <td class="text-error"><span>{{ total.withdraw_amount.toLocaleString() }}</span> &#8361;</td>
                                    </tr>
                                </table>
                            </VCol>
                            <VCol cols="12" sm="4">
                                <table class="total-table">
                                    <tr v-for="(finance_van, index) in finance_vans">
                                        <th>{{finance_van.nick_name}}</th>
                                        <td class="text-warning"><span>{{ finance_van.balance?.toLocaleString() }}</span> &#8361;</td>
                                    </tr>
                                </table>
                            </VCol>
                        </template>
                        <template #pg_extra_field>
                            <VCol cols="6" sm="3" v-if="getUserLevel() >= 35">
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.is_withdraw" :items="[{ id: null, title: '전체' }, {id: 0, title:'입금'}, {id: 1, title:'출금'}]"
                                    density="compact" variant="outlined" item-title="title" item-value="id" label="입출금 타입"
                                    @update:modelValue="store.updateQueryString({is_withdraw: store.params.is_withdraw})"
                                    :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''"
                                    />
                            </VCol>
                            <VCol cols="6" sm="3">
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.fin_id" :items="[{ id: null, nick_name: '전체' }].concat(finance_vans)"
                                    density="compact" variant="outlined" item-title="nick_name" item-value="id" label="거래모듈"
                                    @update:modelValue="store.updateQueryString({fin_id: store.params.fin_id})"
                                    :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''"
                                />
                            </VCol>
                        </template>
                    </BaseIndexFilterCard>
                </template>
                <template #index_extra_field>
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
                                    <b v-else-if="_key === 'amount'" :class="item['is_withdraw'] ? 'text-error' : 'text-primary'">
                                        {{ item[_key].toLocaleString() }}
                                    </b>
                                    <span v-else-if="_key === 'fin_id'">
                                        {{ (finance_vans.find(obj => obj.id == item[_key]))?.nick_name }}
                                    </span>
                                    <span v-else-if="_key === 'is_withdraw'">
                                        <VChip :color="item[_key] ? 'error' : 'success'">
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
        </div>
    </section>
</template>
<style scoped>
.total-table {
  inline-size: 100%;
}

.total-table > tr > th {
  padding-inline-end: 1em;
  text-align: start;
}

.total-table > tr > td {
  text-align: end;
}
</style>
