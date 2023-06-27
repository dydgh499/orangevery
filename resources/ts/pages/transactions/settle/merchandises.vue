<script setup lang="ts">
import { useSearchStore } from '@/views/transactions/settle/useMerchandiseStore'
import AddDeductBtn from '@/views/transactions/settle/AddDeductBtn.vue'
import ExtraMenu from '@/views/transactions/settle/ExtraMenu.vue'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue';
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue';
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';


const { store, head, exporter } = useSearchStore()
provide('store', store)
provide('head', head)
provide('exporter', exporter)

const metas = [
    {
        icon: 'tabler-user',
        color: 'primary',
        title: '금월 추가된 가맹점',
        stats: '21,459',
        percentage: +29,
        subtitle: 'Total Users',
    },
    {
        icon: 'tabler-user-plus',
        color: 'error',
        title: '금주 추가된 가맹점',
        stats: '4,567',
        percentage: +18,
        subtitle: 'Last week analytics',
    },
    {
        icon: 'tabler-user-check',
        color: 'success',
        title: '금월 감소한 가맹점',
        stats: '19,860',
        percentage: -14,
        subtitle: 'Last week analytics',
    },
    {
        icon: 'tabler-user-exclamation',
        color: 'warning',
        title: '금주 감소한 가맹점',
        stats: '237',
        percentage: +42,
        subtitle: 'Last week analytics',
    },
]
store.params.level = 10 // taransaction model에서 필수

const getSettleStyle = (parent_key: string) => {
    if (parent_key === 'appr')
        return 'color: blue;';
    else if (parent_key === 'cxl')
        return 'color: red;';
    else if (parent_key === 'settle')
        return 'font-weight: bold;';
    else
        return ''; // 기본 스타일 또는 다른 스타일을 지정하고 싶은 경우 여기에 작성
}
const isSalesCol = (key: string) => {
    const sales_cols = ['count', 'amount', 'trx_amount', 'settle_fee', 'hold_amount', 'total_trx_amount', 'profit']
    for (let i = 0; i < sales_cols.length; i++) {
        if (sales_cols[i] === key)
            return true
    }
    return false
}
</script>
<template>
    <BaseIndexView placeholder="가맹점 상호 검색" :metas="metas" :add="false" add_name="정산" :is_range_date="false">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :pay_cond="true" :terminal="true" :cus_filter="true" :sales="true" />
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
                <th v-for="(header, key) in head.flat_headers" :key="key" v-show="!header.hidden" class='list-square'>
                    <template v-if="key == 'deduction.input'">
                        <BaseQuestionTooltip :location="'top'" :text="(header.ko as string)"
                            :content="'차감이 아닌 추가금 설정을 하시러면 금액 앞에 -(마이너스 기호)를 입력 후 차감버튼을 클릭해주세요.'">
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
            <tr v-for="(item, index) in store.items" :key="index" style="height: 3.75rem;">
                <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                    <template v-if="head.getDepth(_header, 0) != 1">
                        <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="!__header.hidden"
                            class='list-square'>
                            <span v-if="_key == 'deduction' && (__key as string) == 'input'">
                                <AddDeductBtn :id="item['id']" :name="item['mcht_name']" :is_mcht="true">
                                </AddDeductBtn>
                            </span>
                            <span v-else :style="getSettleStyle(_key as string)">
                                {{ (item[_key][__key] as number).toLocaleString() }}
                            </span>
                        </td>
                    </template>
                    <template v-else>
                        <td v-show="!_header.hidden" class='list-square'>
                            <span v-if="_key === 'id'" class="edit-link">
                                #{{ item[_key] }}
                            </span>
                            <span v-else-if="isSalesCol(_key as string)" style="font-weight: bold;">
                                {{ (item[_key] as number).toLocaleString() }}
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
        </template>
    </BaseIndexView>
</template>
