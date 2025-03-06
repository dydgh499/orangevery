<script setup lang="ts">
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import { settleCycles, settleDays, settleTaxTypes } from '@/views/salesforces/useStore'
import { selectFunctionCollect } from '@/views/selected'
import { useStore } from '@/views/services/pay-gateways/useStore'
import AddDeductBtn from '@/views/transactions/settle/AddDeductBtn.vue'
import { settlementFunctionCollect } from '@/views/transactions/settle/Settle'
import { useSearchStore } from '@/views/transactions/settle/useSalesforceStore'
import { getLevelByIndex, getUserLevel, salesLevels } from '@axios'
import { DateFilters } from '@core/enums'
import corp from '@corp'

const { store, head, exporter } = useSearchStore()
const { selected, all_selected } = selectFunctionCollect(store)
const { getSettleStyle, batchSettle, isSalesCol, movePartSettle, settle } = settlementFunctionCollect(store)
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
store.params.is_base_trx = 1

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
</script>
<template>
    <BaseIndexView placeholder="영업점 상호 검색" :metas="[]" :add="false" add_name="정산" :date_filter_type="DateFilters.SETTLE_RANGE">
        <template #index_extra_field>
            <VBtn prepend-icon="tabler-calculator" @click="batchSettle(selected, false)" v-if="getUserLevel() >= 35" size="small">
                일괄 정산하기
            </VBtn>
            <VSwitch hide-details :false-value=0 :true-value=1 v-model="store.params.is_base_trx" label="매출일 기준 조회" color="primary" @update:modelValue="[store.updateQueryString({is_base_trx: store.params.is_base_trx})]"/>
        </template>
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="false" :terminal="true" :cus_filter="true"
                :sales="true">
                <template #sales_extra_field>
                    <VCol cols="6" sm="3">
                        <VSelect v-model="store.params.level" :items="salesLevels()" density="compact" label="조회 등급"
                            item-title="title" item-value="id"
                            @update:modelValue="store.updateQueryString({ level: store.params.level })" />
                    </VCol>
                    <VCol cols="6" sm="3">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.settle_cycle"
                            :items="[{ id: null, title: '전체' }].concat(settleCycles())" :label="`영업점 정산주기 필터`"
                            item-title="title" item-value="id" @update:modelValue="[store.updateQueryString({settle_cycle: store.params.settle_cycle})]" />
                    </VCol>
                </template>
                <template #pg_extra_field>
                    <VCol cols="6" sm="3">
                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.mcht_settle_type"
                            :items="[{ id: null, name: '전체' }].concat(settle_types)" label="가맹점 정산타입 필터" item-title="name"
                            item-value="id" @update:modelValue="[store.updateQueryString({mcht_settle_type: store.params.mcht_settle_type})]"/>
                    </VCol>
                </template>
            </BaseIndexFilterCard>
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
                            :content="'차감이 아닌 추가금 설정을 하시러면 금액 앞에 -(마이너스 기호)를 입력 후 차감버튼을 클릭해주세요.'"/>
                    </template>
                    <template v-else-if="key == 'id'">
                        <div class='check-label-container'>
                            <VCheckbox v-model="all_selected" class="check-label" v-if="getUserLevel() >= 35" style="min-width: 1em;"/>
                            <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                                :content="'하단 영업점 고유번호를 클릭하여 부분정산 페이지로 이동할 수 있습니다.'"/>
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
                            <span v-else-if="_key === 'terminal' && (__key as string) === 'settle_pay_module_idxs'">
                                    {{ item[_key][__key] ? (item[_key][__key]).toLocaleString() : 0 }}
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
                <VTooltip activator="parent" location="end" open-delay="250" transition="scroll-x-transition" v-if="$vuetify.display.smAndDown === false">
                    {{ item['sales_name'] }}
                </VTooltip>
                <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                    <template v-if="head.getDepth(_header, 0) != 1">
                        <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="__header.visible"
                            class='list-square'>
                            <span v-if="_key == 'deduction' && (__key as string) == 'input'">
                                <AddDeductBtn :id="item['id']" :name="item['user_name']" :is_mcht="false"/>
                            </span>
                            <span v-else-if="_key === 'terminal' && (__key as string) === 'settle_pay_module_idxs'">
                                {{ item[_key][__key] ? (item[_key][__key].length).toLocaleString() : 0 }}
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
                                    <VCheckbox v-model="selected" :value="item[_key]" class="check-label" style="min-inline-size: 1em;" v-if="getUserLevel() >= 35"/>
                                    <span class="edit-link" @click="movePartSettle(item, false)">#{{ item[_key] }}</span>
                                </div>
                            </span>
                            <span v-else-if="_key == 'level'">
                                <VChip :color="store.getSelectIdColor(getLevelByIndex(item[_key]))">
                                    {{ all_sales.find(obj => obj.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'settle_cycle'">
                                <VChip :color="store.getSelectIdColor(all_cycles.find(obj => obj.id === item[_key]/7)?.id)">
                                    {{ all_cycles.find(sales => sales.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key == 'resident_num'">
                                <span>{{ item['resident_num_front'] }}</span>
                                <span style="margin: 0 0.25em;">-</span>
                                <span v-if="corp.pv_options.free.resident_num_masking">*******</span>
                                <span v-else>{{ item['resident_num_back'] }}</span>
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
                                <VBtn size="small" type="button" color="primary" @click="settle(item['user_name'], item, false)" v-if="getUserLevel() >= 35">
                                    정산하기
                                    <VIcon size="22" icon="tabler-calculator" style="margin-left: 0.25em;"/>
                                </VBtn>
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
