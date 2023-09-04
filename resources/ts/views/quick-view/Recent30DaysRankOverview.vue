<script setup lang="ts">
import type { MchtRecentTransaction } from '@/views/types'
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue'

interface Props {
    transactions: MchtRecentTransaction[],
}
const props = defineProps<Props>()

const formatDate = <any>(inject('$formatDate'))
const date = new Date()
const weekdays = ['일', '월', '화', '수', '목', '금', '토'];
const s_dt = formatDate(new Date(date.getFullYear(), date.getMonth()))
const e_dt = formatDate(new Date(date.getFullYear(), date.getMonth() + 1))

const is_skeleton = ref(true)

watchEffect(() => {
    if(props.transactions)
        is_skeleton.value = false
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
