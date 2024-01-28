<script setup lang="ts">
import { installments, module_types } from '@/views/merchandises/pay-modules/useStore'
import { useCRMStore } from '@/views/dashboards/crm/crm'
import SkeletonBox from '@/layouts/utils/SkeletonBox.vue'

const is_skeleton = <any>(inject('is_skeleton'))

const { danger_histories } = useCRMStore()
const booleanTypeColor = (type: boolean | null) => {
    return Boolean(type) ? "default" : "success";
}
const getSelectIdColor = (id: number | undefined) => {
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
        return 'default'
}
</script>

<template>
    <VCard title="최근 이상거래">
        <VDivider />
        <VTable class="text-no-wrap" style="height: 100% !important;">
            <thead>
                <tr>
                    <th class="font-weight-semibold list-square">
                        가맹점 상호
                    </th>
                    <th class="font-weight-semibold list-square">
                        거래타입
                    </th>
                    <th class="font-weight-semibold list-square">
                        거래금액
                    </th>
                    <th class="font-weight-semibold list-square">
                        할부
                    </th>
                    <th class="font-weight-semibold list-square">
                        발급사
                    </th>
                    <th class="font-weight-semibold list-square">
                        카드번호
                    </th>
                    <th class="font-weight-semibold list-square">
                        거래시간
                    </th>
                    <th class="font-weight-semibold list-square">
                        이상거래타입
                    </th>
                </tr>
            </thead>
            <tbody>
                <template v-if="is_skeleton">
                    <tr v-for="(transition, _key) in 9" :key="_key">
                        <td class="list-square">
                            <SkeletonBox />
                        </td>
                        <td class="list-square">
                            <SkeletonBox />
                        </td>
                        <td class="list-square">
                            <SkeletonBox />
                        </td>
                        <td class="list-square">
                            <SkeletonBox />
                        </td>
                        <td class="list-square">
                            <SkeletonBox />
                        </td>
                        <td class="list-square">
                            <SkeletonBox />
                        </td>
                        <td class="list-square">
                            <SkeletonBox />
                        </td>
                        <td class="list-square">
                            <SkeletonBox />
                        </td>
                    </tr>
                </template>
                <template v-else>
                    <tr v-for="(transition, key, index) in danger_histories" :key="key">
                        <td class="list-square">
                            {{ transition.mcht_name }}
                        </td>
                        <td class="list-square">
                            <VChip
                                :color="getSelectIdColor(module_types.find(obj => obj.id === transition.module_type)?.id)">
                                {{ module_types.find(obj => obj.id === transition.module_type)?.title }}
                            </VChip>
                        </td>
                        <td class="list-square">
                            {{ transition.amount.toLocaleString() }}
                        </td>
                        <td class="list-square">
                            {{ installments.find(inst => inst['id'] === transition.installment)?.title }}
                        </td>
                        <td class="list-square">
                            {{ transition.issuer }}
                        </td>
                        <td class="list-square">
                            {{ transition.card_num }}
                        </td>
                        <td class="list-square">
                            {{ transition.trx_dttm }}
                        </td>
                        <td class="list-square">
                            <VChip :color="booleanTypeColor(!transition.danger_type)">
                                {{ transition.danger_type ? '한도초과' : '중복결제' }}
                            </VChip>
                        </td>
                    </tr>
                </template>
            </tbody>
            <tfoot v-show="!Boolean(danger_histories.length) && !is_skeleton">
                <tr>
                    <td colspan="8" class='list-square' style="border: 0;">
                        최근 이상거래가 존재하지 않습니다.
                    </td>
                </tr>
            </tfoot>
        </VTable>
    </VCard>
</template>
