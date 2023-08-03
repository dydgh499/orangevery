<script setup lang="ts">
import type { MchtRecentTransaction } from '@/views/types'

interface Props {
    transactions: MchtRecentTransaction,
}
const props = defineProps<Props>()

const formatDate = <any>(inject('$formatDate'))
const date = new Date()
const weekdays = ['일', '월', '화', '수', '목', '금', '토'];
const s_dt = formatDate(new Date(date.getFullYear(), date.getMonth()))
const e_dt = formatDate(new Date(date.getFullYear(), date.getMonth() + 1))

const transactions = ref(<MchtRecentTransaction>(props.transactions))
const sortByAmount = () => {
    if (props.transactions != undefined) {
        const sortedTransactions = Object.entries(props.transactions)
        .sort(([, a], [, b]) => b.amount - a.amount)
        .reduce((acc, [key, value]) => ({ ...acc, [key]: value }), {})
        transactions.value = sortedTransactions
    }
}

watchEffect(() => {
    sortByAmount()
})
</script>
<template>
    <VCard style="text-align: center;" color="primary">
        <VCol>
            <span>
                <b>30</b>일간 매출액 순위
            </span>
            <br>
            <span style="font-size: 0.8em;">
                {{ s_dt }}
                ({{ weekdays[new Date(s_dt).getDay()] }})
                ~
                {{ e_dt }}
                ({{ weekdays[new Date(e_dt).getDay()] }})
            </span>
        </VCol>
    </VCard>
    <VTable class="text-no-wrap">
        <thead>
            <tr>
                <th class="list-square">
                    <span>순위</span>
                </th>
                <th class="list-square">
                    <span>상호명</span>
                </th>
                <th class="list-square">
                    <span>정산액</span>
                </th>
                <th class="list-square">
                    <span>매출액</span>
                </th>
                <th class="list-square">
                    <span>승인건수</span>
                </th>
                <th class="list-square">
                    <span>취소건수</span>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(transaction, key, index) in transactions" :key="key">
                <td class="list-square">
                    <span>
                        {{ index + 1 }}위
                    </span>
                </td>
                <td class="list-square">
                    <span>
                        <VChip size="small" color="primary" label>
                            {{ key }}
                        </VChip>
                    </span>
                </td>
                <td class="list-square">
                    <span>
                        {{ transaction.profit.toLocaleString() }}원
                    </span>
                </td>
                <td class="list-square">
                    <span>
                        {{ transaction.amount.toLocaleString() }}원
                    </span>
                </td>
                <td class="list-square">
                    <span>
                        {{ transaction.appr.count.toLocaleString() }}건
                    </span>
                </td>
                <td class="list-square">
                    <span>
                        {{ transaction.cxl.count.toLocaleString() }}건
                    </span>
                </td>
            </tr>
        </tbody>
    </VTable>
</template>
