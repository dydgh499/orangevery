<script setup lang="ts">
import type { MchtRecentTransaction } from '@/views/types'
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue'

interface Props {
    transactions: MchtRecentTransaction[],
}
const props = defineProps<Props>()

let s_dt = '2023-01-01'
let e_dt = '2023-01-01'
const weekdays = ['일', '월', '화', '수', '목', '금', '토'];
const is_skeleton = ref(true)
watchEffect(() => {
    if(props.transactions)
    {
        s_dt = props.transactions[props.transactions.length - 1].day as string
        e_dt = props.transactions[0].day as string
        is_skeleton.value = false
    }
})
</script>
<template>
    <VCard style="text-align: center;" color="primary">
        <VCol>
            <span>
                <template v-if="is_skeleton">
                </template>
                <template v-else>
                    <b>{{ props.transactions.length }}</b>일간 정산 금액
                </template>                
            </span>
            <br>
            <span style="font-size: 0.8em;">
                <template v-if="is_skeleton">
                </template>
                <template v-else>
                    {{ s_dt }}
                    ({{ weekdays[new Date(s_dt).getDay()] }})
                    ~
                    {{ e_dt }}
                    ({{ weekdays[new Date(e_dt).getDay()] }})
                </template>
            </span>
        </VCol>
    </VCard>
    <VTable class="text-no-wrap">
        <thead>
            <tr>
                <th class="list-square">
                    <span>일자</span>
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
            <template v-if="is_skeleton">
                <tr v-for="(transaction, key) in 10" :key="key">
                    <td class="list-square"><SkeletonBox/></td>
                    <td class="list-square"><SkeletonBox/></td>
                    <td class="list-square"><SkeletonBox/></td>
                    <td class="list-square"><SkeletonBox/></td>
                    <td class="list-square"><SkeletonBox/></td>
                </tr>
            </template>
            <template v-else>
                <tr v-for="(transaction, key) in props.transactions" :key="key">
                    <td class="list-square">
                        <span>
                            <VChip size="small" color="primary" label>
                                {{ transaction.day + "(" + weekdays[new Date(transaction.day).getDay()] + ")" }}
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
                            {{ transaction.appr_amount.toLocaleString() }}원
                        </span>
                    </td>
                    <td class="list-square">
                        <span>
                            {{ transaction.appr_count.toLocaleString() }}건
                        </span>
                    </td>
                    <td class="list-square">
                        <span>
                            {{ transaction.cxl_count.toLocaleString() }}건
                        </span>
                    </td>
                </tr>
            </template>
        </tbody>
    </VTable>
</template>
