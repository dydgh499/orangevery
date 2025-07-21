
<script setup lang="ts">
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { selectFunctionCollect } from '@/views/selected';
import { getUserLevel, pay_token, user_info } from '@/plugins/axios'
import { useSearchStore } from '@/views/virtuals/cms-transactions/useStore'
import { realtimeMessage, realtimeResult, withdraw_status } from '@/views/virtuals/cms-transactions/useStore'
import { useStore } from '@/views/services/options/useStore'
import { useRequestStore } from '@/views/request';
import { DateFilters } from '@core/enums'
import WithdrawHistoriesDialog from '@/layouts/dialogs/cms-transactions/WithdrawHistoriesDialog.vue';
import PVWithdrawErrorCodeDialog from '@/layouts/dialogs/cms-transactions/PVWithdrawErrorCodeDialog.vue'
import ExtraMenu from '@/views/virtuals/cms-transactions/ExtraMenu.vue';

const alert = <any>(inject('alert'))
const { post } = useRequestStore()
const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { finance_vans } = useStore()
const pvErrorCodeDialog = ref()
const total = ref(<any>{
    withdraw_amount: 0,
    total_withdraw_count: 0,
})
const withdrawHistoriesDialog = ref()

provide('store', store)
provide('head', head)
provide('exporter', exporter)
provide('withdrawHistoriesDialog', withdrawHistoriesDialog)
const snackbar = <any>(inject('snackbar'))

const batchRemove = async() => {
    if (await alert.value.show(`정말 ${selected.value.length}개의 예약이체를 취소하시겠습니까?`)) {
        const r = await post(`/api/v1/manager/virtuals/cms-transactions/cancel-job`, {
            ids: selected.value
        }, true)
        if (r.status === 201) {
            selected.value = []
            store.setTable()
        }
    }
}

if(getUserLevel() < 35) {
    pay_token.value = ''
    user_info.value = {}
    location.href = '/'
}

onMounted(() => {
    watchEffect(async () => {
        if (store.getChartProcess() === false) {
            const r = await store.getChartData()
            total.value.total_withdraw_count = Number(r.data.total_withdraw_count)
            total.value.withdraw_amount = Number(r.data.withdraw_amount)
        }
    })
    snackbar.value.show('거래모듈 및 입금정보는 2024-07-17부터 업데이트됩니다.', 'success')
})

</script>
<template>
    <section>
        <div>
            <BaseIndexView placeholder="계좌번호 검색" :metas="[]" :add="false" add_name="" :date_filter_type="DateFilters.SETTLE_RANGE">
                <template #filter>
                    <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="false"
                        :sales="false" :page="true">
                        <template #sales_extra_field>
                            <VCol cols="12" sm="4">
                                <table class="total-table">
                                    <tr>
                                        <th>출금 건수</th>
                                        <td class="text-error"><b>{{ total.total_withdraw_count.toLocaleString() }}</b> 건</td>
                                    </tr>
                                </table>
                            </VCol>
                            <VCol cols="12" sm="4">
                                <table class="total-table">
                                    <tr>
                                        <th>출금액 합계</th>
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
                            <VCol cols="6" sm="3">
                                <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.withdraw_status" :items="[{ id: null, title: '전체' }, {id: 0, title:'대기'}, {id: 1, title:'완료'}, {id: 2, title:'실패'}]"
                                    density="compact" variant="outlined" item-title="title" item-value="id" label="이체 상태"
                                    @update:modelValue="store.updateQueryString({withdraw_status: store.params.withdraw_status})"
                                    :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''"
                                    />
                            </VCol>
                        </template>
                    </BaseIndexFilterCard>
                </template>
                <template #index_extra_field>
                    <VBtn v-if="store.params.withdraw_status === 0" type="button" color="error" @click="batchRemove()" style="float: inline-end;" size="small"
                    :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''" item-title="title" item-value="id">
                        일괄삭제
                        <VIcon size="18" icon="tabler-trash" />
                    </VBtn>
                    <VBtn prepend-icon="line-md:emoji-frown-twotone" @click="pvErrorCodeDialog.show()" color="error" size="small"
                        :style="$vuetify.display.smAndDown ? 'margin: 0.25em;' : ''">
                        출금 에러코드 정의
                    </VBtn>
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
                    <template v-for="(item, index) in store.getItems" :key="index">
                        <tr>
                            <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                                <td v-show="_header.visible" :class="_key == 'title' ? 'list-square title' : 'list-square'">
                                    <span v-if="_key == 'id'">
                                        <div
                                            class='check-label-container'>
                                            <VCheckbox v-model="selected" :value="item[_key]" class="check-label" />
                                            <span>#{{ item[_key] }}</span>
                                        </div>
                                    </span>
                                    <span v-else-if="_key === 'result_code'">
                                        <VChip :color="store.getSelectIdColor(realtimeResult(item[_key]))">
                                            {{ realtimeMessage(item) }}
                                        </VChip>
                                    </span>
                                    <b v-else-if="_key === 'amount'" :class="item['is_withdraw'] ? 'text-error' : 'text-primary'">
                                        {{ item[_key].toLocaleString() }}
                                    </b>
                                    <span v-else-if="_key === 'fin_id'">
                                        {{ (finance_vans.find(obj => obj.id == item[_key]))?.nick_name }}
                                    </span>
                                    <span v-else-if="_key === 'withdraw_status'">
                                        <VChip :color="store.getSelectIdColor(item[_key])" >
                                            {{ withdraw_status.find(obj => obj.id === item['withdraw_status'])?.title }}
                                        </VChip>
                                    </span>
                                    <span v-else-if="_key === 'extra_cols'">
                                        <ExtraMenu :item="item"/>
                                    </span>
                                    <span v-else>
                                        {{ item[_key] }}
                                    </span>
                                </td>
                            </template>
                        </tr>
                    </template>
                </template>
            </BaseIndexView>
            <WithdrawHistoriesDialog ref="withdrawHistoriesDialog" />
        <PVWithdrawErrorCodeDialog ref="pvErrorCodeDialog"/>   
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
