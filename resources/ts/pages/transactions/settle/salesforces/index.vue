<script setup lang="ts">
import { useSearchStore } from '@/views/transactions/settle/useSalesforceStore'
import AddDeductBtn from '@/views/transactions/settle/AddDeductBtn.vue'
import ExtraMenu from '@/views/transactions/settle/ExtraMenu.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue'
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue'
import { salesLevels, settleCycles, settleDays, settleTaxTypes } from '@/views/salesforces/useStore'
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'

const { store, head, exporter } = useSearchStore()
const all_sales = salesLevels()
const all_cycles = settleCycles()
const all_days = settleDays()
const tax_types = settleTaxTypes()
const totals = ref(<any[]>([]))
const router = useRouter()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

store.params.level = all_sales[0].id

const getSettleStyle = (parent_key: string) => {
    if (parent_key === 'appr')
        return 'color: rgb(var(--v-theme-primary));'
    else if (parent_key === 'cxl')
        return 'color: red;'
    else if (parent_key === 'settle')
        return 'font-weight: bold;'
    else
        return '' // 기본 스타일 또는 다른 스타일을 지정하고 싶은 경우 여기에 작성
}
const isSalesCol = (key: string) => {
    const sales_cols = ['count', 'amount', 'trx_amount', 'settle_fee', 'hold_amount', 'total_trx_amount', 'profit']
    for (let i = 0; i < sales_cols.length; i++) {
        if (sales_cols[i] === key)
            return true
    }
    return false
}

const movePartSettle = (id: number) => {    
    router.push('/transactions/settle/salesforces/part/'+id+'?dt='+store.params.dt+'&level='+store.params.level)
}

onMounted(() => {
    watchEffect(async () => {
        if (store.getChartProcess() === false) {
            const r = await store.getChartData()
            totals.value = []
            if (r.data.amount != 0)
                totals.value.push(r.data)
        }
    })
})

watchEffect(() => {
    store.setChartProcess()
    store.params.level = store.params.level
    store.params.settle_cycle = store.params.settle_cycle
})
</script>
<template>
    <BaseIndexView placeholder="영업점 상호 검색" :metas="[]" :add="false" add_name="정산" :is_range_date="false">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :settle_type="true" :terminal="true" :cus_filter="true"
                :sales="true">
                <template #sales_extra_field>
                    <VCol cols="12" sm="3">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.level" :items="salesLevels()"
                            :label="`전체`" item-title="title" item-value="id" create />
                    </VCol>
                    <VCol cols="12" sm="3">
                        <VSelect :menu-props="{ maxHeight: 400 }" v-model="store.params.settle_cycle"
                            :items="[{ id: null, title: '전체' }].concat(settleCycles())" :label="`정산주기 선택`"
                            item-title="title" item-value="id" create />
                    </VCol>
                </template>
            </BaseIndexFilterCard>
        </template>
        <template #headers>
            <tr>
                <th v-for="(colspan, index) in head.getColspansComputed" :colspan="colspan" :key="index"
                    class='list-square'>
                    <span>
                        {{ head.main_headers[index] }}
                    </span>
                </th>
            </tr>
            <tr>
                <th v-for="(header, key) in head.flat_headers" :key="key" v-show="header.visible" class='list-square'>
                    <template v-if="key == 'deduction.input'">
                        <BaseQuestionTooltip :location="'top'" :text="header.ko"
                            :content="'차감이 아닌 추가금 설정을 하시러면 금액 앞에 -(마이너스 기호)를 입력 후 차감버튼을 클릭해주세요.'">
                        </BaseQuestionTooltip>
                    </template>
                    <template v-else-if="key == 'id'">
                        <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                            :content="'하단 영업점 고유번호를 클릭하여 부분정산 페이지로 이동할 수 있습니다.'">
                        </BaseQuestionTooltip>
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
            <tr v-for="(item, index) in totals" :key="index" style="height: 3.75rem;">
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
            <tr v-for="(item, index) in store.items" :key="index" style="height: 3.75rem;">
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
                            <span v-if="_key === 'id'" class="edit-link" @click="movePartSettle(item[_key])">
                                #{{ item[_key] }}
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
