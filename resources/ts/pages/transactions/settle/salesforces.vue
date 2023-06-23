<script setup lang="ts">
import { useSearchStore } from '@/views/transactions/settle/useSalesforceStore'
import AddDeductBtn from '@/views/transactions/settle/AddDeductBtn.vue'
import { settleCycles } from '@/views/salesforces/useStore'
import BaseIndexFilterCard from '@/layouts/lists/BaseIndexFilterCard.vue';
import BaseIndexView from '@/layouts/lists/BaseIndexView.vue';
import { salesLevels } from '@/views/salesforces/useStore';

const { store, head, exporter } = useSearchStore()
const all_sales = salesLevels()

provide('store', store)
provide('head', head)
provide('exporter', exporter)

const metas = []
store.params.level = all_sales[0].id

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
const getSalesTypeColor = (_class: number) => {
    const id = all_sales.find(item => item.id === _class)?.id
    if (id == 0)
        return "default"
    else if (id == 1)
        return "primary"
    else if (id == 2)
        return "success"
    else if (id == 3)
        return "info"
    else if (id == 4)
        return "warning"
    else if (id == 5)
        return "error"
    else
        return 'default';
}
</script>
<template>
    <BaseIndexView placeholder="영업점 ID 검색" :metas="metas" :add="false" add_name="정산" :is_range_date="false">
        <template #filter>
            <BaseIndexFilterCard :pg="true" :ps="true" :pay_cond="true" :terminal="true" :cus_filter="true" :sales="true">
                <template #extra_left>
                    <VCol cols="12" sm="3">
                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.level"
                            :items="salesLevels()" :label="`등급 선택`"
                            item-title="title" item-value="id" />
                    </VCol>
                    <VCol cols="12" sm="3">
                        <VAutocomplete :menu-props="{ maxHeight: 400 }" v-model="store.params.settle_cycle"
                            :items="[{ id: null, title: '정산주기 선택' }].concat(settleCycles())" :label="`정산주기 선택`"
                            item-title="name" item-value="id" />
                    </VCol>
                </template>
            </BaseIndexFilterCard>
        </template>
        <template #headers>
            <tr>
                <th v-for="(colspan, index) in head.getColspansComputed" :colspan="colspan" :key="index" class='list-square'>
                    <span>
                        {{ head.main_headers[index] }}
                    </span>
                </th>
            </tr>
            <tr>
                <th v-for="(header, key) in head.flat_headers" :key="key" v-show="!header.hidden" class='list-square'>
                    <template v-if="key == 'deduction.input'">
                        <div class="d-inline-flex align-center gap-2 justify-content-evenly">
                            <span>
                                {{ header.ko }}
                            </span>
                            <VTooltip open-on-click :open-on-hover="false" location="top" transition="scale-transition">
                                <template #activator="{ props }">
                                    <VIcon v-bind="props" size="20" icon="ic:outline-help" color="primary"
                                        style="margin-bottom: 0.2em;" />
                                </template>
                                <span>
                                    차감이 아닌 추가금 설정을 하시러면 금액 앞에 "-"(마이너스 기호)를 입력 후 차감버튼을 클릭해주세요.
                                </span>
                            </VTooltip>
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
            <tr v-for="(item, index) in store.items" :key="index" style="height: 3.75rem;">
                <template v-for="(_header, _key, _index) in head.headers" :key="_index">
                    <template v-if="head.getDepth(_header, 0) != 1">
                        <td v-for="(__header, __key, __index) in _header" :key="__index" v-show="!__header.hidden" class='list-square'>
                            <span v-if="_key == 'deduction' && (__key as string) == 'input'">
                                <AddDeductBtn :id="item['id']" :name="item['mcht_name']" :is_mcht="false">
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
                            <span v-else-if="_key == 'level'">
                                <VChip :color="getSalesTypeColor(item[_key])">
                                    {{ all_sales.find(sales => sales.id === item[_key])?.title }}
                                </VChip>
                            </span>
                            <span v-else-if="_key === 'count'" style="font-weight: bold;">
                                {{ (item[_key] as number).toLocaleString() }}
                            </span>
                            <span v-else-if="_key === 'amount'" style="font-weight: bold;">
                                {{ (item[_key] as number).toLocaleString() }}
                            </span>
                            <span v-else-if="_key === 'trx_amount'" style="font-weight: bold;">
                                {{ (item[_key] as number).toLocaleString() }}
                            </span>
                            <span v-else-if="_key === 'pay_cond_amount'" style="font-weight: bold;">
                                {{ (item[_key] as number).toLocaleString() }}
                            </span>
                            <span v-else-if="_key === 'profit'" style="font-weight: bold;">
                                {{ (item[_key] as number).toLocaleString() }}
                            </span>
                            <span v-else-if="_key === 'extra_col'">
                                <VBtn icon size="x-small" color="default" variant="text">
                                    <VIcon size="22" icon="tabler-dots-vertical" />
                                    <VMenu activator="parent">
                                        <VList>
                                            <VListItem value="saleslip" @click="">
                                                <template #prepend>
                                                    <VIcon size="24" class="me-3" icon="tabler-calculator" />
                                                </template>
                                                <VListItemTitle>정산하기</VListItemTitle>
                                            </VListItem>
                                        </VList>
                                    </VMenu>
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
