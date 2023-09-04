<script setup lang="ts">
import type { MchtRecentTransaction } from '@/views/types'
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue'

interface Props {
    transactions: MchtRecentTransaction[],
}
const props = defineProps<Props>()

let first = '2023-01-01'
let end = '2023-01-01'
const is_skeleton = ref(true)

watchEffect(() => {
    if(props.transactions)
    {
        first = props.transactions[0].mcht_name as string
        end = props.transactions[props.transactions.length - 1].mcht_name as string
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
                    <b>{{ props.transactions.length }}</b>개 가맹점 매출액 랭킹
                </template>
            </span>
            <br>
            <span style="font-size: 0.8em;">
                <template v-if="is_skeleton">
                </template>
                <template v-else>
                    {{ first }} (1위)
                    ~
                    {{ end }} ({{ props.transactions.length }}위)
                </template>
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
            <template v-if="is_skeleton">
                <tr v-for="(transaction, key) in 10" :key="key">
                    <td class="list-square"><SkeletonBox/></td>
                    <td class="list-square"><SkeletonBox/></td>
                    <td class="list-square"><SkeletonBox/></td>
                    <td class="list-square"><SkeletonBox/></td>
                    <td class="list-square"><SkeletonBox/></td>
                    <td class="list-square"><SkeletonBox/></td>
                </tr>
            </template>
            <template v-else>
            <tr v-for="(transaction, key, index) in props.transactions" :key="key">
                <td class="list-square">
                    <span>
                        {{ key + 1 }}위
                    </span>
                </td>
                <td class="list-square">
                    <span>
                        <VChip size="small" color="primary" label>
                            {{ transaction.mcht_name }}
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
