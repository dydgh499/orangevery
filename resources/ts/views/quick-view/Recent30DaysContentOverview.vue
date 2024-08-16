<script setup lang="ts">
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue';
import type { MchtRecentTransaction } from '@/views/types';
import { getUserLevel, user_info } from '@axios';

interface Props {
    transactions?: MchtRecentTransaction[] | undefined,
}
const props = defineProps<Props>()
const formatDate = <any>(inject('$formatDate'))

let s_dt = formatDate(new Date())
let e_dt = formatDate(new Date())

const weekdays = ['일', '월', '화', '수', '목', '금', '토'];
const is_skeleton = ref(true)
watchEffect(() => {
    if(props.transactions)
    {
        if(props.transactions.length) {
            s_dt = props.transactions[props.transactions.length - 1].day as string
            e_dt = props.transactions[0].day as string
        }
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
                    <b>{{ props.transactions.length }}</b>일간 
                    <span v-if="((getUserLevel() == 10 && user_info.is_show_fee) || getUserLevel() >= 13)">정산</span>
                    <span v-else>승인/취소</span>
                    금액
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
                <th class="list-square" v-if="((getUserLevel() == 10 && user_info.is_show_fee) || getUserLevel() >= 13)">
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
                                {{ transaction.day + "(" + weekdays[new Date(transaction.day as string).getDay()] + ")" }}
                            </VChip>
                        </span>
                    </td>
                    <td class="list-square" v-if="((getUserLevel() == 10 && user_info.is_show_fee) || getUserLevel() >= 13)">
                        <span>
                            {{ transaction.profit.toLocaleString() }}원
                        </span>
                    </td>
                    <td class="list-square">
                        <span>
                            {{ (transaction.appr_amount + transaction.cxl_amount).toLocaleString() }}원
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
        <tfoot v-if="!Boolean(props.transactions && props.transactions.length)">
            <tr>
                <td :colspan="5" class="list-square">
                    거래건이 존재하지 않습니다.
                </td>                
            </tr>
        </tfoot>
    </VTable>
</template>
