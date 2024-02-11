<script lang="ts" setup>
import BaseQuestionTooltip from '@/layouts/tooltips/BaseQuestionTooltip.vue'
import DifferentSettlementInfoCardTr from '@/views/services/brands/different-settlement-infos/DifferentSettlementInfoCardTr.vue'
import type { Brand, DifferentSettlementInfo } from '@/views/types'
import { useRequestStore } from '@/views/request'

interface Props {
    item: Brand,
}
const props = defineProps<Props>()
const { setNullRemove } = useRequestStore()
const different_settlement_infos = reactive<DifferentSettlementInfo[]>(props.item.different_settlement_infos || [])

const addNewDifferentSettlementInfoCard = () => {
    different_settlement_infos.push(<DifferentSettlementInfo>({
        id: 0,
    }))
}
watchEffect(() => {
    setNullRemove(different_settlement_infos)
})
</script>
<template>
    <VCardTitle style="margin: 1em 0;">
        <BaseQuestionTooltip :location="'top'" :text="'차액정산 정보'" :content="'차액정산에 사용될 계정정보입니다.'">
        </BaseQuestionTooltip>
    </VCardTitle>
    <VTable class="text-no-wrap" style=" min-width: 100%; margin-bottom: 1em;text-align: center;">
        <thead>
            <tr>
                <th scope="col" class='list-square'>No.</th>
                <th scope="col" class='list-square' style="width: 250px;">PG사 선택</th>
                <th scope="col" class='list-square' style="width: 150px;">대표가맹점 MID</th>
                <th scope="col" class='list-square' style="width: 150px;">SFTP ID</th>
                <th scope="col" class='list-square' style="width: 150px;">SFTP PW</th>
                <th scope="col" class='list-square'></th>
            </tr>
        </thead>
        <tbody>
            <DifferentSettlementInfoCardTr v-for="(item, index) in different_settlement_infos"
                :key="item.id" style="margin-top: 1em;" :item="item" :index="index" />
        </tbody>
        <tfoot v-show="Boolean(props.item.id == 0)">
            <tr>
                <td colspan="4" class="text-center">
                    운영사 정보를 추가하신 후 사용 가능합니다.
                </td>
            </tr>
        </tfoot>
    </VTable>
    <VRow v-show="Boolean(props.item.id != 0)">
        <VCol class="d-flex gap-4">
            <VBtn type="button" style="margin-left: auto;" @click="addNewDifferentSettlementInfoCard()">
                차액정산 정보 신규추가
                <VIcon end icon="tabler-plus" />
            </VBtn>
        </VCol>
    </VRow>
</template>
<style scoped>
:deep(.v-table__wrapper) {
  block-size: auto !important;
}
</style>
