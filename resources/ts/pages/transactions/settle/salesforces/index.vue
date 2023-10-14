<script setup lang="ts">
import { useSearchStore } from '@/views/transactions/settle/useSalesforceStore'
import { SettlementFunctionCollect } from '@/views/transactions/settle/Settle'
import { selectFunctionCollect } from '@/views/selected'
import AddDeductBtn from '@/views/transactions/settle/AddDeductBtn.vue'
import ExtraMenu from '@/views/transactions/settle/ExtraMenu.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { salesLevels, settleCycles, settleDays, settleTaxTypes } from '@/views/salesforces/useStore'
import { useStore } from '@/views/services/pay-gateways/useStore'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { getUserLevel } from '@axios'
import { DateFilters } from '@core/enums'

const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { getSettleStyle, batchSettle, isSalesCol, movePartSettle } = SettlementFunctionCollect(store)
const { settle_types } = useStore()
const all_sales = salesLevels()
const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()

const totals = ref(<any[]>([]))
const snackbar = <any>(inject('snackbar'))

provide('store', store)
provide('head', head)
provide('exporter', exporter)

store.params.level = all_sales[0].id
store.params.is_base_trx = true

onMounted(() => {
    watchEffect(async () => {
        if (store.getChartProcess() === false) {
            const r = await store.getChartData()
            totals.value = []
            totals.value.push(r.data)
        }
    })
    snackbar.value.show('정산일은 검색 종료일('+store.params.e_dt+') 기준으로 진행됩니다.', 'success')
})

watchEffect(() => {
    store.setChartProcess()
    store.params.level = store.params.level
    store.params.settle_cycle = store.params.settle_cycle
    store.params.mcht_settle_type = store.params.mcht_settle_type
    store.params.is_base_trx = store.params.is_base_trx
})
</script>
<template>
    <BaseIndexView placeholder="영업점 상호 검색" :metas="[]" :add="false" add_name="정산" :date_filter_type="DateFilters.SETTLE_RANGE">
        <template #index_extra_field>
            <VBtn prepend-icon="tabler-calculator" @click="batchSettle(selected, false)" v-if="getUserLevel() >= 35">
                일괄 정산하기
            </VBtn>
            <div class="demo-space-x">
                <VSwitch v-model="store.params.is_base_trx" label="매출일 기준 조회" color="primary" />
            </div>
        </template>
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true"
                :sales="true">
                <template #sales_extra_field>
                    <VCol cols="12" sm="3">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.level" :items="salesLevels()"
                            :label="`조회등급`" item-title="title" item-value="id" create />
                    </VCol>
                    <VCol cols="12" sm="3">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.settle_cycle"
                            :items="[{ id: null, title: '전체' }].concat(settleCycles())" :label="`영업점 정산주기 필터`"
                            item-title="title" item-value="id" create />
                    </VCol>
                    <VCol cols="12" sm="3">
                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.mcht_settle_type"
                            :items="[{ id: null, name: '전체' }].concat(settle_types)" label="가맹점 정산타입 필터" item-title="name"
                            item-value="id"/>
                    </VCol>
                </template>
            </BaseIndexFilterCard>
        </template>
        <template #headers>
            <tr>
                <th v-for="(colspan, index) in head.getColspansComputed" :colspan="colspan" :key="index"
                    class='list-square' style="border-bottom: 0;">
                    <span>
                        {{ head.main_headers[index] }}
                    </span>
                </th>
            </tr>
            <tr>
                <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                    <template v-if="key == 'deduction.input'">
                        <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                            :content="'차감이 아닌 추가금 설정을 하시러면 금액 앞에 -(마이너스 기호)를 입력 후 차감버튼을 클릭해주세요.'">
                        </BaseQuestionTooltip>
                    </template>
                    <template v-else-if="key == 'id'">
                        <div style="display: inline-flex; align-items: center; vertical-align: middle;">
                            <VCheckbox v-model="all_selected" class="check-label" v-if="getUserLevel() >= 35" style="min-width: 1em;"/>
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'하단 영업점 고유번호를 클릭하여 부분정산 페이지로 이동할 수 있습니다.'">
                            </BaseQuestionTooltip>
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
            <!-- chart -->
            <tr v-for="(item, index) in totals" :key="index">
                <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                    <template v-if="head.getDepth(_header, 0) != 1">
                        <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible"
                            class='list-square'>
                            <span v-if="_key == 'deduction' && (__key as string) == 'input'">
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
            <tr v-for="(item, index) in store.getItems" :key="index">
                <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                    <template v-if="head.getDepth(_header, 0) != 1">
                        <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible"
                            class='list-square'>
                            <span v-if="_key == 'deduction' && (__key as string) == 'input'">
                                <AddDeductBtn :id="item['id']" :name="item['user_name']" :is_mcht="false">
                                </AddDeductBtn>
                            </span>
                            <span v-else :style="getSettleStyle(_key as string)">
                                {{ item[_key][__key] ? (item[_key][__key] as number).toLocaleString() : 0 }}
                            </span>
                        </td>
                    </template>
                    <template v-else>
                        <td v-show="_header.visible" class='list-square'>
                            <span v-if="_key === 'id'">
                                <div style="display: inline-flex; align-items: center; vertical-align: middle;">
                                    <VCheckbox v-model="selected" :value="item[_key]" class="check-label" style="min-inline-size: 1em;" v-if="getUserLevel() >= 35"/>
                                    <span class="edit-link" @click="movePartSettle(item['id'], false)">#{{ item[_key] }}</span>
                                </div>
                            </span>
                            <span v-else-if="_key == 'level'">
                                <VChip :color="store.getSelectIdColor(all_sales.find(obj => obj.id === item[_key])?.id)">
                                    {{ all_sales.find(sales => sales.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'settle_cycle'">
                                <VChip :color="store.getSelectIdColor(all_cycles.find(obj => obj.id === item[_key])?.id)">
                                    {{ all_cycles.find(sales => sales.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'settle_day'">
                                {{ all_days.find(sales => sales.id === item[_key])?.title }}
                            </span>
                            <span v-else-if="_key == 'settle_tax_type'">
                                <VChip :color="store.getSelectIdColor(tax_types.find(obj => obj.id === item[_key])?.id)">
                                    {{ tax_types.find(sales => sales.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="isSalesCol(_key as string)" style="font-weight: bold;">
                                {{ item[_key] ? (item[_key] as number).toLocaleString() : 0 }}
                            </span>
                            <span v-else-if="_key === 'extra_col'">
                                <ExtraMenu :name="item['user_name']" :is_mcht="false" :item="item">
                                </ExtraMenu>
                            </span>
                            <span v-else>
                                {{ item[_key] }}
                            </span>
                        </td>
                    </template>
                </template>
            </tr>
        </template>
    </BaseIndexView>
</template>
