<script setup lang="ts">
import { useSearchStore } from '@/views/transactions/settle/useMerchandiseStore'
import { settlementFunctionCollect } from '@/views/transactions/settle/Settle'
import { selectFunctionCollect } from '@/views/selected'
import AddDeductBtn from '@/views/transactions/settle/AddDeductBtn.vue'
import ExtraMenu from '@/views/transactions/settle/ExtraMenu.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { useStore } from '@/views/services/pay-gateways/useStore'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'
import corp from '@corp'

const { store, head, exporter } = useSearchStore()
const { getSettleStyle, batchSettle, isSalesCol, movePartSettle } = settlementFunctionCollect(store)
const { selected, all_selected } = selectFunctionCollect(store)

provide('store', store)
provide('head', head)
provide('exporter', exporter)

store.params.level = 10 // taransaction model에서 필수
store.params.use_cancel_deposit = Number(corp.pv_options.paid.use_cancel_deposit)
store.params.use_collect_withdraw = Number(corp.pv_options.paid.use_collect_withdraw)

if(corp.pv_options.paid.use_settle_hold)
    store.params.use_settle_hold = 1
if (corp.pv_options.paid.use_realtime_deposit)
    store.params.use_realtime_deposit = 0
else
    store.params.use_realtime_deposit = -1

const { settle_types } = useStore()
const totals = ref(<any[]>([]))
const snackbar = <any>(inject('snackbar'))

const isExtendSettleCols = (parent_key: string, key: string) => {
    return parent_key === 'settle' && (key === 'cancel_deposit_amount' || key === 'collect_withdraw_amount' || key === 'collect_withdraw_fee')
}

onMounted(() => {
    watchEffect(async () => {
        if (store.getChartProcess() === false) {
            const r = await store.getChartData()
            totals.value = [r.data]
        }
    })
    snackbar.value.show('정산일은 검색 종료일(' + store.params.e_dt + ') 기준으로 진행됩니다.', 'success')
})
</script>
<template>
    <div>
        <BaseIndexView placeholder="가맹점 상호 검색" :metas="[]" :add="false" add_name="정산"
            :date_filter_type="DateFilters.SETTLE_RANGE">
            <template #filter>
                <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true"
                    :sales="true">
                    <template #pg_extra_field>
                        <VCol cols="12" sm="3">
                            <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.mcht_settle_type"
                                :items="[{ id: null, name: '전체' }].concat(settle_types)" label="정산타입 필터" item-title="name"
                                item-value="id"
                                @update:modelValue="store.updateQueryString({ mcht_settle_type: store.params.mcht_settle_type })" />
                        </VCol>
                    </template>
                </BaseIndexFilterCard>
            </template>
            <template #index_extra_field>
                <VBtn prepend-icon="tabler-calculator" @click="batchSettle(selected, true)" v-if="getUserLevel() >= 35"
                    size="small">
                    일괄 정산하기
                </VBtn>
                <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.use_realtime_deposit"
                    label="즉시출금 포함" color="primary"
                    @update:modelValue="[store.updateQueryString({ use_realtime_deposit: store.params.use_realtime_deposit })]"
                    v-if="corp.pv_options.paid.use_realtime_deposit" />
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
                        <template v-if="key == 'deduction.input'">
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'차감이 아닌 추가금 설정을 하시러면 금액 앞에 -(마이너스 기호)를 입력 후 차감버튼을 클릭해주세요.'">
                            </BaseQuestionTooltip>
                        </template>
                        <template v-else-if="key == 'id'">
                            <div class='check-label-container' v-if="key == 'id' && getUserLevel() >= 35">
                                <VCheckbox v-model="all_selected" class="check-label" />
                                <span>
                                    <BaseQuestionTooltip :location="'top'" :text="'선택/취소'"
                                        :content="'하단 가맹점 고유번호를 클릭하여 부분정산 페이지로 이동할 수 있습니다.'">
                                    </BaseQuestionTooltip>
                                </span>
                            </div>
                            <span v-else>
                                {{ header.ko }}
                            </span>
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
                <!-- chart -->
                <tr v-for="(item, key, index) in totals" :key="key">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <template v-if="head.getDepth(_header, 0) != 1">
                            <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible"
                                class='list-square'>
                                <span v-if="_key === 'deduction' && (__key as string) === 'input'">
                                </span>
                                <span v-else-if="_key === 'terminal' && (__key as string) === 'settle_pay_module_idxs'">
                                    {{ item[_key][__key] ? (item[_key][__key]).toLocaleString() : 0 }}
                                </span>
                                <span v-else-if="isExtendSettleCols(_key as string, __key as string)"
                                    style="color: red; font-weight: bold;">
                                    {{ item[_key][__key] ? (item[_key][__key] as number).toLocaleString() : 0 }}
                                </span>
                                <span v-else :style="getSettleStyle(_key as string)">
                                    {{ item[_key][__key] ? (item[_key][__key] as number).toLocaleString() : 0 }}
                                </span>
                            </td>
                        </template>
                        <template v-else>
                            <td v-show="_header.visible" class='list-square'>
                                <span v-if="isSalesCol(_key as string)" style="font-weight: bold;">
                                    {{ item[_key] ? (item[_key] as number).toLocaleString() : 0 }}
                                </span>
                                <span v-else>
                                    {{ item[_key] }}
                                </span>
                            </td>
                        </template>
                    </template>
                </tr>
                <!-- normal -->
                <tr v-for="(item, _key, _index) in store.getItems" :key="_key">
                    <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                        <template v-if="head.getDepth(_header, 0) != 1">
                            <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible"
                                class='list-square'>
                                <span v-if="_key == 'deduction' && (__key as string) == 'input'">
                                    <AddDeductBtn :id="item['id']" :name="item['mcht_name']" :is_mcht="true">
                                    </AddDeductBtn>
                                </span>
                                <span v-else-if="_key === 'terminal' && (__key as string) === 'settle_pay_module_idxs'">
                                    {{ item[_key][__key] ? (item[_key][__key].length).toLocaleString() : 0 }}
                                </span>
                                <span v-else-if="isExtendSettleCols(_key as string, __key as string)"
                                    style="color: red; font-weight: bold;">
                                    {{ item[_key][__key] ? (item[_key][__key] as number).toLocaleString() : 0 }}
                                </span>
                                <span v-else :style="getSettleStyle(_key as string)">
                                    {{ item[_key][__key] ? (item[_key][__key] as number).toLocaleString() : 0 }}
                                </span>
                            </td>
                        </template>
                        <template v-else>
                            <td v-show="_header.visible" class='list-square'>
                                <span v-if="_key === 'id'">
                                    <div class='check-label-container'>
                                        <VCheckbox v-model="selected" :value="item[_key]" class="check-label" />
                                        <span class="edit-link" @click="movePartSettle(item, true)"
                                            v-if="getUserLevel() >= 35">#{{ item[_key] }}</span>
                                        <span class="edit-link" v-else>#{{ item[_key] }}</span>
                                    </div>
                                </span>
                                <span v-else-if="_key == 'settle_hold_s_dt'">
                                    <VChip color="error" v-if="item[_key]">
                                        {{ item[_key]  }}
                                    </VChip>
                                    <span v-else>-</span>
                                </span>
                                <span v-else-if="_key == 'resident_num'">
                                    <span>
                                        <span>{{ item['resident_num_front'] }}</span>
                                        <span style="margin: 0 0.25em;">-</span>
                                        <span v-if="corp.pv_options.free.resident_num_masking">*******</span>
                                        <span v-else>{{ item['resident_num_back'] }}</span>
                                    </span>
                                </span>
                                <span v-else-if="isSalesCol(_key as string)" style="font-weight: bold;">
                                    {{ item[_key] ? (item[_key] as number).toLocaleString() : 0 }}
                                </span>
                                <span v-else-if="_key === 'extra_col'">
                                    <ExtraMenu :name="item['mcht_name']" :is_mcht="true" :item="item">
                                    </ExtraMenu>
                                </span>
                                <span v-else>
                                    {{ item[_key] }}
                                </span>
                            </td>
                        </template>
                    </template>
                </tr>
                <!-- part -->
            </template>
        </BaseIndexView>
    </div>
</template>
<style scoped>
  :deep(.sub-headers) {
    border-inline-end: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  }
</style>
