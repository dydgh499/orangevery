
<script setup lang="ts">
import { useRequestStore } from '@/views/request';
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { selectFunctionCollect } from '@/views/selected';
import { getUserLevel, pay_token, user_info } from '@/plugins/axios'
import { realtimeMessage, realtimeResult, useSearchStore, withdrawInterface } from '@/views/services/cms-transaction-books/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { DateFilters } from '@core/enums'

const alert = <any>(inject('alert'))
const { request, remove } = useRequestStore()
const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { finance_vans } = useStore()
const { cancelJobs } = withdrawInterface()
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


const batchRemove = async() => {
    if (await alert.value.show(`정말 ${selected.value.length}개의 출금예약을 삭제하시겠습니까?`)) {
        const r = await request({ url: `/api/v1/manager/bulk-withdraws/batch-updaters/remove`, method: 'delete', data: {
            selected_idxs: selected.value
        } }, true)
        selected.value = []
    }
    store.setTable()
}

const destory = async (id: number) => {
    remove(`/services/cms-transaction-books`, {
        id: id,
    }, false)
}

if(getUserLevel() < 35) {
    pay_token.value = ''
    user_info.value = {}
    location.href = '/'
}
</script>
<template>
    <section>
        <div>
            <BaseIndexView placeholder="계좌번호, 메모사항 검색" :metas="[]" :add="false" add_name="입금계좌" :date_filter_type="DateFilters.DATE_RANGE">
                <template #filter>
                    <BaseIndexFilterCard :pg="false" :ps="false" :settle_type="false" :terminal="false" :cus_filter="false"
                        :sales="false">
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
                <VBtn type="button" color="error" @click="batchRemove()" style="float: inline-end;" size="small"
                    :style="$vuetify.display.smAndDown ? 'margin: 0.5em;' : ''" item-title="title" item-value="id">
                    일괄삭제
                    <VIcon size="18" icon="tabler-trash" />
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
                                        <VCheckbox v-if="item['withdraw_status'] !== 1" v-model="selected" :value="item[_key]" class="check-label" />
                                        <span>#{{ item[_key] }}</span>
                                    </div>
                                    </span>
                                    <span v-else-if="_key === 'result_code'">
                                        <VChip :color="store.getSelectIdColor(realtimeResult(item[_key]))">
                                            {{ realtimeMessage(item) }}
                                        </VChip>
                                    </span>
                                    <span v-else-if="_key === 'fin_id'">
                                        {{ (finance_vans.find(obj => obj.id == item[_key]))?.nick_name }}
                                    </span>
                                    <span v-else-if="_key === 'is_withdraw'">
                                        <VChip :color="item[_key] ? 'error' : 'success'">
                                            {{ item[_key] ? '출금' : '입금' }}
                                        </VChip>
                                    </span>
                                    <b v-else-if="_key === 'amount'" :class="item['is_withdraw'] ? 'text-error' : 'text-primary'">
                                        {{  item['is_withdraw'] ? '-' + item[_key].toLocaleString() : item[_key].toLocaleString() }}
                                    </b>
                                    <span v-else-if="_key === 'withdraw_status'">
                                        <VChip :color="store.booleanTypeColor(!item[_key])" >
                                            {{ item[_key] ? '이체완료' : '이체예약' }}
                                        </VChip>
                                    </span>
                                    <span v-else-if="_key === 'note'" v-html="item[_key]" style="line-height: 2em;"></span>
                                    
                                    <span v-else-if="_key === 'extra_col'" v-if="item['withdraw_status'] != 1">
                                        <VBtn size="small" type="button" color="error" @click="cancelJobs([item['id']])">
                                            삭제
                                            <VIcon size="22" icon="tabler-trash"/>
                                        </VBtn>
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
